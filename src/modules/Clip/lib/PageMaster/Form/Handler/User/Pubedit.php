<?php
/**
 * PageMaster
 *
 * @copyright   (c) PageMaster Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package     Zikula_3rdParty_Modules
 * @subpackage  pagemaster
 */

/**
 * Form handler to update publications.
 */
class PageMaster_Form_Handler_User_Pubedit extends Form_Handler
{
    private $id;
    private $pub;

    private $tid;
    private $pubtype;
    private $pubfields;
    private $itemurl;
    private $referer;
    private $goto;

    function initialize($view)
    {
        // process the input parameters
        $this->tid  = (isset($this->pubtype['tid']) && $this->pubtype['tid'] > 0) ? $this->pubtype['tid'] : FormUtil::getPassedValue('tid');
        $this->goto = FormUtil::getPassedValue('goto', '');

        // initialize the publication array
        $pubdata = array();

        $classname = 'PageMaster_Model_Pubdata'.$this->tid;
        // process a new or existing pub, and it's available actions
        if (!empty($this->id)) {
            $pubdata = Doctrine_Core::getTable($classname)
                       ->find($this->id);

            $pubdata->pubPostProcess();

            $this->pubAssign($pubdata);

            $actions = Zikula_Workflow_Util::getActionsForObject($pubdata, $this->pubtype->getTableName(), 'id', 'PageMaster');
        } else {
            // initial values
            $pubdata = new $classname();

            $this->pubDefault();

            $actions = Zikula_Workflow_Util::getActionsByStateArray(str_replace('.xml', '', $this->pubtype->workflow), 'PageMaster');
        }

        // if there are no actions the user is not allowed to change / submit / delete something.
        // We will redirect the user to the overview page
        if (count($actions) < 1) {
            LogUtil::registerError($this->__('No workflow actions found. This can be a permissions issue.'));

            return $view->redirect(ModUtil::url('PageMaster', 'user', 'view', array('tid' => $this->tid)));
        }

        // translate any gt string on the action parameters
        foreach (array_keys($actions) as $aid) {
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

        // handle the Doctrine_Record data as an array from here
        $data = $pubdata->toArray();

        // detect the relations
        $relations = array();
        $relationdefs = $pubdata->getRelations();

        foreach ($relationdefs as $key => $tid) {
            // set the data object
            $data[$key] = $pubdata[$key];
            // set additional relation fields
            $rel = $this->getRelationType($key);
            $relations[$this->__($key)] = array(
                'tid'  => $tid,
                'type' => $rel['type'],
                'own'  => $rel['own']
            );
        }

        $view->assign('relations', $relations);

        // check for set_* default values
        foreach (array_keys($this->pubfields->toArray()) as $fieldname) {
            $val = FormUtil::getPassedValue('set_'.$fieldname, '');
            if (!empty($val)) {
                $data[$fieldname] = $val;
            }
        }

        // add the pub information to the render
        $view->assign('pubdata', $data);

        // stores the first referer and the item URL
        if (empty($this->referer)) {
            $viewurl = ModUtil::url('PageMaster', 'user', 'view', array('tid' => $this->tid), null, null, true);
            $this->referer = System::serverGetVar('HTTP_REFERER', $viewurl);
        }

        if (!empty($this->id)) {
            $this->itemurl = ModUtil::url('PageMaster', 'user', 'display', array('tid' => $this->tid, 'pid' => $this->pub['core_pid']), null, null, true);
        }

        $view->assign('actions', $actions);

        return true;
    }

    function handleCommand($view, &$args)
    {
        if ($args['commandName'] == 'cancel') {
            return $view->redirect($this->referer);
        }

        if (!$view->isValid()) {
            return false;
        }

        $data = $view->getValues();

        // restore the core values
        $this->pubExtract($data['pubdata']);

        // perform the command
        $data = ModUtil::apiFunc('PageMaster', 'user', 'edit',
                                 array('data'        => $data['pubdata'],
                                       'commandName' => $args['commandName'],
                                       'pubfields'   => $this->pubfields,
                                       'schema'      => str_replace('.xml', '', $this->pubtype['workflow'])));

        // see http://www.smarty.net/manual/en/caching.groups.php
        $tmp = Zikula_View::getInstance('PageMaster');
        // clear the view of the current publication
        $tmp->clear_cache(null, 'display'.$this->tid.'|'.$this->pub['core_pid']);
        // clear all page of publist
        $tmp->clear_cache(null, 'view'.$this->tid);
        unset($tmp);

        // core operations processing
        $goto = $this->itemurl;
        $ops  = isset($data['core_operations']) ? $data['core_operations'] : array();

        if ($data['core_indepot'] == 1 || (isset($ops['deletePub']) && $ops['deletePub'])) {
            // if the item moved to the depot or was deleted
            $urltid = ModUtil::url('PageMaster', 'user', 'view', array('tid' => $data['tid']));
            // check if the user comes of the display screen or not
            $goto = (strpos($this->referer, $this->itemurl) === 0) ? $urltid : $this->referer;

        } elseif (isset($ops['createPub']) && $ops['createPub']) {
            // the publication was created
            if ($data['core_online'] == 1) {
                $goto = ModUtil::url('PageMaster', 'user', 'display', array('tid' => $data['tid'], 'pid' => $data['core_pid']));
            } else {
                // back to the pubtype pending template or referer page if it is not approved yet
                $goto = isset($ops['createPub']['goto']) ? $ops['createPub']['goto'] : $this->referer;
            }

        } elseif (!empty($ops)) {
            // check if an operation thrown a goto value
            foreach (array_keys($ops) as $op) {
                if (isset($ops[$op]['goto'])) {
                    $goto = $ops[$op]['goto'];
                }
            }
        }

        // check the goto parameter
        switch ($this->goto) {
            case 'stepmode':
                // stepmode can be used to go automatically from one workflowstep to the next
                $this->goto = ModUtil::url('PageMaster', 'user', 'edit',
                                       array('tid'  => $data['tid'],
                                             'id'   => $data['id'],
                                             'goto' => 'stepmode'));
                break;

            case 'referer':
                $this->goto = $this->referer;
                break;

            case 'editlist':
                $this->goto = ModUtil::url('PageMaster', 'admin', 'editlist',
                                       array('_id' => $data['tid'].'_'.$data['core_pid']));
                break;

            case 'admin':
                $this->goto = ModUtil::url('PageMaster', 'admin', 'publist', array('tid' => $data['tid']));
                break;

            case 'index':
                $this->goto = ModUtil::url('PageMaster', 'user', 'view', array('tid' => $data['tid']));
                break;

            case 'home':
                $this->goto = System::getHomepageUrl();
                break;

            default:
                //if (empty($this->goto)) {
                    $this->goto = $goto;
                //}
        }

        if (!$data) {
            return false;
        }

        return $view->redirect($this->goto);
    }

    /**
     * Setters and getters
     */
    public function pmSetUp($id, $tid, $pubtype=null, $pubfields=null)
    {
        $this->id = $id;

        $this->tid = $tid;
        // pubtype
        if ($pubtype) {
            $this->pubtype = $pubtype;
        } else {
            $this->pubtype = PageMaster_Util::getPubType($tid);
        }
        // pubfields
        if ($pubfields) {
            $this->pubfields = $pubfields;
        } else {
            $this->pubfields = PageMaster_Util::getPubFields($tid);
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

    protected function getRelationType($alias)
    {
        $result = false;

        $relationtypes1 = PageMaster_Util::getRelations($this->tid, true);
        $relationtypes2 = PageMaster_Util::getRelations($this->tid, false);

        foreach ($relationtypes1 as $relation) {
            if ($relation['alias1'] == $alias) {
                $result = array(
                    'type' => $relation['type'],
                    'own'  => true
                );
                break;
            }
        }
        if ($result === false) {
            foreach ($relationtypes1 as $relation) {
                if ($relation['alias2'] == $alias) {
                    $result = array(
                        'type' => $relation['type'],
                        'own'  => false
                    );
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Publication data handlers
     */
    private function pubDefault()
    {
        $this->pub['core_pid']         = NULL;
        $this->pub['core_author']      = UserUtil::getVar('uid');
        $this->pub['core_hitcount']    = 0;
        $this->pub['core_revision']    = 0;
        $this->pub['core_language']    = '';
        $this->pub['core_online']      = 0;
        $this->pub['core_indepot']     = 0;
        $this->pub['core_showinmenu']  = 0;
        $this->pub['core_showinlist']  = 1;
        $this->pub['core_publishdate'] = NULL;
        $this->pub['core_expiredate']  = NULL;
    }

    private function pubAssign($pubdata)
    {
        $this->pub['core_pid']         = $pubdata['core_pid'];
        $this->pub['core_author']      = $pubdata['core_author'];
        $this->pub['core_hitcount']    = $pubdata['core_hitcount'];
        $this->pub['core_revision']    = $pubdata['core_revision'];
        $this->pub['core_language']    = $pubdata['core_language'];
        $this->pub['core_online']      = $pubdata['core_online'];
        $this->pub['core_indepot']     = $pubdata['core_indepot'];
        $this->pub['core_showinmenu']  = $pubdata['core_showinmenu'];
        $this->pub['core_showinlist']  = $pubdata['core_showinlist'];
        $this->pub['core_publishdate'] = $pubdata['core_publishdate'];
        $this->pub['core_expiredate']  = $pubdata['core_expiredate'];
    }

    private function pubExtract(&$data)
    {
        if (!empty($this->id)) {
            $data['id'] = $this->id;
        }
        $data['tid']              = $this->tid;
        $data['core_pid']         = isset($data['core_pid']) && !empty($data['core_pid']) ? $data['core_pid'] : $this->pub['core_pid'];
        $data['core_author']      = $this->pub['core_author'];
        $data['core_revision']    = $this->pub['core_revision'];
        $data['core_hitcount']    = $this->pub['core_hitcount'];
        $data['core_language']    = isset($data['core_language']) ? $data['core_language'] : $this->pub['core_language'];
        $data['core_online']      = $this->pub['core_online'];
        $data['core_indepot']     = $this->pub['core_indepot'];
        $data['core_showinmenu']  = isset($data['core_showinmenu']) ? $data['core_showinmenu'] : $this->pub['core_showinmenu'];
        $data['core_showinlist']  = isset($data['core_showinlist']) ? $data['core_showinlist'] : $this->pub['core_showinlist'];
        $data['core_publishdate'] = isset($data['core_publishdate']) ? $data['core_publishdate'] : $this->pub['core_publishdate'];
        $data['core_expiredate']  = isset($data['core_expiredate']) ? $data['core_expiredate'] : $this->pub['core_expiredate'];
    }
}
