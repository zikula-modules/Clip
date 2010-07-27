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
    var $id;
    var $core_pid;
    var $core_author;
    var $core_hitcount;
    var $core_revision;
    var $core_language;
    var $core_online;
    var $core_indepot;
    var $core_showinmenu;
    var $core_showinlist;
    var $core_publishdate;
    var $core_expiredate;

    var $tid;
    var $tablename;
    var $pubtype;
    var $pubfields;
    var $itemurl;
    var $referer;
    var $goto;

    function initialize($view)
    {
        // process the input parameters
        $this->tid  = (isset($this->pubtype['tid']) && $this->pubtype['tid'] > 0) ? $this->pubtype['tid'] : FormUtil::getPassedValue('tid');
        $this->goto = FormUtil::getPassedValue('goto', '');

        // initialize the publication array
        $pubdata = array();

        // process a new or existing pub, and it's available actions
        if (!empty($this->id)) {
            $pubdata = DBUtil::selectObjectByID($this->tablename, $this->id, 'id');

            $this->pubAssign($pubdata);

            $pubdata['core_title'] = $pubdata[$this->pubtype['titlefield']];

            $actions = Zikula_Workflow_Util::getActionsForObject($pubdata, $this->tablename, 'id', 'PageMaster');
        } else {
            // initial values
            $this->pubDefault();

            $actions = Zikula_Workflow_Util::getActionsByStateArray(str_replace('.xml', '', $this->pubtype['workflow']), 'PageMaster');
        }

        // if there are no actions the user is not allowed to change / submit / delete something.
        // We will redirect the user to the overview page
        if (count($actions) < 1) {
            LogUtil::registerError($this->__('No workflow actions found. This can be a permissions issue.'));

            return $view->redirect(ModUtil::url('PageMaster', 'user', 'main', array('tid' => $this->tid)));
        }

        // translate any gt string on the action parameters
        foreach (array_keys($actions) as $aid) {
            if (isset($actions[$aid]['parameters'])) {
                foreach (array_keys($actions[$aid]['parameters']) as $pname) {
                    foreach ($actions[$aid]['parameters'][$pname] as $k => $v) {
                        if (strpos($k, '__') === 0) {
                            unset($actions[$aid]['parameters'][$pname][$k]);
                            $k = substr($k, 2);
                            $actions[$aid]['parameters'][$pname][$k] = $this->__($v);
                        }
                    }
                }
                if (!isset($actions[$aid]['parameters']['button']['title'])) {
                    $actions[$aid]['parameters']['button']['title'] = $this->__($actions[$aid]['description']);
                }
            }
        }

        // check for set_* default values
        $fieldnames = array_keys($this->pubfields);

        foreach ($fieldnames as $fieldname) {
            $val = FormUtil::getPassedValue('set_'.$fieldname, '');
            if (!empty($val)) {
                $pubdata[$fieldname] = $val;
            }
        }

        // add the pub information to the render if exists
        if (count($pubdata) > 0) {
            $view->assign($pubdata);
        }

        // stores the first referer and the item URL
        if (empty($this->referer)) {
            $viewurl = ModUtil::url('PageMaster', 'user', 'main', array('tid' => $this->tid), null, null, true);
            $this->referer = System::serverGetVar('HTTP_REFERER', $viewurl);
        }

        if (!empty($this->id)) {
            $this->itemurl = ModUtil::url('PageMaster', 'user', 'display', array('tid' => $this->tid, 'pid' => $this->core_pid), null, null, true);
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
        $this->pubExtract($data);

        // perform the command
        $data = ModUtil::apiFunc('PageMaster', 'user', 'edit',
                                 array('data'        => $data,
                                       'commandName' => $args['commandName'],
                                       'pubfields'   => $this->pubfields,
                                       'schema'      => str_replace('.xml', '', $this->pubtype['workflow'])));

        // see http://www.smarty.net/manual/en/caching.groups.php
        $vw = Zikula_View::getInstance('PageMaster');
        // clear the view of the current publication
        $vw->clear_cache(null, 'display'.$this->tid.'|'.$this->core_pid);
        // clear all page of publist
        $vw->clear_cache(null, 'view'.$this->tid);
        unset($vw);

        // core operations processing
        $goto = $this->itemurl;
        $ops  = $data['core_operations'];
        if ($data['core_indepot'] == 1 || (isset($ops['deletePub']) && $ops['deletePub'])) {
            // if the item moved to the depot or was deleted
            $urltid = ModUtil::url('PageMaster', 'user', 'main', array('tid' => $data['tid']));
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

        } else {
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
                $this->goto = ModUtil::url('PageMaster', 'user', 'main', array('tid' => $data['tid']));
                break;

            case 'home':
                $this->goto = System::getHomepageUrl();
                break;

            default:
                //if (empty($this->goto)) {
                    $this->goto = $goto;
                //}
        }

        if (empty($data)) {
            return false;
        }

        return $view->redirect($this->goto);
    }

    /**
     * Publication data handlers
     */
    function pubDefault()
    {
        $this->core_pid         = NULL;
        $this->core_author      = UserUtil::getVar('uid');
        $this->core_hitcount    = 0;
        $this->core_revision    = 0;
        $this->core_language    = '';
        $this->core_online      = 0;
        $this->core_indepot     = 0;
        $this->core_showinmenu  = 0;
        $this->core_showinlist  = 1;
        $this->core_publishdate = NULL;
        $this->core_expiredate  = NULL;
    }

    function pubAssign($pubdata)
    {
        $this->core_pid         = $pubdata['core_pid'];
        $this->core_author      = $pubdata['core_author'];
        $this->core_hitcount    = $pubdata['core_hitcount'];
        $this->core_revision    = $pubdata['core_revision'];
        $this->core_language    = $pubdata['core_language'];
        $this->core_online      = $pubdata['core_online'];
        $this->core_indepot     = $pubdata['core_indepot'];
        $this->core_showinmenu  = $pubdata['core_showinmenu'];
        $this->core_showinlist  = $pubdata['core_showinlist'];
        $this->core_publishdate = $pubdata['core_publishdate'];
        $this->core_expiredate  = $pubdata['core_expiredate'];
    }

    function pubExtract(&$data)
    {
        $data['tid']              = $this->tid;
        $data['id']               = $this->id;
        $data['core_pid']         = $this->core_pid;
        $data['core_author']      = $this->core_author;
        $data['core_revision']    = $this->core_revision;
        $data['core_hitcount']    = $this->core_hitcount;
        $data['core_language']    = isset($data['core_language']) ? $data['core_language'] : $this->core_language;
        $data['core_online']      = $this->core_online;
        $data['core_indepot']     = $this->core_indepot;
        $data['core_showinmenu']  = isset($data['core_showinmenu']) ? $data['core_showinmenu'] : $this->core_showinmenu;
        $data['core_showinlist']  = isset($data['core_showinlist']) ? $data['core_showinlist'] : $this->core_showinlist;
        $data['core_publishdate'] = isset($data['core_publishdate']) ? $data['core_publishdate'] : $this->core_publishdate;
        $data['core_expiredate']  = isset($data['core_expiredate']) ? $data['core_expiredate'] : $this->core_expiredate;
    }
}
