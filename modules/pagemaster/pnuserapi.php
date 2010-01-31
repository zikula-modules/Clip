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
 * Returns a Publication List
 *
 * @author kundi
 * @param  int     $args['tid']
 * @param  int     $args['startnum']
 * @param  int     $args['itemsperpage']
 * @param  string  $args['countmode'] no, just, both
 * @param  bool    $args['checkperm']
 * @param  bool    $args['handlePluginFields']
 * @param  bool    $args['getApprovalState']
 * @return array   publication or count
 */
function pagemaster_userapi_pubList($args)
{
    $dom = ZLanguage::getModuleDomain('pagemaster');

    if (!isset($args['tid'])) {
        return LogUtil::registerError(__f('Error! Missing argument [%s].', 'tid', $dom));
    }

    $tid = $args['tid'];

    // validate the passed tid
    $pntables = pnDBGetTables();
    if (!isset($pntables['pagemaster_pubdata'.$tid])) {
        return LogUtil::registerError(__('Error! No such publication type found.', $dom));
    }
    unset($pntables);

    // parameters defaults
    $handlePluginFields = isset($args['handlePluginFields']) ? $args['handlePluginFields'] : false;
    $getApprovalState   = isset($args['getApprovalState']) ? $args['getApprovalState'] : false;
    $checkPerm          = isset($args['checkPerm']) ? $args['checkPerm'] : false;

    // permission check
    if ($checkPerm && !SecurityUtil::checkPermission('pagemaster:list:', "$tid::", ACCESS_READ)) {
        return LogUtil::registerPermissionError();
    }

    // optional arguments.
    if (!isset($args['startnum']) || !is_numeric($args['startnum'])) {
        $args['startnum'] = 1;
    }
    if (!isset($args['itemsperpage']) || !is_numeric($args['itemsperpage'])) {
        $args['itemsperpage'] = -1;
    }
    if (!isset($args['justcount']) || !is_numeric($args['justcount'])) {
        $args['justcount'] = 'no';
    }

    $pubtype   = isset($args['pubtype']) ? $args['pubtype'] : PMgetPubType($tid);
    $pubfields = isset($args['pubfields']) ? $args['pubfields'] : PMgetPubFields($tid);

    // mode check
    $isadmin = !SecurityUtil::checkPermission('pagemaster:full:', "$tid::", ACCESS_ADMIN) || (!isset($args['admin']) || !$args['admin']);
    // TODO pubtype.editown + author mode parameter check

    // set the order
    if (!isset($args['orderby']) || empty($args['orderby'])) {
        if (!empty($pubtype['sortfield1'])) {
            if ($pubtype['sortdesc1'] == 1) {
                $orderby = $pubtype['sortfield1'].' DESC ';
            } else {
                $orderby = $pubtype['sortfield1'].' ASC ';
            }

            if (!empty($pubtype['sortfield2'])) {
                if ($pubtype['sortdesc2'] == 1) {
                    $orderby .= ', '.$pubtype['sortfield2'].' DESC ';
                } else {
                    $orderby .= ', '.$pubtype['sortfield2'].' ASC ';
                }
            }

            if (!empty($pubtype['sortfield3'])) {
                if ($pubtype['sortdesc3'] == 1) {
                    $orderby .= ', '.$pubtype['sortfield3'].' DESC ';
                } else {
                    $orderby .= ', '.$pubtype['sortfield3'].' ASC ';
                }
            }
        }
    } else {
        $orderby = $args['orderby'];
    }

    if (version_compare(PN_VERSION_NUM, '1.3', '>=')) {
        Loader::LoadClass('FilterUtil');
    } else {
        Loader::LoadClass('FilterUtil', 'modules/pagemaster/classes');
    }

    foreach ($pubfields as $fieldname => $field)
    {
        $pluginclass = $field['fieldplugin'];
        $plugin = PMgetPlugin($pluginclass);

        if (isset($plugin->filterClass)) {
            $filterPlugins[$plugin->filterClass]['fields'][] = $fieldname;
        }
        // check for tables to join
        if ($args['countmode'] <> 'just'){
            // do not join for just
           if ($field['fieldplugin'] == 'pmformpubinput'){
                $vars        = explode(';', $field['typedata']);
                $join_tid    = $vars[0];
                $join_filter = $vars[1]; // TODO Use?
                $join        = $vars[2];
                $join_fields = $vars[3];
                $join_arr    = explode(',', $join_fields);
                if ($join == 'on') {
                    foreach ($join_arr as $value) {
                        list($x, $y) = explode(':', $value);
                        $join_field_arr[]        = $x;
                        $object_field_name_arr[] = $y;
                    }
                    $joinInfo[] = array('join_table'         =>  'pagemaster_pubdata'.$join_tid,
                                        'join_field'         =>  $join_field_arr,
                                        'object_field_name'  =>  $object_field_name_arr,
                                        'compare_field_table'=>  $fieldname,
                                        'compare_field_join' =>  'core_pid');
                }
            }
        }
    }

    if (isset($joinInfo)) {
        $tbl_alias = 'tbl.';
	    $filter_args = array('join' => array('join_table' => $joinInfo['join_table']),
	                                         'plugins'    => $filterPlugins);
    } else {
        $tbl_alias = '';
	    $filter_args = array('plugins' => $filterPlugins);
    }

    // check if some plugin specific orderby has to be done
    $orderby   = PMhandlePluginOrderBy($orderby, $pubfields, $tbl_alias);
    $tablename = 'pagemaster_pubdata'.$tid;
    $fu = new FilterUtil('pagemaster', $tablename, $filter_args);

    if (isset($args['filter']) && !empty($args['filter'])) {
        $fu->setFilter($args['filter']);
    } elseif (!empty($pubtype['defaultfilter'])) {
        $fu->setFilter($pubtype['defaultfilter']);
    }

    $filter_where = $fu->GetSQL();

    // build the where clause
    $where = array();
    $uid   = pnUserGetVar('uid');

    if ($isadmin) {
        if (!empty($uid) && $pubtype['enableeditown'] == 1) {
            $where[] = "( {$tbl_alias}pm_online = '1' AND ( {$tbl_alias}pm_author = '$uid' OR {$tbl_alias}pm_showinlist = '1') )";
        } else {
            $where[] = "  {$tbl_alias}pm_online = '1' AND {$tbl_alias}pm_showinlist = '1'";
        }

        $where[] = "  {$tbl_alias}pm_indepot = '0' ";
        $where[] = "( {$tbl_alias}pm_language = '' OR {$tbl_alias}pm_language = '".ZLanguage::getLanguageCode()."' )";
        $where[] = "( {$tbl_alias}pm_publishdate <= NOW() OR {$tbl_alias}pm_publishdate IS NULL )";
        $where[] = "( {$tbl_alias}pm_expiredate >= NOW() OR {$tbl_alias}pm_expiredate IS NULL )";
    }
    // TODO Implement author condition

    if (!empty($filter_where['where'])) {
        $where[] = $filter_where['where'];
    }

    $where = implode(' AND ', $where);

    if ($args['countmode'] <> 'just') {
        if (isset($joinInfo)) {
            $publist = DBUtil::selectExpandedObjectArray($tablename, $joinInfo, $where, $orderby, $args['startnum']-1, $args['itemsperpage']);
        } else {
            $publist = DBUtil::selectObjectArray($tablename, $where, $orderby, $args['startnum']-1, $args['itemsperpage']);
        }
        if ($getApprovalState) {
            foreach (array_keys($publist) as $key) {
                WorkflowUtil::getWorkflowForObject($publist[$key], $tablename, 'id', 'pagemaster');
            }
        }
        if ($handlePluginFields) {
            $publist = PMhandlePluginFields($publist, $pubfields);
        }
    }

    if ($args['countmode'] == 'just' || $args['countmode'] == 'both') {
        $pubcount = DBUtil::selectObjectCount($tablename, str_replace(' tbl.', ' ', $where));
    }

    return array (
        'publist'  => $publist,
        'pubcount' => $pubcount
    );
}

/**
 * Returns a Publication
 *
 * @author kundi
 * @param  int     $args['tid']
 * @param  int     $args['pid']
 * @param  int     $args['id']
 * @param  bool    $args['checkPerm']
 * @param  bool    $args['getApprovalState']
 * @param  bool    $args['handlePluginFields']
 * @return array   publication
 */
function pagemaster_userapi_getPub($args)
{
    $dom = ZLanguage::getModuleDomain('pagemaster');

    // validation of essential parameters
    if (!isset($args['tid'])) {
        return LogUtil::registerError(__f('Error! Missing argument [%s].', 'tid', $dom));
    }
    if (!isset($args['id']) && !isset($args['pid'])) {
        return LogUtil::registerError(__f('Error! Missing argument [%s].', 'id | pid', $dom));
    }

    // defaults
    $pubtype            = isset($args['pubtype']) ? $args['pubtype'] : null;
    $pubfields          = isset($args['pubfields']) ? $args['pubfields'] : null;
    $getApprovalState   = isset($args['getApprovalState']) ? $args['getApprovalState'] : false;
    $checkPerm          = isset($args['checkPerm']) ? $args['checkPerm'] : false;
    $handlePluginFields = isset($args['handlePluginFields']) ? $args['handlePluginFields'] : false;

    $tid = (int)$args['tid'];
    $pid = isset($args['pid']) ? (int)$args['pid'] : null;
    $id  = isset($args['id']) ? (int)$args['id'] : null;
    unset($args);

    // get the pubtype if not set
    if (empty($pubtype)) {
        $pubtype = PMgetPubType($tid);
        // validate the result
        if (!$pubtype) {
            return LogUtil::registerError(__('Error! No such publication type found.', $dom));
        }
    }

    // get the pubfields if not set
    if (empty($pubfields)) {
        $pubfields = PMgetPubFields($tid);
        // validate the result
        if (!$pubfields) {
            return LogUtil::registerError(__('Error! No publication fields found.', $dom));
        }
    }

    // build the where clause
    $tablename = 'pagemaster_pubdata'.$tid;
    $uid       = pnUserGetVar('uid');
    $where     = '';

    if (!SecurityUtil::checkPermission('pagemaster:full:', "$tid::", ACCESS_ADMIN))
    {
        if (!empty($uid) && $pubtype['enableeditown'] == 1) {
            $where .= " (pm_author = '$uid' OR pm_online = '1' )";
        } else {
            $where .= " pm_online = '1' ";
        }
        $where .= " AND pm_indepot = '0'
                    AND (pm_language = '' OR pm_language = '".ZLanguage::getLanguageCode()."')
                    AND (pm_publishdate <= NOW() OR pm_publishdate IS NULL)
                    AND (pm_expiredate >= NOW() OR pm_expiredate IS NULL)";

        if (empty($args['id'])) {
            $where .= " AND pm_pid = '$pid'";
        } else {
            $where .= " AND pm_id = '$id'";
        }
    } else {
        if (empty($id)) {
            $tablem = DBUtil::getLimitedTablename($tablename);
            $where .= " pm_pid = '$pid' AND pm_online = '1'";
        } else {
            $where .= " pm_id = '$id'";
        }
    }

    $pubdata = DBUtil::selectObject($tablename, $where);

    if (!$pubdata) {
        return false;
    }

    if ($checkPerm && !SecurityUtil::checkPermission('pagemaster:full:', "$tid:$pubdata[core_pid]:", ACCESS_READ)) {
        return LogUtil::registerPermissionError();
    }

    // handle the plugins data if needed
    if ($handlePluginFields){
        $pubdata = PMhandlePluginFields($pubdata, $pubfields, false);
    }

    if ($getApprovalState) {
        WorkflowUtil::getWorkflowForObject($pubdata, $tablename, 'id', 'pagemaster');
    }

    // fills the core_title field
    $core_title            = PMgetTitleField($pubfields);
    $pubdata['core_title'] = $pubdata[$core_title];

    return $pubdata;
}

/**
 * Edit or creates a new publication
 *
 * @author kundi
 * @param $args['data'] array of pubfields data
 * @param $args['commandName'] commandName has to be a valid workflow action for the currenct state
 * @param $args['pubfields'] array of pubfields (optional, performance)
 * @param $args['schema'] schema name (optional, performance)
 * @return true or false
 */
function pagemaster_userapi_editPub($args)
{
    $dom = ZLanguage::getModuleDomain('pagemaster');

    if (!isset($args['data'])) {
        return LogUtil::registerError(__f('Error! Missing argument [%s].', 'data', $dom));
    }
    if (!isset($args['commandName'])) {
        return LogUtil::registerError(__f('Error! Missing argument [%s].', 'commandName', $dom).' '.__('commandName has to be a valid workflow action for the currenct state.', $dom));
    }

    $commandName = $args['commandName'];
    $data        = $args['data'];
    $tid         = $data['tid'];

    $pubfields = isset($args['pubfields']) ? $args['pubfields'] : PMgetPubFields($tid);

    if (!isset($args['schema'])) {
        $pubtype = PMgetPubType($tid);
        $schema  = str_replace('.xml', '', $pubtype['workflow']);
    } else {
        $schema = $args['schema'];
    }

    foreach ($pubfields as $fieldname => $field)
    {
        $plugin = PMgetPlugin($field['fieldplugin']);
        if (method_exists($plugin, 'preSave')) {
            $data[$fieldname] = $plugin->preSave($data, $field);
        }
    }

    $ret = WorkflowUtil::executeAction($schema, $data, $commandName, 'pagemaster_pubdata'.$data['tid'], 'pagemaster');
    if (empty($ret)) {
        return LogUtil::registerError(__('Workflow action error.', $dom));
    }

    $data = array_merge($data, $ret);

    return $data;
}

/**
 * Returns pid
 *
 * @author kundi
 * @param int $args['tid']
 * @param int $args['id']
 * @return int pid
 */
function pagemaster_userapi_getPid($args)
{
    $dom = ZLanguage::getModuleDomain('pagemaster');

    if (!isset($args['tid'])) {
        return LogUtil::registerError(__f('Error! Missing argument [%s].', 'tid', $dom));
    }
    if (!isset($args['id'])) {
        return LogUtil::registerError(__f('Error! Missing argument [%s].', 'id', $dom));
    }

    $tablename = 'pagemaster_pubdata'.$args['tid'];
    $pub       = DBUtil::selectObjectByID($tablename, $args['id'], 'id');

    return $pub['id'];
}

/**
 * Returns the ID of the online publication
 *
 * @author kundi
 * @param int $args['tid']
 * @param int $args['pid']
 * @return int id
 */
function pagemaster_userapi_getId($args)
{
    $dom = ZLanguage::getModuleDomain('pagemaster');

    if (!isset($args['tid']) || !is_numeric($args['tid'])) {
        return LogUtil::registerError(__f('Error! Missing argument [%s].', 'tid', $dom));
    }
    if (!isset($args['pid']) || !is_numeric($args['pid'])) {
        return LogUtil::registerError(__f('Error! Missing argument [%s].', 'pid', $dom));
    }

    // build the query
    $tablename = 'pagemaster_pubdata'.$args['tid'];
    $where     = "pm_pid = '$args[pid]' AND pm_online = '1'";

    return DBUtil::selectField($tablename, 'id', $where);
}

/**
 * form custom url string
 *
 * @author Philipp Niethammer <webmaster@nochwer.de>
 * @param  array   $args Arguments given by pnModUrl
 * @return custom  url string
 */
function pagemaster_userapi_encodeurl($args)
{
    if (!isset($args['modname']) || !isset($args['func']) || !isset($args['args'])) {
        return LogUtil::registerArgsError();
    }

    $supportedfunctions = array('main', 'viewpub');
    if (!in_array($args['func'], $supportedfunctions)) {
        return '';
    }

    if (!isset($args['args']['tid'])) {
        return false;
    } else {
        $tid = (int)$args['args']['tid'];
        $pubtype      = PMgetPubType($tid);
        $pubtypeTitle = DataUtil::formatPermalink($pubtype['urltitle']);

        unset($args['args']['tid']);
        unset($pubtype);
    }

   if (isset($args['args']['pid']) || isset($args['args']['id'])) {
        $prefix = pnConfigGetVar('prefix');

        if (isset($args['args']['pid'])) {
            $pid = (int)$args['args']['pid'];
            unset($args['args']['pid']);
        } elseif (isset($args['args']['id'])) {
            $id = (int)$args['args']['id'];
            unset($args['args']['id']);
            $result = DBUtil::executeSQL("SELECT pm_pid FROM {$prefix}_pagemaster_pubdata{$tid} WHERE pm_id = '{$id}'");
            $pid = $result->fields[0];
        } else {
            return false;
        }

        //$result = DBUtil::executeSQL("SELECT pm_id FROM {$prefix}_pagemaster_pubfields WHERE pm_tid = '{$tid}' AND pm_istitle = '1'");
        //$titlefieldid = $result->fields[0];
        $titlefield = PMgetTitleField(PMgetPubFields($tid));

        $pubTitle = DBUtil::selectFieldByID("pagemaster_pubdata{$tid}", $titlefield['name'], $pid, 'core_pid');

        $pubTitle = '/'.DataUtil::formatPermalink($pubTitle).'.'.$pid;
    }

    $params = '';
    if (count($args['args']) > 0) {
        $paramarray = array();
        foreach ($args['args'] as $k => $v) {
            $paramarray[] = $k.'/'.urlencode($v);
        }
        $params = '/'. implode('/', $paramarray);
    }

    return $args['modname'].'/'.$pubtypeTitle.$pubTitle.$params;
}

/**
 * decode custom url string
 *
 * @author Philipp Niethammer
 * @return bool true if succeded false otherwise
 */
function pagemaster_userapi_decodeurl($args)
{
    $_ = $args['vars'];

    $functions = array('executecommand', 'pubedit', 'main', 'viewpub');
    $argsnum   = count($_);
    if (!isset($_[2]) || empty($_[2])) {
        pnQueryStringSetVar('func', 'main');
        return true;
    }

    if (in_array($_[2], $functions)) {
        return false;
    }

    $nextvar = 3;

    $tid = DBUtil::selectFieldByID('pagemaster_pubtypes', 'tid', $_[2], 'urltitle');
    if (!$tid) {
        return false;
    } else {
        pnQueryStringSetVar('func', 'main');
        pnQueryStringSetVar('tid', $tid);
    }

    if (isset($_[3]) && !empty($_[3])) {
        $permalinksseparator = pnConfigGetVar('shorturlsseparator');
        $match = '';
        $isPub = (bool) preg_match('~^[a-z0-9_'.$permalinksseparator.']+\.(\d+)+$~i', $_[3], $match);
        if ($isPub) {
            $pid = $match[1];
            pnQueryStringSetVar('func', 'viewpub');
            pnQueryStringSetVar('pid', $pid);
            $nextvar = 4;
        }
    }

    if (isset($_[$nextvar]) && !empty($_[$nextvar])) {
        for ($i = $nextvar; $i < $argsnum; $i+=2) {
            pnQueryStringSetVar($_[$i], $_[$i+1]);
        }
    }

    return true;
}
