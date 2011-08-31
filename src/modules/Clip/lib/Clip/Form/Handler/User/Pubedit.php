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
    protected $relations;

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

            return $view->redirect(ModUtil::url('Clip', 'user', 'list', array('tid' => $this->tid)));
        }

        //// Processing
        // handle the Doctrine_Record data as an array from here
        $data[$this->tid][$this->id] = $this->pub->clipValues()->toArray();

        // process the relations
        $relconfig = $this->pubtype['config']['edit'];

        $this->relations = array();
        if ($relconfig['load']) {
            foreach ($this->pub->getRelations($relconfig['onlyown']) as $key => $rel) {
                // set the data object
                if ($this->pub[$key] instanceof Doctrine_Collection) {
                    foreach ($this->pub[$key] as $k => &$v) {
                        // exclude null records
                        if ($v->exists()) {
                            $v->clipValues();
                        }
                    }
                    $data[$this->tid][$this->id][$key] = $this->pub[$key]->toArray();

                } elseif ($this->pub[$key] instanceof Doctrine_Record && $this->pub[$key]->exists()) {
                    $this->pub[$key]->clipValues();
                    $data[$this->tid][$this->id][$key] = $this->pub[$key]->toArray();

                } else {
                    $data[$this->tid][$this->id][$key] = null;
                }
                // set the relation info
                $this->relations[$key] = $rel;
            }
        }

        // check for set_* default values
        foreach (array_keys($this->pubfields) as $fieldname) {
            $val = FormUtil::getPassedValue('set_'.$fieldname);
            if (!is_null($val)) {
                $data[$this->tid][$this->id][$fieldname] = $val;
            }
        }

        // clone the pub to assign the pubdata and do not modify the pub data
        $pubdata = $this->pub->copy(true);
        if ($this->id) {
            $pubdata->assignIdentifier($this->id);
        }
        $pubdata->clipValues(true);

        // fills the render
        $view->assign('data',      $data)
             ->assign('pubdata',   $pubdata)
             ->assign('pubfields', $this->pubfields)
             ->assign('relations', $this->relations)
             ->assign('actions',   $actions);

        // stores the first referer and the item URL
        if (!$view->getStateData('referer')) {
            $viewurl = ModUtil::url('Clip', 'user', 'list', array('tid' => $this->tid), null, null, true);
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

        // get the data set in the form
        $data = $view->getValues();

        // fill the new values
        $this->getPub($data['data']['clipmain'][$this->tid][$this->id], $view);

        // adds any extra data to the item
        if (isset($data['core_extra'])) {
            $this->pub->mapValue('core_extra', $data['core_extra']);
        }

        // perform the command
        $data = ModUtil::apiFunc('Clip', 'user', 'edit',
                                 array('data'        => $this->pub,
                                       'commandName' => $args['commandName'],
                                       'schema'      => str_replace('.xml', '', $this->pubtype['workflow'])));

        // detect a workflow operation fail
        if (!$data) {
            return false;
        }

        // clear the cached templates
        // see http://www.smarty.net/manual/en/caching.groups.php
        // clear the displays of the current publication
        $view->clear_cache(null, 'tid_'.$this->tid.'/display/pid'.$this->pub['core_pid']);
        // clear all lists
        $view->clear_cache(null, 'tid_'.$this->tid.'/list');

        // core operations processing
        $goto = $this->processGoto($data);

        // check the goto parameter
        switch ($this->goto)
        {
            case 'stepmode':
                // stepmode can be used to go automatically from one workflowstep to the next
                $this->goto = ModUtil::url('Clip', 'user', 'edit',
                                       array('tid'  => $data['core_tid'],
                                             'id'   => $data['id'],
                                             'goto' => 'stepmode'));
                break;

            case 'form':
                $this->goto = ModUtil::url('Clip', 'user', 'edit', array('tid' => $data['core_tid'], 'goto' => 'form'));
                break;

            case 'list':
                $this->goto = ModUtil::url('Clip', 'user', 'list', array('tid' => $data['core_tid']));
                break;

            case 'display':
                $goto = $displayUrl;
                break;

            case 'admin':
                $this->goto = ModUtil::url('Clip', 'admin', 'pubtypeinfo', array('tid' => $data['core_tid']));
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
        $this->id  = (int)$pubdata['id'];
        $this->tid = (int)$pubtype['tid'];

        $this->workflow = $workflow;

        $this->setPub($pubdata);

        // pubtype
        $this->pubtype = $pubtype;

        // pubfields
        if ($pubfields) {
            $this->pubfields = $pubfields;
        } else {
            $this->pubfields = Clip_Util::getPubFields($this->tid)->toArray();
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTid()
    {
        return $this->tid;
    }

    protected function getPub($data, $view)
    {
        // allow specify fixed PIDs for new pubs
        if (!$this->pub['core_pid'] && isset($data['core_pid'])) {
            $this->pub['core_pid'] = $data['core_pid'];
        }

        foreach (array_keys($this->relations) as $alias) {
            // stores the relations data if present
            // for later DB update
            if (array_key_exists($alias, $data)) {
                $tolink = $tounlink = array();
                // get the links present on the form before submit it
                $links = $view->getStateData('links_'.$alias);

                // check the removed ones
                foreach ($links as $id) {
                    if ($id && !in_array((string)$id, $data[$alias])) {
                        $tounlink[] = (int)$id;
                    }
                }
                // check the added ones
                foreach ($data[$alias] as $id) {
                    if ($id && !in_array((int)$id, $links)) {
                        $tolink[] = (int)$id;
                    }
                }

                // unset this data field
                unset($data[$alias]);

                // perform the operations
                if ($tolink) {
                    $this->pub->link($alias, $tolink);
                }
                if ($tounlink) {
                    $this->pub->unlink($alias, $tounlink);
                }
            }
        }

        // fill any other data
        $this->pub->fromArray($data);
    }

    /**
     * Publication data handlers
     */
    protected function setPub(&$pubdata)
    {
        if (!$this->id) {
            $pubdata['core_author']   = (int)UserUtil::getVar('uid');
            $pubdata['core_language'] = '';
        }

        $pubdata->clipProcess();

        $this->pub = $pubdata;
    }

    /**
     * Process the result and search for operations redirects.
     */
    protected function processGoto($data)
    {
        if ($this->id) {
            $params = array('tid' => $this->tid, 'pid' => $this->pub['core_pid'], 'title' => DataUtil::formatPermalink($this->pub['core_title']));
            $this->itemurl = ModUtil::url('Clip', 'user', 'display', $params);
        }

        $goto = null;
        $uniqueid = $data['core_uniqueid'];

        $ops  = isset($data['core_operations']) ? $data['core_operations'] : array();

        if (isset($ops['delete'][$uniqueid])) {
            // if the item was deleted
            if (Clip_Access::toPubtype($data['core_tid'], 'list')) {
                $url = ModUtil::url('Clip', 'user', 'list', array('tid' => $data['core_tid']));
            } else {
                $url = System::getHomepageUrl();
            }
            // check if the user comes of the display screen or not
            $goto = (strpos($this->referer, $this->itemurl) === false) ? $this->referer : $url;

        } elseif (isset($ops['create'][$uniqueid]) && $ops['create'][$uniqueid]) {
            // the publication was created
            if ($data['core_online'] == 1) {
                $goto = ModUtil::url('Clip', 'user', 'display',
                                     array('tid' => $data['core_tid'],
                                           'pid' => $data['core_pid']));
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
