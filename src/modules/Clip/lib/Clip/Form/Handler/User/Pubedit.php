<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Form_Handler_User
 */

/**
 * Form handler to update publications.
 */
class Clip_Form_Handler_User_Pubedit extends Zikula_Form_AbstractHandler
{
    protected $id;
    protected $pid;
    protected $pub;
    protected $workflow;

    protected $alias;
    protected $tid;
    protected $pubtype;
    protected $pubfields;
    protected $args;
    protected $itemurl;
    protected $referer;
    protected $goto;

    /**
     * Initialize function.
     */
    public function initialize(Zikula_Form_View $view)
    {
        //// Parameters
        // process the input parameters
        $this->goto = FormUtil::getPassedValue('goto', '');

        //// Actions
        $actions = $this->workflow->getActions(Clip_Workflow::ACTIONS_FORM);
        // if there are no actions the user is not allowed to execute anything.
        // we will redirect the user to the list page
        if (!count($actions)) {
            if ($this->id) {
                LogUtil::registerError($this->__('You have no permissions to execute any action on this publication.'));
            } else {
                LogUtil::registerError($this->__('You have no authorization to submit publications.'));
            }

            return $view->redirect(Clip_Util::url($this->tid, 'list'));
        }

        //// Processing
        // assign the configured relations
        $relconfig = $this->pubtype['config']['edit'];
        $relations = array();
        if ($relconfig['load']) {
            $relations = $this->pub->getRelations($relconfig['onlyown']);
        }

        // initialize the data depending on the form state
        if (!$view->isPostBack()) {
            // initialize the session vars
            $view->setStateData('pubs', array());
            $view->setStateData('links', array());

            // check for set_* parameters from $_GET if its a new publication
            if (!$this->pub->exists()) {
                $rels = $this->pub->getRelationFields();

                $get = $this->request->getGet();
                foreach (array_keys($get->getCollection()) as $param) {
                    if (strpos($param, 'set_') === 0) {
                        $field = substr($param, 4);
                        if (isset($rels[$field])) {
                            $this->pub[$rels[$field]] = $get->filter($param);
                        } else if ($this->pub->contains($field)) {
                            $this->pub[$field] = $get->filter($param);
                        }
                    }
                }
            }

            // handle the Doctrine_Record data as an array
            $data[$this->alias][$this->tid][$this->id][$this->pid] = $this->pub->clipFormGet($relconfig['load'], $relconfig['onlyown']);

        } elseif (!$view->isValid()) {
            // assign the incoming data
            $data = $view->getValues();
            $data = $data['clipdata'];

        } else {
            // let the handleCommand to work
            $data = array();
        }

        // clone the pub to assign the pubdata and do not modify the pub data
        $pubdata = $this->pub->clipCopy();
        $pubdata->clipPostRead();

        // fills the render
        $view->assign('clipdata',  $data)
             ->assign('pubdata',   $pubdata)
             ->assign('pubfields', $this->pubfields)
             ->assign('relations', $relations)
             ->assign('actions',   $actions);

        // create and register clip_form
        $clip_form = new Clip_Util_Form($view);

        $view->register_object('clip_form', $clip_form, array('get', 'set', 'reset', 'newId', 'getprefix', 'getvalue', 'loadone', 'loadmany', 'loadvalue', 'resolveId', 'resolvePid'));

        // stores the first referer and the item URL
        if (!$view->getStateData('referer')) {
            $returnurl = Clip_Util::url($this->pub, $this->pub->core_pid ? 'display' : 'main', array(), null, null, true);
            $view->setStateData('referer', System::serverGetVar('HTTP_REFERER', $returnurl));
        }

        return true;
    }

    /**
     * Command handler.
     */
    public function handleCommand(Zikula_Form_View $view, &$args)
    {
        // clear theme caching of the edit forms
        Clip_Util::clearThemeCache('Clip/user/edit');

        $isAjax = $view->getType() == 'ajax';
        $this->referer = $view->getStateData('referer');

        // cancel processing
        if ($args['commandName'] == 'cancel') {
            if ($isAjax) {
                return new Zikula_Response_Ajax_Json(array('cancel' => true));
            }
            return $view->redirect($this->referer);
        }

        // validated the input
        if (!$view->isValid()) {
            return false;
        }

        // hooks validators
        if (!$this->validateHooks($args['commandName'])) {
            return false;
        }

        // get the data set in the form
        $data = $view->getValues();

        // notify the start of the edition
        $data['clipdata'] = Clip_Event::notify('data.edit.pre', $data['clipdata'], $this->args)->getData();

        // validate if the event indicated invalid data
        if ($data['clipdata'] === false) {
            return false;
        }

        // get the initial relations links
        $links = $view->getStateData('links');

        // loop the values and create/update the passed values
        $mainpub = array();

        foreach ($data['clipdata'] as $alias => $a) {
            foreach ($a as $tid => $b) {
                $pubtype = Clip_Util::getPubType($tid);

                foreach ($b as $id => $pubdata) {
                    $pid = key($pubdata);
                    $pubdata = reset($pubdata);

                    // publication instance
                    $pub = $pubtype->getPubInstance();

                    if (is_numeric($id) && $id) {
                        // FIXME verify it's on the 'pubs' state data
                        $pub->assignIdentifier($id);
                    }

                    if (is_numeric($pid) && $pid) {
                        $pub['core_pid'] = $pid;
                    }

                    // fill the publication data
                    $l = isset($links[$alias][$tid][$id][$pid]) ? $links[$alias][$tid][$id][$pid] : array();

                    $pub->clipFormFill($pubdata, $l)
                        ->clipValues();

                    // figure out the action to take
                    if ($alias == $this->alias) {
                        $commandName = $args['commandName'];

                    } elseif ($pub->hasMappedValue('commandName')) {
                        $commandName = $pub->commandName;

                    } elseif (!is_numeric($id) || !$id) {
                        $this->workflow->setup($pubtype, $pub);
                        // get the first higher command permission for initial state
                        $commandName = $this->workflow->getFirstAction('id');

                    } else {
                        // assumes an update
                        $this->workflow->setup($pubtype, $pub);
                        $commandName = $this->workflow->getFirstAction('id');
                    }

                    // perform the command
                    $res = ModUtil::apiFunc('Clip', 'user', 'edit',
                                            array('data'        => $pub,
                                                  'commandName' => $commandName,
                                                  'schema'      => str_replace('.xml', '', $pubtype['workflow'])));

                    // detect a workflow operation fail
                    if (!$res) {
                        return false;
                    }

                    // store the main result to process the goto
                    if ($alias == $this->alias) {
                        $mainpub = $res;
                    }
                }
            }
        }

        // clear the cached templates
        // see http://www.smarty.net/manual/en/caching.groups.php
        // clear all Clip's cache
        Clip_Util::clearThemeCache('Clip');
        // clear the displays of the current publication
        $view->clear_cache(null, 'tid_'.$this->tid.'/display/pid'.$this->pid);
        // clear all lists
        $view->clear_cache(null, 'tid_'.$this->tid.'/list');

        // notify the finalization of the edition
        $mainpub = Clip_Event::notify('data.edit.post', $mainpub, $this->pub)->getData();

        // check the goto parameter
        switch ($this->goto)
        {
            case 'stepmode':
                // stepmode can be used to go automatically from one workflowstep to the next
                $this->goto = Clip_Util::urlobj($this->pub, 'edit', array('goto' => 'stepmode'));
                break;

            case 'form':
                $this->goto = Clip_Util::urlobj($this->tid, 'edit', array('goto' => 'form'));
                break;

            case 'list':
                $this->goto = Clip_Util::urlobj($this->tid, 'list');
                break;

            case 'display':
                $goto = Clip_Util::urlobj($this->pub, 'display');
                break;

            case 'admin':
                $this->goto = ModUtil::url('Clip', 'admin', 'pubtypeinfo', array('tid' => $this->tid), null, null, true);
                break;

            case 'home':
                $this->goto = System::getHomepageUrl();
                break;

            case 'referer':
                $this->goto = $this->referer;
                break;

            default:
                // core operations goto processing
                $goto = $this->processGoto($mainpub);

                $this->goto = $goto ? $goto : ($this->itemurl ? $this->itemurl : $this->referer);
        }

        // stop here if the request is ajax based
        if ($isAjax) {
            if ($this->goto instanceof Clip_Url) {
                if ($this->goto->getAction() != 'edit') {
                    $response = array('output' => $this->goto->setController('ajax')->modFunc()->payload);
                } else {
                    $response = array('redirect' => $this->goto->getUrl());
                }
            } else {
                $response = array('redirect' => $this->goto);
            }

            return new Zikula_Response_Ajax_Json($response);
        }

        // redirect to the determined url
        return $view->redirect($this->goto instanceof Clip_Url ? $this->goto->getUrl(null, true) : $this->goto);
    }

    /**
     * Setters and getters.
     */
    public function ClipSetUp(&$pubdata, &$workflow, $pubtype, $pubfields = null, $args = array())
    {
        $this->alias = 'clipmain';
        $this->tid   = (int)$pubtype['tid'];
        $this->pid   = (int)$pubdata['core_pid'];
        $this->id    = (int)$pubdata['id'];
        $this->args  = $args;

        $this->workflow = $workflow;

        // pubtype
        $this->pubtype = $pubtype;

        // pubfields
        if ($pubfields) {
            $this->pubfields = $pubfields;
        } else {
            $this->pubfields = Clip_Util::getPubFields($this->tid)->toArray(false);
        }

        // publication assignment
        if (!$this->id) {
            $pubdata['core_author']   = (int)UserUtil::getVar('uid');
            $pubdata['core_language'] = '';
        }

        $pubdata->clipValues();

        $this->pub = $pubdata;
    }

    public function getAlias()
    {
        return $this->alias;
    }

    public function getTid()
    {
        return $this->tid;
    }

    public function getPid()
    {
        return $this->pid;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Validate hooks.
     */
    public function validateHooks($commandName)
    {
        $hooktype  = $commandName == 'delete' ? 'validate_delete' : 'validate_edit';
        $eventname = Clip_Util::getPubType($this->tid)->getHooksEventName($hooktype);

        $valhook  = new Zikula_ValidationHook($eventname, new Zikula_Hook_ValidationProviders());
        $this->notifyHooks($valhook);

        return !$valhook->getValidators()->hasErrors();
    }

    /**
     * Process the result and search for operations redirects.
     */
    protected function processGoto($data)
    {
        $goto = null;

        if (empty($data)) {
            return $goto;
        }

        $this->itemurl = Clip_Util::url($data, 'display');

        // on urltitle change, correct the referer if needed
        if ($data->clipModified('core_urltitle') && strpos($this->referer, Clip_Util::url($this->pub, 'display')) !== false) {
            $this->referer = $this->itemurl;
        }

        // now update the pub instance with the final main one
        $this->pub = $data;

        // check the core operations that equires special redirect
        $uniqueid = $data['core_uniqueid'];

        $ops  = isset($data['core_operations']) ? $data['core_operations'] : array();

        if (isset($ops['delete'][$uniqueid])) {
            // if the item was deleted
            if (Clip_Access::toPubtype($data['core_tid'], 'list')) {
                $url = Clip_Util::url($data['core_tid'], 'list');
            } else {
                $url = System::getHomepageUrl();
            }
            // check if the user comes of the display screen or not
            $goto = (strpos($this->referer, $this->itemurl) === false) ? $this->referer : $url;

        } elseif (isset($ops['create'][$uniqueid]) && $ops['create'][$uniqueid]) {
            // the publication was created
            if ($data['core_online'] == 1) {
                $goto = Clip_Util::url($data, 'display');
            } else {
                // back to the pubtype pending template or referer page if it is not approved yet
                $goto = isset($ops['create']['goto']) ? $ops['create']['goto'] : $this->referer;
            }

        } elseif (!empty($ops)) {
            // check if an operation thrown a goto value
            foreach (array_keys($ops) as $op) {
                if (isset($ops[$op]['goto'])) {
                    $goto = $ops[$op]['goto'];
                }
            }
        }

        return $goto;
    }
}
