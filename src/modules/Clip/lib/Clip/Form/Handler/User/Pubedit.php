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
    protected $pub;
    protected $workflow;

    protected $alias;
    protected $tid;
    protected $pubtype;
    protected $pubfields;
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
        // GET values set on the first screen only
        $clipvalues = array();

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

            // handle the Doctrine_Record data as an array
            $data[$this->alias][$this->tid][$this->id] = $this->pub->clipFormGet($relconfig['load'], $relconfig['onlyown']);

            // check for set_* and clip_* parameters from $_GET
            $fieldnames = $this->pub->pubFields();

            $get = $this->request->getGet();
            foreach (array_keys($get->getCollection()) as $param) {
                if (strpos($param, 'set_') === 0 || strpos($param, 'clip_') === 0) {
                    $fieldname = preg_replace(array('/^set_/', '/^clip_/'), '', $param);

                    if ($this->pub->contains($fieldname)) {
                        $data[$this->alias][$this->tid][$this->id][$fieldname] = $get->filter($param);
                    } else {
                        $clipvalues[$fieldname] = $get->filter($param);
                    }
                }
            }
        } elseif (!$view->isValid()) {
            // assign the incoming data
            $data = $view->getValues();
            $data = $data['clipdata'];
        } else {
            // let the handleCommand to work
            $data = array();
        }

        // clone the pub to assign the pubdata and do not modify the pub data
        $pubdata = $this->pub->copy(true);
        if ($this->id) {
            $pubdata->assignIdentifier($this->id);
        }
        $pubdata->clipValues(true);

        // fills the render
        $view->assign('clipdata',   $data)
             ->assign('pubdata',    $pubdata)
             ->assign('pubfields',  $this->pubfields)
             ->assign('relations',  $relations)
             ->assign('actions',    $actions)
             ->assign('clipvalues', $clipvalues);

        // create and register clip_form
        $clip_form = new Clip_Util_Form($view);

        $view->register_object('clip_form', $clip_form, array('get', 'set', 'reset', 'newId', 'getvalue', 'loadone', 'loadmany', 'loadvalue', 'resolveId'));

        // stores the first referer and the item URL
        if (!$view->getStateData('referer')) {
            $viewurl = Clip_Util::url($this->tid, 'list', array(), null, null, true);
            $view->setStateData('referer', System::serverGetVar('HTTP_REFERER', $viewurl));
        }

        return true;
    }

    /**
     * Command handler.
     */
    public function handleCommand(Zikula_Form_View $view, &$args)
    {
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
        if (!$this->validateHooks()) {
            return false;
        }

        // get the data set in the form
        $data = $view->getValues();
        // get the initial relations links
        $links = $view->getStateData('links');

        // loop the values and create/update the passed values
        $mainres = array();

        foreach ($data['clipdata'] as $alias => $a) {
            foreach ($a as $tid => $b) {
                $pubtype = Clip_Util::getPubType($tid);

                foreach ($b as $pid => $pubdata) {
                    // publication instance
                    $pub = $pubtype->getPubInstance();

                    if (is_numeric($pid) && $pid) {
                        // FIXME verify it's on the 'pubs' state data
                        $pub->assignIdentifier($pid);
                    }

                    // fill the publication data
                    $l = isset($links[$alias][$tid][$pid]) ? $links[$alias][$tid][$pid] : array();
                    $pub->clipFormFill($pubdata, $l)
                        ->clipValues();

                    // figure out the action to take
                    if ($alias == $this->alias) {
                        $commandName = $args['commandName'];

                    } elseif (isset($pub->commandName)) {
                        $commandName = $pub->commandName;

                    } elseif (!is_numeric($pid) || !$pid) {
                        $this->workflow->setup($pubtype, $pub);
                        // get the first higher command permission for initial state
                        $commandName = $this->workflow->getHighestAction('id');

                    } else {
                        // assumes an update
                        $commandName = 'update';
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
                        $mainres = $res;
                    }
                }
            }
        }

        // clear the cached templates
        // see http://www.smarty.net/manual/en/caching.groups.php
        // clear the displays of the current publication
        $view->clear_cache(null, 'tid_'.$this->tid.'/display/pid'.$this->pub['core_pid']);
        // clear all lists
        $view->clear_cache(null, 'tid_'.$this->tid.'/list');

        // core operations processing
        $goto = $this->processGoto($mainres);

        // check the goto parameter
        switch ($this->goto)
        {
            case 'stepmode':
                // stepmode can be used to go automatically from one workflowstep to the next
                $this->goto = Clip_Util::url($this->pub, 'edit', array('goto' => 'stepmode'));
                break;

            case 'form':
                $this->goto = Clip_Util::url($this->tid, 'edit', array('goto' => 'form'));
                break;

            case 'list':
                $this->goto = Clip_Util::url($this->tid, 'list');
                break;

            case 'display':
                $goto = $displayUrl;
                break;

            case 'admin':
                $this->goto = ModUtil::url('Clip', 'admin', 'pubtypeinfo', array('tid' => $this->tid));
                break;

            case 'home':
                $this->goto = System::getHomepageUrl();
                break;

            case 'referer':
                $this->goto = $goto ? $goto : $this->referer;
                break;

            default:
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
        return $view->redirect($this->goto);
    }

    /**
     * Setters and getters.
     */
    public function ClipSetUp(&$pubdata, &$workflow, $pubtype, $pubfields = null)
    {
        $this->alias = 'clipmain';
        $this->tid   = (int)$pubtype['tid'];
        $this->id    = (int)$pubdata['id'];

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

    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Validate hooks.
     */
    public function validateHooks()
    {
        $pubtype  = Clip_Util::getPubType($this->tid);
        $hooktype = $args['commandName'] == 'delete' ? 'validate_delete' : 'validate_edit';
        $valhook  = new Zikula_ValidationHook($pubtype->getHooksEventName($hooktype), new Zikula_Hook_ValidationProviders());
        $this->notifyHooks($valhook);
        $validators = $valhook->getValidators();

        return !$validators->hasErrors();
    }

    /**
     * Process the result and search for operations redirects.
     */
    protected function processGoto($data)
    {
        if ($this->id) {
            $this->itemurl = Clip_Util::url($this->pub, 'display');
        }

        $goto = null;
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
