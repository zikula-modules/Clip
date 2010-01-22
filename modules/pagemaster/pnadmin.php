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
        $modvars = pnModGetVar('pagemaster');
        $render->assign('uploadpath', $modvars['uploadpath']);

        // Upload dir check
        $siteroot = substr(pnServerGetVar('DOCUMENT_ROOT'), 0, -1).pnGetBaseURI().'/';
        $render->assign('siteroot', DataUtil::formatForDisplay($siteroot));

        if (file_exists($modvars['uploadpath'].'/')) {
            $render->assign('updirstatus', 1); // exists
            if (is_dir($modvars['uploadpath'].'/')) {
                $render->assign('updirstatus', 2); // is a directory
                if (is_writable($modvars['uploadpath'].'/')) {
                    $render->assign('updirstatus', 3); // is writable
                }
            }
        } else {
            $render->assign('updirstatus', 0); // doesn't exists
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

            // upload path
            // remove the siteroot if was included
            $siteroot = substr(pnServerGetVar('DOCUMENT_ROOT'), 0, -1).pnGetBaseURI().'/';
            $data['uploadpath'] = str_replace($siteroot, '', $data['uploadpath']);
            if (StringUtil::right($data['uploadpath'], 1) == '/') {
                $data['uploadpath'] = StringUtil::left($data['uploadpath'], strlen($data['uploadpath']) - 1);
            }
            pnModSetVar('pagemaster', 'uploadpath', $data['uploadpath']);

            return pnRedirect(pnModURL('pagemaster', 'admin', 'modifyconfig'));

        } elseif ($args['commandName'] == 'cancel') {
            return pnRedirect(pnModURL('pagemaster', 'admin'));
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
        $dom = ZLanguage::getModuleDomain('pagemaster');
        $tid = FormUtil::getPassedValue('tid');

        if (!empty($tid) &&  is_numeric($tid)) {
            $this->tid = $tid;
            $pubtype   = PMgetPubType($tid);
            $pubfields = PMgetPubFields($tid);
            $pubarr[] = array (
                'text'  => '',
                'value' => ''
            );
            $pubarr[] = array (
                'text'  => __('Creation date', $dom),
                'value' => 'cr_date'
            );
            $pubarr[] = array (
                'text'  => __('Update date', $dom),
                'value' => 'lu_date'
            );
            $pubarr[] = array (
                'text'  => __('Creator', $dom),
                'value' => 'core_author'
            );
            $pubarr[] = array (
                'text'  => __('Updater', $dom),
                'value' => 'lu_uid'
            );
           $pubarr[] = array (
                'text'  => __('Publish Date', $dom),
                'value' => 'pm_publishdate'
            );
            $pubarr[] = array (
                'text'  => __('Expire Date', $dom),
                'value' => 'pm_expiredate'
            );
            $pubarr[] = array (
                'text'  => __('English', $dom),
                'value' => 'pm_language'
            );
            $pubarr[] = array (
                'text'  => __('Number of Clicks', $dom),
                'value' => 'pm_hitcount'
            );

            $fieldnames = array_keys($pubfields);
            foreach ($fieldnames as $fieldname) {
                $pubarr[] = array (
                    'text'  => $fieldname,
                    'value' => $fieldname
                );
            }

            $render->assign($pubtype);
            $render->assign('pubfields', $pubarr);
        }

        $pubtypes = DBUtil::selectObjectArray('pagemaster_pubtypes');
        $render->assign('pubtypes', $pubtypes);

        $workflows = PMgetWorkflowsOptionList();
        $render->assign('pmWorkflows', $workflows);

        return true;
    }

    /**
     * Command handler
     */
    function handleCommand(&$render, &$args)
    {
        $dom = ZLanguage::getModuleDomain('pagemaster');
        $data = $render->pnFormGetValues();
        $data['tid'] = $this->tid;

        if ($args['commandName'] == 'updatetabledef') {
            $ret = pnModAPIFunc('pagemaster', 'admin', 'updatetabledef',
                                array('tid' => $data['tid']));
            if (!$ret) {
                return LogUtil::registerError(__('Error! Update attempt failed.', $dom));
            }
            LogUtil::registerStatus(__('Done! Item updated.', $dom));

        } elseif ($args['commandName'] == 'create') {
            if (!$render->pnFormIsValid()) {
                return false;
            }
            if (!isset($data['urltitle']) || empty($data['urltitle'])) {
                $data['urltitle'] = DataUtil::formatPermalink($data['title']);
            }

            if (empty($data['filename'])) {
                $data['filename'] = $data['title'];
            }
            if (empty($data['formname'])) {
                $data['formname'] = $data['title'];
            }

            if (empty($this->tid)) {
                DBUtil::insertObject($data, 'pagemaster_pubtypes');
            } else {
                DBUtil::updateObject($data, 'pagemaster_pubtypes', 'pm_tid='.$this->tid);
            }
            // report a successful update
            LogUtil::registerStatus(__('Done! Item updated.', $dom));

        } elseif ($args['commandName'] == 'delete') {
            DBUtil::deleteObject(null, 'pagemaster_pubtypes', 'pm_tid='.$this->tid);
            DBUtil::deleteObject(null, 'pagemaster_pubfields', 'pm_tid='.$this->tid);
            DBUtil::dropTable('pagemaster_pubdata' . $this->tid);
            LogUtil::registerStatus(__('Done! Item deleted.', $dom));
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
        $dom = ZLanguage::getModuleDomain('pagemaster');
        $tid = FormUtil::getPassedValue('tid');
        $id  = FormUtil::getPassedValue('id');

        // validation check
        if (empty($tid) || !is_numeric($tid)) {
            LogUtil::registerError(__f('%s not set', 'tid', $dom));
            $render->pnFormRedirect(pnModURL('pagemaster', 'admin', 'main'));
        }
        $this->tid = $tid;

        if (!empty($id)) {
            $this->id = $id;
            $pubfield = DBUtil::selectObjectByID('pagemaster_pubfields', $id);
            $render->assign($pubfield);
        }

        $pubfields = DBUtil::selectObjectArray('pagemaster_pubfields', 'pm_tid = '.$tid, 'pm_lineno', -1, -1, 'name');
        $render->assign('pubfields', $pubfields);
        $render->assign('tid', $tid);
        return true;
    }

    /**
     * Command handler
     */
    function handleCommand(&$render, &$args)
    {
        $dom = ZLanguage::getModuleDomain('pagemaster');
        $data = $render->pnFormGetValues();

        $data['id']        = $this->id;
        $data['tid']       = $this->tid;
        $plugin            = PMgetPlugin($data['fieldplugin']);
        $data['fieldtype'] = $plugin->columnDef;

        if ($args['commandName'] == 'delete') {
            DBUtil::deleteObject($data, 'pagemaster_pubfields');
            LogUtil::registerStatus(__('Done! Item deleted.', $dom));

        } elseif ($args['commandName'] == 'create') {
            if (!$render->pnFormIsValid()) {
                return false;
            }
            if ($data['istitle'] == 1) {
                $istitle = array ('istitle' => '0');
                DBUtil::updateObject($istitle, 'pagemaster_pubfields', 'pm_tid = '.$data['tid']);
            }

            if (empty($this->id)) {
                $where = 'pm_name = \''.$data['name'].'\' AND pm_tid = '.$data['tid'];
            } else {
                $where = 'pm_id <> '.$this->id.' AND pm_name = \''.$data['name'].'\' AND pm_tid = '.$data['tid'];
            }

            $nameUnique = DBUtil::selectFieldMax('pagemaster_pubfields', 'id', 'COUNT', $where);
            if ($nameUnique > 0) {
                return LogUtil::registerError(__('Name has to be unique', $dom));
            }

            if (empty($this->id)) {
                $max_rowID = DBUtil::selectFieldMax('pagemaster_pubfields', 'id', 'MAX', 'pm_tid = '.$data['tid']);
                $data['lineno'] = $max_rowID + 1;
                if ($max_rowID == 1) {
                    $data['istitle'] = 1;
                }
                DBUtil::insertObject($data, 'pagemaster_pubfields');
                LogUtil::registerStatus(__('Done! Item created.', $dom));

            } else {
                DBUtil::updateObject($data, 'pagemaster_pubfields', 'pm_id = '.$this->id);
                LogUtil::registerStatus(__('Done! Item updated.', $dom));
            }
        }

        $render->pnFormRedirect(pnModURL('pagemaster', 'admin', 'editpubfields',
                                         array('tid' => $data['tid'])));
        return true;
    }
}

/**
 * Creates a new pubtype
 * @author gf
 */
function pagemaster_admin_create_tid()
{
    $dom = ZLanguage::getModuleDomain('pagemaster');
    if (!SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerError(__('No permission', $dom));
    }

    $render = FormUtil::newpnForm('pagemaster');
    return $render->pnFormExecute('pagemaster_admin_create_tid.htm', new pagemaster_admin_pubtypesHandler());
}

function pagemaster_admin_main()
{
    $dom = ZLanguage::getModuleDomain('pagemaster');
    if (!SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerError(__('No permission', $dom));
    }

    $pubtypes = DBUtil::selectObjectArray('pagemaster_pubtypes');

    $render = pnRender::getInstance('pagemaster');
    $render->assign('pubtypes', $pubtypes);

    return $render->fetch('pagemaster_admin_main.htm');
}

function pagemaster_admin_editpubfields()
{
    $dom = ZLanguage::getModuleDomain('pagemaster');
    if (!SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerError(__('No permission', $dom));
    }

    $render = FormUtil::newpnForm('pagemaster');

    return $render->pnFormExecute('pagemaster_admin_edit_pubfields.htm', new pagemaster_admin_pubfieldsHandler());
}

function pagemaster_admin_publist($args=array())
{
    $dom = ZLanguage::getModuleDomain('pagemaster');
    $tid          = isset($args['tid']) ? $args['tid'] : FormUtil::getPassedValue('tid');
    $startnum     = isset($args['startnum']) ? $args['startnum'] : FormUtil::getPassedValue('startnum');
    $itemsperpage = isset($args['itemsperpage']) ? $args['itemsperpage'] : FormUtil::getPassedValue('itemsperpage', 50);
    $orderby      = isset($args['orderby']) ? $args['orderby'] : FormUtil::getPassedValue('orderby', 'pm_pid');

    // Validate the essential parameyers
    if (empty($tid) || !is_numeric($tid)) {
        return LogUtil::registerError(__f('Missing argument [%s]', 'tid', $dom));
    }

    if (!SecurityUtil::checkPermission('pagemaster::', $tid.'::', ACCESS_EDIT)) {
        return LogUtil :: registerError(__('No permission', $dom));
    }

    $tablename = 'pagemaster_pubdata'.$tid;
    if (!in_array(DBUtil::getLimitedTablename($tablename), DBUtil::metaTables())) {
        return LogUtil::registerError(__('The table of this publication type seems not to exist. Please, update the DB Tables at the bottom of this form.', $dom), null, pnModURL('pagemaster', 'admin', 'create_tid', array('tid' => $tid), null, 'pn-maincontent'));
    }

    $old_orderby = $orderby;
    $core_title = DBUtil::selectField('pagemaster_pubfields', 'name', "pm_tid = '$tid' AND pm_istitle = '1'");
    if (substr($orderby, 0, 10) == 'core_title') {
        $orderby = str_replace('core_title', $core_title, $orderby);
    }

    $publist  = DBUtil::selectObjectArray($tablename, 'pm_indepot = 0', str_replace(':',' ', $orderby), $startnum-1, $itemsperpage);
    if ($publist !== false) {
        $pubcount = (int)DBUtil::selectObjectCount($tablename, 'pm_indepot = 0');
        foreach ($publist as $key => $pub) {
            $workflow = WorkflowUtil::getWorkflowForObject($pub, $tablename, 'id', 'pagemaster');
            $publist[$key] = $pub;
        }
    } else {
        $publist  = array();
        $pubcount = 0;
    }

    // fetch the output
    $render = pnRender::getInstance('pagemaster');
    $render->assign('core_tid', $tid);
    $render->assign('orderby', $old_orderby);
    $render->assign('core_title', $core_title);
    $render->assign('publist', $publist);

    $render->assign('pager', array('numitems'     => $pubcount,
                                   'itemsperpage' => $itemsperpage));

    return $render->fetch('pagemaster_admin_publist.htm');
}

function pagemaster_admin_history()
{
    $dom = ZLanguage::getModuleDomain('pagemaster');
    $pid = FormUtil::getPassedValue('pid');
    $tid = FormUtil::getPassedValue('tid');

    if (empty($tid) || !is_numeric($tid)) {
        return LogUtil::registerError(__f('Missing argument [%s]', 'tid', $dom));
    }
    if (empty($pid) || !is_numeric($pid)) {
        return LogUtil::registerError(__f('Missing argument [%s]', 'pid', $dom));
    }

    if (!SecurityUtil::checkPermission('pagemaster::', "$tid:$pid:", ACCESS_ADMIN)) {
        return LogUtil::registerError(__('No permission', $dom));
    }

    $tablename = 'pagemaster_pubdata'.$tid;
    $publist = DBUtil::selectObjectArray($tablename, 'pm_pid = '.$pid, 'pm_revision desc');
    foreach ($publist as $key => $pub) {
        $workflow = WorkflowUtil::getWorkflowForObject($pub, $tablename, 'id', 'pagemaster');
        $publist[$key] = $pub;
    }
    $core_title = DBUtil::selectField('pagemaster_pubfields', 'name', "pm_tid = '$tid' AND pm_istitle = '1'");

    $render = pnRender::getInstance('pagemaster');
    $render->assign('core_tid', $tid);
    $render->assign('core_title', $core_title);
    $render->assign('publist', $publist);

    return $render->fetch('pagemaster_admin_history.htm');
}

function pagemaster_admin_modifyconfig()
{
    $dom = ZLanguage::getModuleDomain('pagemaster');
    if (!SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerError(__('No permission', $dom));
    }
    $render = FormUtil::newpnForm('pagemaster');
    return $render->pnFormExecute('pagemaster_admin_modifyconfig.htm', new pagemaster_admin_modifyconfigHandler());
}

function pagemaster_admin_showcode()
{
    $dom = ZLanguage::getModuleDomain('pagemaster');
    if (!SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerError(__('No permission', $dom));
    }

    $tid  = FormUtil::getPassedValue('tid');
    $mode = FormUtil::getPassedValue('mode');

    if (empty($tid) || !is_numeric($tid)) {
        return LogUtil::registerError(__f('Missing argument [%s]', 'tid', $dom));
    }
    if (empty($mode)) {
        return LogUtil::registerError(__f('Missing argument [%s]', 'mode', $dom));
    }

    $pubtype   = PMgetPubType($tid);
    $pubfields = DBUtil::selectObjectArray('pagemaster_pubfields', "pm_tid = $tid", 'pm_lineno', -1, -1, 'name');

    // get the code depending of the mode
    if ($mode == 'input') {
        $code = PMgen_editpub_tplcode($tid, $pubfields, $pubtype);

    } elseif ($mode == 'outputfull') {
        include_once('includes/pnForm.php');
        $tablename = 'pagemaster_pubdata'.$tid;
        $id = DBUtil::selectFieldMax($tablename, 'id', 'MAX');
        if ($id <= 0) {
            return LogUtil::registerError(__('There has to be at least one publication, to generate the template code.', $dom));
        }
        $pubdata = pnModAPIFunc('pagemaster', 'user', 'getPub',
                                array('tid' => $tid,
                                      'id'  => $id));
        $code = PMgen_viewpub_tplcode($tid, $pubdata, $pubtype, $pubfields);

    } elseif ($mode == 'outputlist') {
        $code = file_get_contents('modules/pagemaster/pntemplates/generic_publist.htm');
    }

    // code cleaning
    $code = DataUtil::formatForDisplay($code);
    $code = str_replace("\n", '<br/>',$code);

    // generate the output
    $render = pnRender::getInstance('pagemaster');
    $render->assign('mode', $mode);
    $render->assign('pubtype', $pubtype);
    $render->assign('pubfields', $pubfields);

    $render->assign('code', $code);

    return $render->fetch('pagemaster_admin_showcode.htm');
}
