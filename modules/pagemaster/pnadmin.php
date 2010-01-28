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

Loader::includeOnce('modules/pagemaster/common.php');

/**
 * Creates a new pubtype
 * @author gf
 */
function pagemaster_admin_pubtype()
{
    if (!SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    // load the form handler
    Loader::LoadClass('pagemaster_admin_pubtypes', 'modules/pagemaster/classes/FormHandlers');

    // build the output
    $render = FormUtil::newpnForm('pagemaster');

    return $render->pnFormExecute('pagemaster_admin_pubtype.htm', new pagemaster_admin_pubtypes());
}

function pagemaster_admin_main()
{
    if (!SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    $pubtypes = DBUtil::selectObjectArray('pagemaster_pubtypes');

    // build the output
    $render = pnRender::getInstance('pagemaster');

    $render->assign('pubtypes', $pubtypes);

    return $render->fetch('pagemaster_admin_main.htm');
}

function pagemaster_admin_pubfields()
{
    if (!SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    // load the form handler
    Loader::LoadClass('pagemaster_admin_pubfields', 'modules/pagemaster/classes/FormHandlers');

    // build the output
    $render = FormUtil::newpnForm('pagemaster');

    return $render->pnFormExecute('pagemaster_admin_pubfields.htm', new pagemaster_admin_pubfields());
}

function pagemaster_admin_publist($args=array())
{
    $dom = ZLanguage::getModuleDomain('pagemaster');

    // get the input parameters
    $tid          = isset($args['tid']) ? $args['tid'] : FormUtil::getPassedValue('tid');
    $startnum     = isset($args['startnum']) ? $args['startnum'] : FormUtil::getPassedValue('startnum');
    $itemsperpage = isset($args['itemsperpage']) ? $args['itemsperpage'] : FormUtil::getPassedValue('itemsperpage', 50);
    $orderby      = isset($args['orderby']) ? $args['orderby'] : FormUtil::getPassedValue('orderby', 'pm_pid');

    // validate the essential parameters
    if (empty($tid) || !is_numeric($tid)) {
        return LogUtil::registerError(__f('Error! Missing argument [%s].', 'tid', $dom));
    }

    if (!SecurityUtil::checkPermission('pagemaster::', $tid.'::', ACCESS_EDIT)) {
        return LogUtil::registerPermissionError();
    }

    // db table check
    $tablename = 'pagemaster_pubdata'.$tid;
    if (!in_array(DBUtil::getLimitedTablename($tablename), DBUtil::metaTables())) {
        return LogUtil::registerError(__('Error! The table of this publication type seems not to exist. Please, update the DB Tables at the bottom of this form.', $dom),
                                      null,
                                      pnModURL('pagemaster', 'admin', 'pubtype', array('tid' => $tid), null, 'pn-maincontent'));
    }

    // orderby check
    $old_orderby = $orderby;
    $core_title  = DBUtil::selectField('pagemaster_pubfields', 'name', "pm_tid = '$tid' AND pm_istitle = '1'");
    if (substr($orderby, 0, 10) == 'core_title') {
        $orderby = str_replace('core_title', $core_title, $orderby);
    }

    // query the list
    $publist  = DBUtil::selectObjectArray($tablename, 'pm_indepot = 0', str_replace(':',' ', $orderby), $startnum-1, $itemsperpage);

    if ($publist !== false) {
        $pubcount = (int)DBUtil::selectObjectCount($tablename, 'pm_indepot = 0');
        // add the workflow information for each publication
        foreach (array_keys($publist) as $key) {
            WorkflowUtil::getWorkflowForObject($publist[$key], $tablename, 'id', 'pagemaster');
        }
    } else {
        $publist  = array();
        $pubcount = 0;
    }

    // build the output
    $render = pnRender::getInstance('pagemaster');

    $render->assign('core_tid',   $tid);
    $render->assign('core_title', $core_title);
    $render->assign('publist',    $publist);
    $render->assign('orderby',    $old_orderby);

    $render->assign('pager', array('numitems'     => $pubcount,
                                   'itemsperpage' => $itemsperpage));

    return $render->fetch('pagemaster_admin_publist.htm');
}

function pagemaster_admin_history()
{
    $dom = ZLanguage::getModuleDomain('pagemaster');

    // get the input parameters
    $pid = FormUtil::getPassedValue('pid');
    $tid = FormUtil::getPassedValue('tid');

    if (empty($tid) || !is_numeric($tid)) {
        return LogUtil::registerError(__f('Error! Missing argument [%s].', 'tid', $dom));
    }
    if (empty($pid) || !is_numeric($pid)) {
        return LogUtil::registerError(__f('Error! Missing argument [%s].', 'pid', $dom));
    }

    if (!SecurityUtil::checkPermission('pagemaster::', "$tid:$pid:", ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    $tablename = 'pagemaster_pubdata'.$tid;

    $publist = DBUtil::selectObjectArray($tablename, "pm_pid = '$pid'", 'pm_revision desc');

    foreach (array_keys($publist) as $key) {
        WorkflowUtil::getWorkflowForObject($publist[$key], $tablename, 'id', 'pagemaster');
    }

    $core_title = DBUtil::selectField('pagemaster_pubfields', 'name', "pm_tid = '$tid' AND pm_istitle = '1'");

    // build the output
    $render = pnRender::getInstance('pagemaster');

    $render->assign('core_tid',   $tid);
    $render->assign('core_title', $core_title);
    $render->assign('publist',    $publist);

    return $render->fetch('pagemaster_admin_history.htm');
}

function pagemaster_admin_modifyconfig()
{
    if (!SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    // load the form handler
    Loader::LoadClass('pagemaster_admin_modifyconfig', 'modules/pagemaster/classes/FormHandlers');

    // build the output
    $render = FormUtil::newpnForm('pagemaster');

    return $render->pnFormExecute('pagemaster_admin_modifyconfig.htm', new pagemaster_admin_modifyconfig());
}

function pagemaster_admin_showcode()
{
    if (!SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    $dom = ZLanguage::getModuleDomain('pagemaster');

    // get the input parameters
    $tid  = (int)FormUtil::getPassedValue('tid');
    $mode = FormUtil::getPassedValue('mode');

    // validate the essential parameters
    if (empty($tid) || !is_numeric($tid)) {
        return LogUtil::registerError(__f('Error! Missing argument [%s].', 'tid', $dom));
    }
    if (empty($mode)) {
        return LogUtil::registerError(__f('Error! Missing argument [%s].', 'mode', $dom));
    }

    $pubtype   = PMgetPubType($tid);
    $pubfields = PMgetPubFields($tid);

    // get the code depending of the mode
    switch ($mode)
    {
        case 'input':
            $code = PMgen_editpub_tplcode($tid, $pubfields, $pubtype);
            break;

        case 'outputfull':
            include_once('includes/pnForm.php');
            $tablename = 'pagemaster_pubdata'.$tid;
            $id = DBUtil::selectFieldMax($tablename, 'id', 'MAX');
            if ($id <= 0) {
                return LogUtil::registerError(__('There has to be at least one publication, to generate the template code.', $dom));
            }
            $pubdata = pnModAPIFunc('pagemaster', 'user', 'getPub',
                                    array('tid' => $tid,
                                          'id'  => $id,
                                          'handlePluginFields' => true));

            $code = PMgen_viewpub_tplcode($tid, $pubdata, $pubtype, $pubfields);
            break;

        case 'outputlist':
            $code = file_get_contents('modules/pagemaster/pntemplates/generic_publist.htm');
            break;
    }

    // code cleaning
    $code = DataUtil::formatForDisplay($code);
    $code = str_replace("\n", '<br />', $code);

    // generate the output
    $render = pnRender::getInstance('pagemaster');

    $render->assign('code',      $code);
    $render->assign('mode',      $mode);
    $render->assign('pubtype',   $pubtype);
    $render->assign('pubfields', $pubfields);

    return $render->fetch('pagemaster_admin_showcode.htm');
}
