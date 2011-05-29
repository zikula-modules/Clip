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
    private $id;
    private $pub;
    private $workflow;
    private $relations;

    private $tid;
    private $pubtype;
    private $pubfields;
    private $itemurl;
    private $referer;
    private $goto;

    function initialize($view)
    {
        //// Parameters
        // process the input parameters
        $this->tid  = (isset($this->pubtype['tid']) && $this->pubtype['tid'] > 0) ? $this->pubtype['tid'] : FormUtil::getPassedValue('tid');
        $this->goto = FormUtil::getPassedValue('goto', '');

        //// Actions
        $actions = $this->workflow->getActions(Clip_Workflow::ACTIONS_FORM);
        // if there are no actions the user is not allowed to execute anything.
        // we will redirect the user to the list page
        if (!count($actions)) {
            LogUtil::registerError($this->__('You have no permissions to execute any action on this publication.'));

            return $view->redirect(ModUtil::url('Clip', 'user', 'list', array('tid' => $this->tid)));
        }

        // translate any gt string on the action parameters
        $this->translateActions($actions);

        //// Processing
        // handle the Doctrine_Record data as an array from here
        $data = $this->pub->toArray();

        // process the relations
        $relconfig = $this->pubtype['config']['edit'];

        $this->relations = array();
        if ($relconfig['load']) {
            foreach ($this->pub->getRelations($relconfig['onlyown']) as $key => $rel) {
                // set the data object
                if ($this->pub[$key] instanceof Doctrine_Collection) {
                    foreach ($this->pub[$key] as $k => $v) {
                        // exclude null records
                        if ($v->exists()) {
                            $this->pub[$key][$k]->clipProcess();
                        } else {
                            unset($this->pub[$key]);
                        }
                    }
                    $data[$key] = $this->pub[$key]->toArray();

                } elseif ($this->pub[$key] instanceof Doctrine_Record && $this->pub[$key]->exists()) {
                    $this->pub[$key]->clipProcess();
                    $data[$key] = $this->pub[$key]->toArray();

                } else {
                    $data[$key] = null;
                }
                // set the relation info
                $this->relations[$key] = $rel;
            }
        }

        // check for set_* default values
        foreach (array_keys($this->pubfields->toArray()) as $fieldname) {
            $val = FormUtil::getPassedValue('set_'.$fieldname);
            if (!is_null($val)) {
                $data[$fieldname] = $val;
            }
        }

        // fills the render
        $view->assign('pubdata',   $data)
             ->assign('pubobj',    $this->pub)
             ->assign('relations', $this->relations)
             ->assign('actions',   $actions);

        // stores the first referer and the item URL
        if (!$view->getStateData('returnurl')) {
            $viewurl = ModUtil::url('Clip', 'user', 'view', array('tid' => $this->tid), null, null, true);
            $view->setStateData('returnurl', System::serverGetVar('HTTP_REFERER', $viewurl));
        }

        return true;
    }

    function handleCommand($view, &$args)
    {
        $this->returnurl = $view->getStateData('returnurl');

        if ($args['commandName'] == 'cancel') {
            return $view->redirect($this->referer);
        }

        if (!$view->isValid()) {
            return false;
        }

        if (!empty($this->id)) {
            $params = array('tid' => $this->tid, 'pid' => $this->pub['core_pid'], 'title' => DataUtil::formatPermalink($this->pub['core_title']));
            $this->itemurl = ModUtil::url('Clip', 'user', 'display', $params);
        }

        $data = $view->getValues();

        // restore the core values
        $this->getPub($data['pubdata'], $view);

        // adds any extra data to the item
        if (isset($data['core_extra'])) {
            $this->pub->mapValue('core_extra', $data['core_extra']);
        }

        // perform the command
        $data = ModUtil::apiFunc('Clip', 'user', 'edit',
                                 array('data'        => $this->pub,
                                       'commandName' => $args['commandName'],
                                       'pubfields'   => $this->pubfields,
                                       'schema'      => str_replace('.xml', '', $this->pubtype['workflow'])));

        if (!$data) {
            return false;
        }

        // see http://www.smarty.net/manual/en/caching.groups.php
        // clear the displays of the current publication
        $view->clear_cache(null, 'tid_'.$this->tid.'/display/pid'.$this->pub['core_pid']);
        // clear all lists
        $view->clear_cache(null, 'tid_'.$this->tid.'/list');

        // core operations processing
        $goto = $this->processGoto($data);

        // check the goto parameter
        switch ($this->goto) {
            case 'stepmode':
                // stepmode can be used to go automatically from one workflowstep to the next
                $this->goto = ModUtil::url('Clip', 'user', 'edit',
                                       array('tid'  => $data['core_tid'],
                                             'id'   => $data['id'],
                                             'goto' => 'stepmode'));
                break;

            case 'referer':
                $this->goto = $goto ? $goto : $this->referer;
                break;

            case 'editlist':
                $this->goto = ModUtil::url('Clip', 'admin', 'editlist',
                                       array('_id' => $data['core_tid'].'_'.$data['core_pid']));
                break;

            case 'admin':
                $this->goto = ModUtil::url('Clip', 'admin', 'publist', array('tid' => $data['core_tid']));
                break;

            case 'index':
                $this->goto = ModUtil::url('Clip', 'user', 'view', array('tid' => $data['core_tid']));
                break;

            case 'home':
                $this->goto = System::getHomepageUrl();
                break;

            default:
                $this->goto = $goto ? $goto : ($this->itemurl ? $this->itemurl : $this->referer);
        }

        return $view->redirect($this->goto);
    }

    /**
     * Setters and getters
     */
    public function ClipSetUp($id, $tid, &$pubdata, &$workflow, $pubtype=null, $pubfields=null)
    {
        $this->id = $id;
        $this->tid = $tid;
        $this->workflow = $workflow;

        $this->setPub($pubdata);

        // pubtype
        if ($pubtype) {
            $this->pubtype = $pubtype;
        } else {
            $this->pubtype = Clip_Util::getPubType($tid);
        }

        // pubfields
        if ($pubfields) {
            $this->pubfields = $pubfields;
        } else {
            $this->pubfields = Clip_Util::getPubFields($tid);
        }
    }

    public function getPubfieldData($name, $field=null)
    {
        if (empty($name) || !isset($this->pubfields[$name])) {
            return false;
        }

        if ($field && isset($this->pubfields[$name][$field])) {
            return $this->pubfields[$name][$field];
        }

        return $this->pubfields[$name];
    }

    public function getTid()
    {
        return $this->tid;
    }

    private function getPub($data, $view)
    {
        // allow specify fixed PIDs
        if (isset($data['core_pid'])) {
            $this->pub['core_pid'] = $data['core_pid'];
        }

        // link/unlink the relations data if present
        foreach (array_keys($this->relations) as $alias) {
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

                //$relation = $this->pub->getRelation($alias);

                // perform the operations
                // TODO test relation field side assign to NULL
                if ($tolink) {
                    $this->pub->link($alias, $tolink);
                }
                if ($tounlink) {
                    $this->pub->unlink($alias, $tounlink);
                }
                unset($data[$alias]);
            }
        }

        // fill any other data
        $this->pub->fromArray($data);
    }

    /**
     * Publication data handlers
     */
    private function setPub(&$pubdata)
    {
        if (!$this->id) {
            $pubdata['core_author']   = UserUtil::getVar('uid');
            $pubdata['core_language'] = '';
        }

        $pubdata->clipProcess();

        $this->pub = $pubdata;
    }

    /**
     * Misc
     */
    private function translateActions(&$actions)
    {
        foreach (array_keys((array)$actions) as $aid) {
            if (isset($actions[$aid]['parameters'])) {
                // check if the action parameter is translatable
                foreach (array_keys($actions[$aid]['parameters']) as $pname) {
                    foreach ($actions[$aid]['parameters'][$pname] as $k => $v) {
                        if (strpos($k, '__') === 0) {
                            unset($actions[$aid]['parameters'][$pname][$k]);
                            $k = substr($k, 2);
                            $actions[$aid]['parameters'][$pname][$k] = $this->__($v);
                        }
                    }
                }
                // set the button title with the description if not set
                if (!isset($actions[$aid]['parameters']['button']['title'])) {
                    $actions[$aid]['parameters']['button']['title'] = $this->__($actions[$aid]['description']);
                }
            }
        }
    }

    private function processGoto($data)
    {
        $goto = null;
        $pid  = $data['core_pid'];

        $ops  = isset($data['core_operations']) ? $data['core_operations'] : array();

        if (isset($ops['delete'][$pid])) {
            // if the item was deleted
            // FIXME perm check here?
            $urltid = ModUtil::url('Clip', 'user', 'view', array('tid' => $data['core_tid']));
            // check if the user comes of the display screen or not
            $goto = (strpos($this->itemurl, $this->referer) !== 0) ? $this->referer : $urltid;

        } elseif (isset($ops['create']) && $ops['create']) {
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
