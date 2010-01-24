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
 * pnForm handler for updating pubdata tables.
 *
 * @author kundi
 */
class pagemaster_user_dynHandler
{
    var $tid;
    var $core_author;
    var $core_pid;
    var $core_revision;
    var $id;
    var $pubfields;
    var $pubtype;
    var $tablename;
    var $goto;

    function initialize(&$render)
    {
        $dom = ZLanguage::getModuleDomain('pagemaster');
        $this->goto = FormUtil::getPassedValue('goto', '');

        if (!empty($this->id)) {
            $pubdata = DBUtil::selectObjectByID($this->tablename, $this->id, 'id');
            $this->core_author = $pubdata['core_author'];
            $this->core_pid = $pubdata['core_pid'];
            $this->core_revision = $pubdata['core_revision'];
            $actions = PmWorkflowUtil::getActionsForObject($pubdata, $this->tablename, 'id', 'pagemaster');
        } else {
            $pubdata = array();
            $this->core_author = pnUserGetVar('uid');
            $actions = PmWorkflowUtil::getActionsByState(str_replace('.xml', '', $this->pubtype['workflow']), 'pagemaster');
        }

        if ($this->pubtype['tid'] > 0) {
            $tid = $this->pubtype['tid'];
        } else {
            $tid = FormUtil::getPassedValue('tid');
        }
        // if there are no actions the user is not allowed to change / submit / delete something.
        // We will redirect the user to the overview page
        if (count($actions) < 1) {
            LogUtil::registerError(__('No workflow actions with permission found.', $dom));
            return $render->pnFormRedirect(pnModURL('pagemaster', 'user', 'main', array('tid' => $tid)));
        }

        // check for set_ default values
        $fieldnames = array_keys($this->pubfields);
        foreach ($fieldnames as $fieldname)
        {
            $val = FormUtil::getPassedValue('set_'.$fieldname, '');
            if (!empty($val)) {
                $pubdata[$fieldname] = $val;
            }
        }

        if (count($pubdata > 0)) {
            $render->assign($pubdata);
        }

        $render->assign('actions', $actions);
        return true;
    }

    function handleCommand(&$render, &$args)
    {
        if (!$render->pnFormIsValid()) {
            return false;
        }

        $data = $render->pnFormGetValues();
        $data['tid']           = $this->tid;
        $data['id']            = $this->id;
        $data['core_author']   = $this->core_author;
        $data['core_pid']      = $this->core_pid;
        $data['core_revision'] = $this->core_revision;
        $data = pnModAPIFunc('pagemaster', 'user', 'editPub',
                             array('data'        => $data,
                                   'commandName' => $args['commandName'],
                                   'pubfields'   => $this->pubfields,
                                   'schema'      => str_replace('.xml', '', $this->pubtype['workflow'])));

        // see http://www.smarty.net/manual/en/caching.groups.php
        $pnr=pnRender::getInstance('pagemaster') ; 
        // clear the view of the current publication
		$pnr->clear(null,'viewpub'.$this->tid.'|'.$this->core_pid);
 		// clear all page of publist
		$pnr->clear(null,'publist'.$this->tid );
		unset($pnr);                           
                                   
        // somebody change this always back, pls let it be like this, otherwise stepmode does not work!
        // if the item moved to the depot
        if ($data[$args['commandName']]['core_indepot'] == 1) {
            $this->goto = pnModURL('pagemaster', 'user', 'main',
                                   array('tid' => $data['tid']));

        } elseif ($this->goto == 'stepmode') {
            // stepmode can be used to go automaticaly from one workflowstep to the next
            $this->goto = pnModURL('pagemaster', 'user', 'pubedit',
                                   array('tid'  => $data['tid'],
                                         'id'   => $data['id'],
                                         'goto' => 'stepmode'));

         } elseif (empty($this->goto)) {
            $this->goto = pnModURL('pagemaster', 'user', 'viewpub',
                                   array('tid' => $data['tid'],
                                         'pid' => $data['core_pid']));
        }
        if (empty($data)) {
            return false;
        } else {
            return $render->pnFormRedirect($this->goto);
        }
    }
}

/**
 * Executes a Workflow command over a direct URL Request
 *
 * @param $args['tid']
 * @param $args['id']
 * @param $args['goto'] redirect to after execution
 * @param $args['schema'] optional workflow shema
 * @param $args['commandName'] commandName
 * @author kundi
 */
function pagemaster_user_executecommand()
{
    $dom = ZLanguage::getModuleDomain('pagemaster');
    $tid         = FormUtil::getPassedValue('tid');
    $id          = FormUtil::getPassedValue('id');
    $commandName = FormUtil::getPassedValue('commandName');
    $schema      = FormUtil::getPassedValue('schema');
    $goto        = FormUtil::getPassedValue('goto');

    if (empty($tid) || !is_numeric($tid)) {
        return LogUtil::registerError(__f('Missing argument [%s]', 'tid', $dom));
    }

    if (!isset($id) || empty($id) || !is_numeric($id)) {
        return LogUtil::registerError(__f('Missing argument [%s]', 'id', $dom));
    }

    if (empty($commandName)) {
        return LogUtil::registerError(__f('Missing argument [%s]', 'commandName', $dom));
    }

    if (empty($schema)) {
        $pubtype = PMgetPubType($tid);
        $schema  = str_replace('.xml', '', $pubtype['workflow']);
    }

    $tablename = 'pagemaster_pubdata'.$tid;
    $pub = DBUtil::selectObjectByID($tablename, $id, 'id');
    if (!$pub) {
        return LogUtil::registerError(__('No publication found.', $dom));
    }

    PmWorkflowUtil::executeAction($schema, $pub, $commandName, $tablename, 'pagemaster');
    if (!empty($goto)) {
        if ($goto == 'edit') {
            return pnRedirect(pnModURL('pagemaster', 'user', 'pubedit',
                                       array('tid' => $tid,
                                             'id'  => $pub['id'])));
        } elseif ($goto == 'stepmode'){
            return pnRedirect(pnModURL('pagemaster', 'user', 'pubedit',
                                       array('tid'  => $tid,
                                             'id'   => $pub['id'],
                                             'goto' => 'stepmode')));
        } else {
            return pnRedirect($goto);
        }
    } else {
        return pnRedirect(pnModURL('pagemaster', 'user', 'viewpub',
                                   array('tid' => $tid,
                                         'id'  => $pub['id'])));
    }
}

/**
 * Edit/Create a publication
 *
 * @param $args['tid']
 * @param $args['id']
 * @author kundi
 */
function pagemaster_user_pubedit()
{
    $dom = ZLanguage::getModuleDomain('pagemaster');
    $tid = FormUtil::getPassedValue('tid');
    $id  = FormUtil::getPassedValue('id');
    $pid = FormUtil::getPassedValue('pid');

    if (empty($tid) || !is_numeric($tid)) {
        return LogUtil::registerError(__f('Missing argument [%s]', 'tid', $dom));
    }

    $pubtype = PMgetPubType($tid);
    if (empty($pubtype)) {
        return LogUtil::registerError(__f('No such tid found.', 'tid', $dom));
    }

    $pubfields = PMgetPubFields($tid, 'pm_lineno');
    if (empty($pubfields)) {
        LogUtil::registerError(__f('No such %i% found.', 'pubfields', $dom));
    }

    // No security check needed - the security check will be done by the handler class.
    // see the init-part of the handler class for details.
    $dynHandler = new pagemaster_user_dynHandler();

    if (empty($id) && !empty($pid)) {
        $id = pnModAPIFunc('pagemaster', 'user', 'getId',
                           array('tid' => $tid,
                                 'pid' => $pid));
        if (empty($id)) {
            return LogUtil::registerError(__f('No such %i% found.', 'pid', $dom));
        }
    }

    // cast values to ensure the type
    $id  = (int)$id;
    $pid = (int)$pid;

    $dynHandler->tid       = $tid;
    $dynHandler->id        = $id;
    $dynHandler->pubtype   = $pubtype;
    $dynHandler->pubfields = $pubfields;
    $dynHandler->tablename = 'pagemaster_pubdata'.$tid;

    // get actual state for selecting pnForm Template
    if (!empty($id)) {
        $obj = array('id' => $id);
        PmWorkflowUtil::getWorkflowForObject($obj, $dynHandler->tablename, 'id', 'pagemaster');
        $stepname = $obj['__WORKFLOW__']['state'];
    } else {
        $stepname = '';
    }

    $render = FormUtil::newpnForm('pagemaster');

    if (empty($stepname)) {
        $stepname = 'initial';
    }

    // resolve the template to use
    $user_defined_template_step = 'input/pubedit_'.$pubtype['formname'].'_'.$stepname.'.htm';

    if (!empty($stepname) && $render->get_template_path($user_defined_template_step)) {
        return $render->pnFormExecute($user_defined_template_step, $dynHandler);

    } else {
        $user_defined_template_all = 'input/pubedit_'.$pubtype['formname'].'_all.htm';

        if ($render->get_template_path($user_defined_template_all)) {
            return $render->pnFormExecute($user_defined_template_all, $dynHandler);

        } else {
            if (!empty($stepname)) {
                LogUtil::registerError(__f('Template [%s] not found', $user_defined_template_step, $dom));
            }
            LogUtil::registerError(__f('Template [%s] not found', $user_defined_template_all, $dom));
            $hookAction = empty($id) ? 'new' : 'modify';

            // TODO delete all the time, even if it's not needed
            $render->force_compile = true;
            $render->assign('editpub_template_code', PMgen_editpub_tplcode($tid, $pubfields, $pubtype, $hookAction));
            return $render->pnFormExecute('var:editpub_template_code', $dynHandler);
        }
    }
}

/**
 * List of publications
 *
 * @param $args['tid']
 * @author kundi
 */
function pagemaster_user_main($args)
{
    $dom = ZLanguage::getModuleDomain('pagemaster');
    // Get the input parameters
    $tid                = isset($args['tid']) ? $args['tid'] : FormUtil::getPassedValue('tid');
    $startnum           = isset($args['startnum']) ? $args['startnum'] : FormUtil::getPassedValue('startnum');
    $filter             = isset($args['filter']) ? $args['filter'] : FormUtil::getPassedValue('filter');
    $orderby            = isset($args['orderby']) ? $args['orderby'] : FormUtil::getPassedValue('orderby');
    $template           = isset($args['template']) ? $args['template'] : FormUtil::getPassedValue('template');
    $getApprovalState   = isset($args['getApprovalState']) ? $args['getApprovalState'] : FormUtil::getPassedValue('getApprovalState');
    $handlePluginFields = isset($args['handlePluginFields']) ? $args['handlePluginFields'] : FormUtil::getPassedValue('handlePluginFields');
    $rss                = isset($args['rss']) ? (bool)$args['rss'] : (bool)FormUtil::getPassedValue('rss');
    $cachelifetime      = isset($args['cachelifetime']) ? $args['cachelifetime'] : FormUtil::getPassedValue('cachelifetime');

    // Essential validation
    if (empty($tid) || !is_numeric($tid)) {
        return LogUtil::registerError(__f('Missing argument [%s]', 'tid', $dom));
    }

    $pubtype = PMgetPubType($tid);
    if (empty($pubtype)) {
        return LogUtil::registerError(__f('No such %s found.', 'tid', $dom));
    }

    if (empty($template)) {
        if (!empty($pubtype['filename'])) {
            // template comes from pubtype
            $sec_template = $pubtype['filename'];
            $template     = 'output/publist_'.$pubtype['filename'].'.htm';
        } else {
            // standart template
            $template     = 'generic_publist.htm';
            // do not check permission for dynamic template
            $sec_template = '';
        }
    } else {
        // template comes from parameter
        $sec_template = $template;
        $template     = 'output/publist_'.$template.'.htm';
    }

    // Security check as early as possible
    if (!SecurityUtil::checkPermission('pagemaster:list:', "$tid::$sec_template", ACCESS_READ)) {
        return LogUtil::registerError(_NOT_AUTHORIZED . ' pagemaster:list:  -  '."$tid::$sec_template");
    }

    // Check if this view is cached
    if (empty($cachelifetime)) {
        $cachelifetime = $pubtype['cachelifetime'];
    }

    if (!empty($cachelifetime)) {
        $cachetid = true;
        if (!empty($filter)) {
            $cacheid = 'publist'.$tid.'|'.$filter;
        } else {
            $cacheid = 'publist'.$tid.'|nofilter';
        }
    } else {
        $cachetid = false;
        $cacheid  = false;
    }

    if (empty($startnum)) {
        $cacheid .= '|nostartnum';
    } else {
        $cacheid .= '|'.$startnum;
    }

    $render = pnRender::getInstance('pagemaster', $cachetid, $cacheid, true);

    if ($cachetid) {
        $render->cache_lifetime = $cachelifetime;
        if ($render->is_cached($template, $cacheid)) {
            return $render->fetch($template, $cacheid);
        }
    }

    if (empty($getApprovalState)) {
        $getApprovalState = false;
    }
    if (empty($handlePluginFields)) {
        $handlePluginFields = true;
    }

    if (isset($args['itemsperpage'])) {
        $itemsperpage = $args['itemsperpage'];
    } elseif (FormUtil::getPassedValue('itemsperpage') != null) {
        $itemsperpage = (int)FormUtil::getPassedValue('itemsperpage');
    } else {
        $itemsperpage = ((int)$pubtype['itemsperpage'] > 0 ? (int)$pubtype['itemsperpage'] : -1 );
    }

    if ($itemsperpage != 0) {
        $countmode = 'both';
    } else {
        $countmode = 'no';
    }

    $orderby   = PMcreateOrderBy($orderby);

    $pubfields = PMgetPubFields($tid);

    // Uses the API to get the list of publications
    $result = pnModAPIFunc('pagemaster', 'user', 'pubList',
                           array('tid'                => $tid,
                                 'pubfields'          => $pubfields,
                                 'pubtype'            => $pubtype,
                                 'countmode'          => $countmode,
                                 'startnum'           => $startnum,
                                 'filter'             => $filter,
                                 'orderby'            => $orderby,
                                 'itemsperpage'       => $itemsperpage,
                                 'checkPerm'          => false, // already checked
                                 'handlePluginFields' => $handlePluginFields,
                                 'getApprovalState'   => $getApprovalState));

    // Assign the data to the output
    $render->assign('tid', $tid);
    $render->assign('publist', $result['publist']);
    $render->assign('core_titlefield', PMgetTitleField($pubfields));

    // Assign the pager values if needed
    if ($itemsperpage != 0) {
        $render->assign('pager', array('numitems'     => $result['pubcount'],
                                       'itemsperpage' => $itemsperpage));
    }

    // Check if template is available
    if ($template != 'generic_publist.htm' && !$render->get_template_path($template)) {
        LogUtil::registerStatus(__f('Template [%s] not found', $template, $dom));
        $template = 'generic_publist.htm';
    }

    if ($rss) {
        echo $render->display($template, $cacheid);
        pnShutDown();
    }

    return $render->fetch($template, $cacheid);
}

/**
 * View a publication
 * @author kundi
 *
 * @param $args['tid']
 * @param $args['pid']
 * @param $args['id'] (optional)
 * @param $args['template'] (optional)
 * @return publication view output
 */
function pagemaster_user_viewpub($args)
{
    $dom = ZLanguage::getModuleDomain('pagemaster');
    // Get the input parameters
    $tid      = isset($args['tid']) ? $args['tid'] : FormUtil::getPassedValue('tid');
    $pid      = isset($args['pid']) ? $args['pid'] : FormUtil::getPassedValue('pid');
    $id       = isset($args['id']) ? $args['id'] : FormUtil::getPassedValue('id');
    $template = isset($args['template']) ? $args['template'] : FormUtil::getPassedValue('template');
    $cachelt  = isset($args['cachelifetime']) ? $args['cachelifetime'] : FormUtil::getPassedValue('cachelifetime');

    // Essential validation
    if (empty($tid) || !is_numeric($tid)) {
        return LogUtil::registerError(__f('Missing argument [%s]', 'tid', $dom));
    }
    if ((empty($pid) || !is_numeric($pid)) && (empty($id) || !is_numeric($id))) {
        return LogUtil::registerError(__f('Missing argument [%s]', 'id | pid', $dom));
    }

    $pubtype = PMgetPubType($tid);
    if (empty($pubtype)) {
        return LogUtil::registerError(__f('No such %s found.', 'tid', $dom));
    }

    // Get the pid if it was not passed
    if (empty($pid)) {
        $pid = pnModAPIFunc('pagemaster', 'user', 'getPid',
                            array('tid' => $tid,
                                  'id'  => $id));
    }

    // Determine the template to use
    if (empty($template)) {
        if (!empty($pubtype['filename'])) {
            // template comes from pubtype
            $template     = 'output/viewpub_'.$pubtype['filename'].'.htm';
            // template for the security check
            $sec_template = $pubtype['filename'];
        } else {
            // standart template
            $template     = 'var:viewpub_template_code';
            // do not check permission for dynamic template
            $sec_template = '';
        }
    } else {
        // template comes from parameter
        $template     = 'output/viewpub_'.$template.'.htm';
        // template for the security check
        $sec_template = $template;
    }

    // Security check as early as possible
    if (!SecurityUtil::checkPermission('pagemaster:full:', "$tid:$pid:$sec_template", ACCESS_READ)) {
        return LogUtil::registerError(_NOT_AUTHORIZED . ' pagemaster:full: - ' . "$tid:$pid:$sec_template");
    }

    // Check if this view is cached
    if (empty($cachelt)) {
        $cachelt = $pubtype['cachelifetime'];
    }

    if (!empty($cachelt)) {
        $cachetid = true;
        $cacheid = 'viewpub'.$tid.'|'.$pid;
    } else {
        $cachetid = false;
        $cacheid  = false;
    }

    $render = pnRender::getInstance('pagemaster', $cachetid, $cacheid, true);

    if ($cachetid) {
        $render->cache_lifetime = $cachelt;
        if ($render->is_cached($template, $cacheid)) {
            return $render->fetch($template, $cacheid);
        }
    }

    // Not cached or cache disabled, then get the Pub from the DB
    $pubfields = PMgetPubFields($tid);

    $pubdata = pnModAPIFunc('pagemaster', 'user', 'getPub',
                            array('tid'                => $tid,
                                  'id'                 => $id,
                                  'pid'                => $pid,
                                  'pubtype'            => $pubtype,
                                  'pubfields'          => $pubfields,
                                  'checkPerm'          => false, //check later, together with template
                                  'getApprovalState'   => true,
                                  'handlePluginFields' => true));

    if (!$pubdata) {
        return LogUtil::registerError(__f('No such %s found.', 'Pub', $dom));
    }

    $core_title = PMgetTitleField($pubfields);

    // Assign each field of the pubdata to the output
    foreach ($pubdata as $key => $field) {
        $render->assign($key, $field);
    }

    // Process the output
    $render->assign('core_tid', $tid);
    $render->assign('core_approvalstate', $pubdata['__WORKFLOW__']['state']);
    $render->assign('core_titlefield', $core_title);
    $render->assign('core_title', $pubdata[$core_title]);
    $render->assign('core_uniqueid', $tid.'-'.$pubdata['core_pid']);
    $render->assign('core_creator', ($pubdata['core_author'] == pnUserGetVar('uid')) ? true : false);

    // Check if template is available
    if ($template != 'var:viewpub_template_code' && !$render->get_template_path($template)) {
        LogUtil::registerStatus(__f('Template [%s] not found', $template, $dom));
        $template = 'var:viewpub_template_code';
    }

    if ($template == 'var:viewpub_template_code') {
        $render->compile_check = true;
        $render->assign('viewpub_template_code', PMgen_viewpub_tplcode($tid, $pubdata, $pubtype, $pubfields));
    }

    return $render->fetch($template, $cacheid);
}
