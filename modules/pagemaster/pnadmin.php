<?php
/**
 * PageMaster
 *
 * @copyright (c) 2008, PageMaster Team
 * @link        http://code.zikula.org/projects/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package     Zikula_3rd_party_Modules
 * @subpackage  pagemaster
 */

Loader::includeOnce('modules/pagemaster/common.php');

/**
 * pnForm handler for updating module vars
 * @author kundi
 */
class pagemaster_admin_modifyconfigHandler
{
    /**
     * Initialize function
     */
    function initialize(&$render)
    {
        $uploadpath = pnModGetVar('pagemaster', 'uploadpath');
        $render->assign('uploadpath', $uploadpath);

        // Check taken from MediaAttach
        if (is_dir($$uploadpath . '/') &&  is_writable($uploadpath . '/')) {        
            $render->assign('updirok', '1');
        } else {
            $render->assign('updirok', '0');
        }
        return true;
    }

    /**
     * Command handler
     */
    function handleCommand(&$render, &$args)
    {
        $data = $render->pnFormGetValues();
        if ($args['commandName'] == 'modify') {
            $data = $render->pnFormGetValues();
            pnModSetVar('pagemaster', 'uploadpath', $data['uploadpath']);
            return true;
        }
        return true;
    }
}

/**
 * pnForm handler for updating publication types
 * @author kundi
 */
class pagemaster_admin_pubtypesHandler
{
    var $tid;

    /**
     * Initialize function
     */
    function initialize(&$render)
    {
        $tid = FormUtil::getPassedValue('tid');
        if ($tid <> "") {
            $this->tid = $tid;
            $pubtype   = DBUtil::selectObjectByID('pagemaster_pubtypes', $tid, 'tid');
            $pubfields = DBUtil::selectObjectArray('pagemaster_pubfields', 'pm_tid = '.$tid);
            $pubarr[] = array (
                'text'  => '',
                'value' => ''
            );
            $pubarr[] = array (
                'text'  => _PAGEMASTER_CREATIONDATE,
                'value' => 'cr_date'
            );
            $pubarr[] = array (
                'text'  => _PAGEMASTER_EDITDATE,
                'value' => 'lu_date'
            );
            $pubarr[] = array (
                'text'  => _PAGEMASTER_CREATOR,
                'value' => 'cr_uid'
            );
            $pubarr[] = array (
                'text'  => _PAGEMASTER_UPDATER,
                'value' => 'lu_uid'
            );

            foreach ($pubfields as $pubfield) {
                $pubarr[] = array (
                    'text' => $pubfield['name'],
                    'value' => $pubfield['name']
                );
            }

            $render->assign($pubtype);
            $render->assign('pubfields', $pubarr);
        }
        $pubtypes = DBUtil :: selectObjectArray("pagemaster_pubtypes");
        $render->assign('pubtypes', $pubtypes);
        $workflows = pagemasterGetWorkflowsOptionList();
        $render->assign('pmWorkflows', $workflows);
        return true;
    }

    /**
     * Command handler
     */
    function handleCommand(&$render, &$args)
    {
        $data = $render->pnFormGetValues();
        $data['tid'] = $this->tid;

        if ($args['commandName'] == 'updatetabledef') {
            $ret = pnModAPIFunc('pagemaster', 'admin', 'updatetabledef',
                                array('tid' => $data[tid]));
            if (!$ret) {
                return LogUtil::registerError(_UPDATEFAILED);
            }
            LogUtil::registerStatus(_UPDATESUCCEDED);

        } elseif ($args['commandName'] == 'create') {
            if (!$render->pnFormIsValid()) {
                return false;
            }
            if ($data['filename'] == '' ) {
                $data['filename'] = $data['title'];
            }
            if ($data['formname'] == '' ) {
                $data['formname'] = $data['title'];
            }
            if ($this->tid == "") {
                DBUtil::insertObject($data, 'pagemaster_pubtypes');
            } else {
                DBUtil::updateObject($data, 'pagemaster_pubtypes', 'pm_tid='.$this->tid);
            }
            LogUtil::registerStatus(_UPDATESUCCEDED);

        } elseif ($args['commandName'] == 'delete') {
            DBUtil::deleteObject(null, 'pagemaster_pubtypes', 'pm_tid='.$this->tid);
            DBUtil::deleteObject(null, 'pagemaster_pubfields', 'pm_tid='.$this->tid);
            DBUtil::dropTable('pagemaster_pubdata' . $this->tid);
            LogUtil::registerStatus(_DELETESUCCEDED);
        }
        return $render->pnFormRedirect(pnModURL('pagemaster', 'admin', 'main'));
    }
}

/**
 * pnForm handler for updating publication fields
 * @author kundi
 */
class pagemaster_admin_pubfieldsHandler
{
    var $tid;
    var $id;

    /**
     * Initialize function
     */
    function initialize(&$render)
    {
        $tid = FormUtil::getPassedValue('tid');
        $id  = FormUtil::getPassedValue('id');
        $this->tid = $tid;

        if ($id <> "") {
            $this->id = $id;
            $pubfield = DBUtil::selectObjectByID('pagemaster_pubfields', $id);
            $render->assign($pubfield);
        }

        $pubfields = DBUtil::selectObjectArray('pagemaster_pubfields', 'pm_tid = '.$tid, 'pm_lineno');
        $render->assign('pubfields', $pubfields);
        $render->assign('tid', $tid);
        if ($tid == '') {
            LogUtil::registerError(pnML('_PAGEMASTER_VARNOTSET', array('var' => 'tid')));
            $render->pnFormRedirect(pnModURL('pagemaster', 'admin', 'main'));
        }
        return true;
    }

    /**
     * Command handler
     */
    function handleCommand(&$render, &$args)
    {
        $data = $render->pnFormGetValues();

        $data[id]  = $this->id;
        $data[tid] = $this->tid;
        $file   = $data['fieldplugin'];
        $plugin = pagemasterGetPlugin($file);
        $data['fieldtype'] = $plugin->columnDef;

        if ($args['commandName'] == 'delete') {
            DBUtil::deleteObject($data, 'pagemaster_pubfields');
            LogUtil::registerStatus(_DELETESUCCEDED);

        } elseif ($args['commandName'] == 'create') {
            if (!$render->pnFormIsValid()) {
                return false;
            }
            if ($data['istitle'] == 1) {
                $istitle = array (
                    'istitle' => '0'
                );
                DBUtil::updateObject($istitle, 'pagemaster_pubfields', 'pm_tid = '.$data['tid']);
            }

            if ($this->id == "") {
                $where = 'pm_name = \''.$data['name'].'\' AND pm_tid = '.$data['tid'];
            } else {
                $where = 'pm_id <> '.$this->id.' AND pm_name = \''.$data['name'].'\' AND pm_tid = '.$data['tid'];
            }

            $nameUnique = DBUtil::selectFieldMax('pagemaster_pubfields', 'id', 'COUNT', $where);
            if ($nameUnique > 0) {
                return LogUtil :: registerError(_PAGEMASTER_NAMEUNIQUE);
            }

            if ($this->id == "") {
                $max_rowID = DBUtil::selectFieldMax('pagemaster_pubfields', 'id', 'MAX', 'pm_tid = '.$data['tid']);
                $data['lineno'] = $max_rowID +1;
                if ($max_rowID == 1) {
                    $data['istitle'] = 1;
                }
                DBUtil::insertObject($data, 'pagemaster_pubfields');
                LogUtil::registerStatus(_CREATESUCCEDED);

            } else {
                DBUtil::updateObject($data, 'pagemaster_pubfields', 'pm_id = '.$this->id);
                LogUtil::registerStatus(_UPDATESUCCEDED);
            }
        }

        $render->pnFormRedirect(pnModURL('pagemaster', 'admin', 'editpubfields',
                                         array ('tid' => $data['tid'])));
        return true;
    }
}

/**
 * Creates a new pubtype
 * @author gf
 */
function pagemaster_admin_create_tid()
{
    if (!SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerError(_NOT_AUTHORIZED);
    }
    $render = FormUtil::newpnForm('pagemaster');
    return $render->pnFormExecute('pagemaster_admin_create_tid.htm', new pagemaster_admin_pubtypesHandler());
}

function pagemaster_admin_main()
{
    if (!SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerError(_NOT_AUTHORIZED);
    }

    $pubtypes = DBUtil::selectObjectArray('pagemaster_pubtypes');

    $render = pnRender::getInstance('pagemaster');
    $render->assign('pubtypes', $pubtypes);

    return $render->fetch('pagemaster_admin_main.htm');
}

function pagemaster_admin_editpubfields()
{
    if (!SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerError(_NOT_AUTHORIZED);
    }

    $render = FormUtil::newpnForm('pagemaster');

    PageUtil::setVar('javascript', "modules/pagemaster/pnjavascript/handletypedata.js");

    return $render->pnFormExecute('pagemaster_admin_edit_pubfields.htm', new pagemaster_admin_pubfieldsHandler());
}

function pagemaster_admin_publist()
{
    $tid = FormUtil::getPassedValue('tid');

    if ($tid == '') {
        return LogUtil::registerError(pnML('_PAGEMASTER_MISSINGARG', array('arg' => 'tid')));
    }

    if (!SecurityUtil::checkPermission('pagemaster::', $tid.'::', ACCESS_EDIT)) {
        return LogUtil :: registerError(_NOT_AUTHORIZED);
    }

    $tablename = 'pagemaster_pubdata'.$tid;
    $publist   = DBUtil::selectObjectArray($tablename, 'pm_indepot = 0', 'pm_pid, pm_id');
    foreach ($publist as $key => $pub) {
        $workflow = WorkflowUtil::getWorkflowForObject($pub, $tablename, 'id', 'pagemaster');
        $publist[$key] = $pub;
    }

    $render = FormUtil::newpnForm('pagemaster');
    $render->assign('core_tid', $tid);
    $render->assign('publist', $publist);

    return $render->fetch('pagemaster_admin_publist.htm');
}

function pagemaster_admin_history()
{
    $pid = FormUtil::getPassedValue('pid');
    $tid = FormUtil::getPassedValue('tid');

    if ($tid == '') {
        return LogUtil::registerError(pnML('_PAGEMASTER_MISSINGARG', array('arg' => 'tid')));
    }
    if ($pid == '') {
        return LogUtil::registerError(pnML('_PAGEMASTER_MISSINGARG', array('arg' => 'pid')));
    }

    if (!SecurityUtil::checkPermission('pagemaster::', "$tid:$pid:", ACCESS_ADMIN)) {
        return LogUtil::registerError(_NOT_AUTHORIZED);
    }

    $tablename = 'pagemaster_pubdata'.$tid;
    $publist = DBUtil::selectObjectArray($tablename, 'pm_pid = '.$pid, 'pm_id');
    foreach ($publist as $key => $pub) {
        $workflow = WorkflowUtil::getWorkflowForObject($pub, $tablename, 'id', 'pagemaster');
        $publist[$key] = $pub;
    }

    $render = FormUtil::newpnForm('pagemaster');
    $render->assign('core_tid', $tid);
    $render->assign('publist', $publist);

    return $render->fetch('pagemaster_admin_history.htm');
}

function pagemaster_admin_modifyconfig()
{
    if (!SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerError(_NOT_AUTHORIZED);
    }
    $render = FormUtil::newpnForm('pagemaster');
    return $render->pnFormExecute('pagemaster_admin_modifyconfig.htm', new pagemaster_admin_modifyconfigHandler());
}

function pagemaster_admin_showcode()
{
    if (!SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerError(_NOT_AUTHORIZED);
    }

    $tid  = FormUtil::getPassedValue('tid', '');
    $mode = FormUtil::getPassedValue('mode', '');

    if ($tid == '') {
        return LogUtil::registerError(pnML('_PAGEMASTER_MISSINGARG', array('arg' => 'tid')));
    }
    if ($mode == '') {
        return LogUtil::registerError(pnML('_PAGEMASTER_MISSINGARG', array('arg' => 'mode')));
    }

    $pubtype   = DBUtil::selectObjectByID('pagemaster_pubtypes', $tid, 'tid');
    $pubfields = DBUtil::selectObjectArray('pagemaster_pubfields', "pm_tid = $tid", 'pm_lineno');

    /* get the code depending of the mode */
    if ($mode == 'input') {
        $code = generate_editpub_template_code($tid, $pubfields, $pubtype);

    } elseif ($mode == 'outputfull') {
        include_once('includes/pnForm.php');
        $tablename = 'pagemaster_pubdata'.$tid;
        $id = DBUtil::selectFieldMax($tablename, 'id', 'MAX');
        if (!($id > 0)) {
            return LogUtil::registerError(_PAGEMASTER_ATLEASTONE);
        }
        $pubdata = pnModAPIFunc('pagemaster', 'user', 'getPub',
                                array('tid' => $tid,
                                      'id' => $id));
        $code = generate_viewpub_template_code($tid, $pubdata, $pubtype, $pubfields);

    } elseif ($mode == 'outputlist') {
        $code = file_get_contents('modules/pagemaster/pntemplates/publist_template.htm');
    }

    /* code cleaning */
    $code = DataUtil::formatForDisplay($code);
    $code = str_replace("\n", '<br/>',$code);

    /* generate the output */
    $render = pnRender :: getInstance('pagemaster');
    $render->assign('mode', $mode);
    $render->assign('pubtype', $pubtype);
    $render->assign('pubfields', $pubfields);

    $render->assign('code', $code);

    return $render->fetch('pagemaster_admin_showcode.htm');
}
