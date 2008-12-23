<?php
/**
 * PageMaster
 *
 * @copyright (c) 2008, PageMaster Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package     Zikula_3rd_party_Modules
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
        $this->goto = FormUtil::getPassedValue('goto', '');
        if ($this->id <> '') {
            $pubdata = DBUtil::selectObjectByID($this->tablename, $this->id, 'id');
            $this->core_author = $pubdata['core_author'];
            $this->core_pid = $pubdata['core_pid'];
            $this->core_revision = $pubdata['core_revision'];
            $actions = WorkflowUtil::getActionsForObject($pubdata, $this->tablename, 'id', 'pagemaster');
        } else {
            $actions = WorkflowUtil::getActionsByState(str_replace('.xml', '', $this->pubtype['workflow']), 'pagemaster');
        }

        if ($pubtype['tid'] > 0) {
            $tid = $pubtype['tid']; 
        } else {
            $tid = FormUtil::getPassedValue('tid'); 
            // if there are no actions the user is not allowed to change / submit / delete something.
            // We will redirect the user to the overview page
            if (count($actions) < 1) {
                LogUtil::registerError(_PAGEMASTER_WORKFLOW_NOACTIONSFOUND);
               // return $render->pnFormRedirect(pnModURL('pagemaster', 'user', 'main', array('tid' => $tid))); 
            }
        }

        // check for set_ default values
        $fieldnames = array_keys($this->pubfields);
        foreach ($fieldnames as $fieldname)
        {
            $val = FormUtil::getPassedValue('set_'.$fieldname, '');
            if ($val <> '') {
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
        $data['core_author']        = $this->core_author;
        $data['core_pid']      = $this->core_pid;
        $data['core_revision'] = $this->core_revision;

        $data = pnModAPIFunc('pagemaster', 'user', 'editPub',
                             array('data'        => $data,
                                   'commandName' => $args['commandName'],
                                   'pubfields'   => $this->pubfields,
                                   'schema'      => str_replace('.xml', '', $this->pubtype['workflow'])));

        if ($this->goto == '') {
            $this->goto = pnModURL('pagemaster', 'user', 'viewpub',
                                   array('tid' => $data['tid'],
                                         'pid' => $data['core_pid']));

        } elseif ($this->goto == 'stepmode') {
            // stepmode can be used to go automaticaly from one workflowstep to the next
            $this->goto = pnModURL('pagemaster', 'user', 'pubedit',
                                   array('tid'  => $data['tid'],
                                         'id'   => $data['id'],
                                         'goto' => 'stepmode'));
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
    $tid         = FormUtil::getPassedValue('tid');
    $id          = FormUtil::getPassedValue('id');
    $commandName = FormUtil::getPassedValue('commandName');
    $schema      = FormUtil::getPassedValue('schema');
    $goto        = FormUtil::getPassedValue('goto');

    if ($tid == '') {
        return LogUtil::registerError(pnML('_PAGEMASTER_MISSINGARG', array('arg' => 'tid')));
    }

    if (!isset($id) || empty($id)) {
        return LogUtil::registerError(pnML('_PAGEMASTER_MISSINGARG', array('arg' => 'id')));
    }
    if ($commandName == '') {
        return LogUtil::registerError(pnML('_PAGEMASTER_MISSINGARG', array('arg' => 'commandName')));
    }

    if ($schema == '') {
        $pubtype = DBUtil::selectObjectByID('pagemaster_pubtypes', $tid, 'tid');
        $schema  = str_replace('.xml', '', $pubtype['workflow']);
    }

    $tablename = 'pagemaster_pubdata'.$tid;
    $pub = DBUtil::selectObjectByID($tablename, $id, 'id');
    if (!$pub) {
        return LogUtil::registerError(pnML('_NOFOUND', array('i' => _PAGEMASTER_PUBLICATION)));
    }

    WorkflowUtil::executeAction($schema, $pub, $commandName, 'pagemaster_pubdata'.$tid, 'pagemaster');
    if ($goto <> ''){
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
    $tid = FormUtil::getPassedValue('tid');
    $id  = FormUtil::getPassedValue('id');
    $pid = FormUtil::getPassedValue('pid');

    if (empty($tid)) {
        return LogUtil::registerError(pnML('_PAGEMASTER_MISSINGARG', array('arg' => 'tid')));
    }

    $pubtype   = DBUtil::selectObjectByID('pagemaster_pubtypes', $tid, 'tid');
    $pubfields = DBUtil::selectObjectArray('pagemaster_pubfields', 'pm_tid = '.$tid, 'pm_lineno', -1, -1, 'name');

    // No security check needed - the security check will be done by the handler class. 
    // see the init-part of the handler class for details. 
    $dynHandler = new pagemaster_user_dynHandler();

    if ($id == '' && $pid <>'') {
        $id = pnModAPIFunc('pagemaster', 'user', 'getId',
                           array('tid' => $tid,
                                 'pid' => $pid));
        if ($id == '') {
            return LogUtil::registerError("pid $pid not found");
        }
    }
    $dynHandler->tid       = $tid;
    $dynHandler->id        = $id;
    $dynHandler->pubtype   = $pubtype;
    $dynHandler->pubfields = $pubfields;
    $dynHandler->tablename = 'pagemaster_pubdata'.$tid;

    // get actual state for selecting pnForm Template
    if ('id' <> '') {
        $obj = array('id' => $id);
        WorkflowUtil::getWorkflowForObject($obj, $dynHandler->tablename, 'id', 'pagemaster');
        $stepname = $obj['__WORKFLOW__']['state'];
    }

    $render = FormUtil::newpnForm('pagemaster');

    // resolve the template to use
    $user_defined_template_step = 'input/pubedit_'.$pubtype['formname'].'_'.$stepname.'.htm';

    if ($render->get_template_path($user_defined_template_step)) {
        return $render->pnFormExecute($user_defined_template_step, $dynHandler);

    } else {
        $user_defined_template_all = 'input/pubedit_'.$pubtype['formname'].'_all.htm';

        if ($render->get_template_path($user_defined_template_all)) {
            return $render->pnFormExecute($user_defined_template_all, $dynHandler);

        } else {
            LogUtil::registerStatus(pnML('_PAGEMASTER_TEMPLATENOTFOUND', array('tpl' => $user_defined_template_step)));
            LogUtil::registerStatus(pnML('_PAGEMASTER_TEMPLATENOTFOUND', array('tpl' => $user_defined_template_all)));
            global $editpub_template_code;
            $editpub_template_code = generate_editpub_template_code($tid, $pubfields, $pubtype);
            // TODO delete all the time, even if it's not needed
            $render->force_compile = true;
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
    $tid                = isset($args['tid']) ? $args['tid'] : FormUtil::getPassedValue('tid');
    $startnum           = isset($args['startnum']) ? $args['startnum'] : FormUtil::getPassedValue('startnum');
    $filter             = isset($args['filter']) ? $args['filter'] : FormUtil::getPassedValue('filter');
    $orderby            = isset($args['orderby']) ? $args['orderby'] : FormUtil::getPassedValue('orderby');
    $justOwn            = isset($args['justOwn']) ? $args['justOwn'] : FormUtil::getPassedValue('justOwn');
    $template           = isset($args['template']) ? $args['template'] : '';
    $getApprovalState   = isset($args['getApprovalState']) ? $args['getApprovalState'] : FormUtil::getPassedValue('getApprovalState');
    $handlePluginFields = isset($args['handlePluginFields']) ? $args['handlePluginFields'] : FormUtil::getPassedValue('handlePluginFields');
    $rss                = isset($args['rss']) ? $args['rss'] : FormUtil :: getPassedValue('rss');
    $cachelifetime      = isset($args['cachelifetime']) ? $args['cachelifetime'] : FormUtil::getPassedValue('cachelifetime');

    if ($justOwn == '') {
        $justOwn = false;
    }
    if ($getApprovalState == '') {
        $getApprovalState = false;
    }
    if ($handlePluginFields == '') {
        $handlePluginFields = true;
    }

    if (empty($tid)) {
        return LogUtil::registerError(pnML('_PAGEMASTER_MISSINGARG', array('arg' => 'tid')));
    } elseif(!is_numeric($tid)) {
        return LogUtil::registerError(pnML('_MUSTBENUMERIC', array('s' => 'tid')));
    }

    $pubtype = DBUtil::selectObjectByID('pagemaster_pubtypes', $tid, 'tid');
    if (empty($pubtype)) {
        return LogUtil::registerError(pnML('_NOSUCHITEMFOUND', array('i' => 'tid')));
    }

    if (isset($args['itemsperpage'])) {
        $itemsperpage = $args['itemsperpage'];
    } elseif (FormUtil::getPassedValue('itemsperpage') <> '') {
        $itemsperpage = FormUtil::getPassedValue('itemsperpage');
    } else {
        $itemsperpage = ((int)$pubtype['itemsperpage'] > 0 ? (int)$pubtype['itemsperpage'] : -1 );
    }

    if ($cachelifetime == '') {
        $cachelifetime = $pubtype['cachelifetime'];
    }

    if ($cachelifetime <> '') {
        $cachetid = true;
        if ($filter <> '') {
            $cacheid = 'publist'.$tid.'|'.$filter;
        } else {
            $cacheid = 'publist'.$tid.'|nofilter';
        }
    } else {
        $cachetid = false;
        $cacheid = false;
    }

    if ($template == '') {
        if ($pubtype['filename'] <> '') {
            //template comes from pubtype
            $sec_template = $pubtype['filename'];
            $template     = 'output/publist_'.$pubtype['filename'].'.htm';
        } else {
            //standart template
            $template = 'publist_template.htm';
            //do not check permission for dynamic template
            $sec_template = '';
        }
    } else {
        //template comes from parameter
        $sec_template = $template;
        $template     = 'output/publist_'.$template.'.htm';
    }

    if (!SecurityUtil::checkPermission('pagemaster:list:', "$tid::$sec_template", ACCESS_READ)) {
        return LogUtil::registerError(_NOT_AUTHORIZED . ' pagemaster:list:  -  ' . "$tid::$sec_template");
    }

    if ($startnum == '') {
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

    $orderby   = createOrderBy($orderby);
    $pubfields = DBUtil::selectObjectArray('pagemaster_pubfields', 'pm_tid = '.$tid, '', -1, -1, 'name');

    if ($itemsperpage <> 0) {
        $countmode = 'both';
    } else {
        $countmode = 'no';
    }
            
    $pubarr = pnModAPIFunc('pagemaster', 'user', 'pubList',
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
                                 'getApprovalState'   => $getApprovalState,
                                 'justOwn'            => $justOwn));
    
    $publist  = $pubarr['publist'];
    $pubcount = $pubarr['pubcount'];

    $core_title = getTitleField($pubfields);

    if ($itemsperpage <> 0) {
        $render->assign('pager', array('numitems'     => $pubcount,
                                       'itemsperpage' => $itemsperpage));
    }
    $render->assign('publist', $publist);
    $render->assign('core_titlefield', $core_title);
    $render->assign('tid', $tid);

    // check if template is available
    if ($template <> 'publist_template.htm' && !$render->get_template_path($template)) {
        LogUtil::registerStatus(pnML('_PAGEMASTER_TEMPLATENOTFOUND', array('tpl' => $template)));
        $template = 'publist_template.htm';
    }

    if ($rss == true) {
        echo $render->display($template, $cacheid);
        pnShutDown();
    }
    return $render->fetch($template, $cacheid);
}

/**
 * View a publication
 *
 * @param $args['tid']
 * @param $args['pid']
 * @param $args['id'] (optional)
 * @param $args['template'] (optional)
 * @author kundi
 */
function pagemaster_user_viewpub($args)
{
    $tid      = isset($args['tid']) ? $args['tid'] : FormUtil::getPassedValue('tid');
    $pid      = isset($args['pid']) ? $args['pid'] : FormUtil::getPassedValue('pid');
    $id       = isset($args['id']) ? $args['id'] : FormUtil::getPassedValue('id');
    $template = isset($args['template']) ? $args['template'] : '';//FormUtil::getPassedValue('template');
    $cachelifetime = isset($args['cachelifetime']) ? $args['cachelifetime'] : FormUtil::getPassedValue('cachelifetime');

    if ($tid == '') {
        return LogUtil::registerError(pnML('_PAGEMASTER_MISSINGARG', array('arg' => 'tid')));
    }
    if ($pid == '' && $id == '') {
        return LogUtil::registerError(pnML('_PAGEMASTER_MISSINGARG', array('arg' => 'id | pid')));
    }

    $pubtype   = DBUtil::selectObjectByID('pagemaster_pubtypes', $tid, 'tid');
    $pubfields = DBUtil::selectObjectArray('pagemaster_pubfields', 'pm_tid = '.$tid, '', -1, -1, 'name');

    if ($pid == '') {
        $pid = pnModAPIFunc('pagemaster', 'user', 'getPid',
                            array('tid' => $tid,
                                  'id' => $id));
    }

    if ($template == '') {
        if ($pubtype['filename'] <> '') {
            // template comes from pubtype
            $sec_template = $pubtype['filename'];
            $template     = 'output/viewpub_'.$pubtype['filename'].'.htm';
        } else {
            // standart template
            $template = 'var:viewpub_template_code';
            // do not check permission for dynamic template
            $sec_template = '';
        }
    } else {
        // template comes from parameter
        $sec_template = $template;
        $template     = 'output/viewpub_' . $template . '.htm';
    }

    if (!SecurityUtil::checkPermission('pagemaster:full:', "$tid:$pid:$sec_template", ACCESS_READ)) {
        return LogUtil::registerError(_NOT_AUTHORIZED . ' pagemaster:full: - ' . "$tid:$pid:$sec_template");
    }

    if ($cachelifetime == '') {
        $cachelifetime = $pubtype['cachelifetime'];
    }

    if ($cachelifetime <> '') {
        $cachetid = true;
        $cacheid = 'viewpub'.$tid.'|'.$pid;
    } else {
        $cachetid = false;
        $cacheid = false;
    }

    $render  = pnRender::getInstance('pagemaster', $cachetid, $cacheid, true);
    $render->cache_lifetime = $cachelifetime;

    if ($cacheid) {
        if ( $render->is_cached($template, $cacheid)) {
            return $render->fetch($template, $cacheid);
        }
    }
    $pubdata = pnModAPIFunc('pagemaster', 'user', 'getPub',
                            array('tid'                => $tid,
                                  'id'                 => $id,
                                  'pid'                => $pid,
                                  'checkPerm'          => false, //check later, together with template
                                  'getApprovalState'   => true,
                                  'handlePluginFields' => true));

    if (!$pubdata) {
        return false;
    }

    $core_title = getTitleField($pubfields);

    foreach ($pubdata as $key => $field) {
        $render->assign($key, $field);
    }
    $render->assign('core_tid', $tid);
    $render->assign('core_approvalstate', $pubdata['__WORKFLOW__']['state']);
    if ($pubdata['core_author'] == pnUserGetVar('uid')) {
        $render->assign('core_creator', true);
    } else {
        $render->assign('core_creator', false);
    }
    $render->assign('core_title', $pubdata[$core_title]);
    $render->assign('core_uniqueid', $tid.'_'.$pubdata['core_pid']);
    $render->assign('core_titlefield', $core_title);

    // check if template is available
    if ($template <> 'var:viewpub_template_code' && !$render->get_template_path($template)) {
        LogUtil::registerStatus(pnML('_PAGEMASTER_TEMPLATENOTFOUND', array('tpl' => $template)));
        $template = 'var:viewpub_template_code';
    }

    if ($template == 'var:viewpub_template_code') {
        global $viewpub_template_code;
        $viewpub_template_code = generate_viewpub_template_code($tid, $pubdata, $pubtype, $pubfields);
        //TODO: only recompile if changed
        $render->force_compile = true;
    }
    return $render->fetch($template, $cacheid);
}
