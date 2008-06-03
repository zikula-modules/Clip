<?php
include_once ('modules/pagemaster/common.php');

/**
 * Edit or creates a new publication
 * @author kundi
 * @param $args['data'] array of pubfields data
 * @param $args['commandName'] commandName has to be a valid workflow action for the currenct state
 * @param $args['pubfields'] array of pubfields (optional, performance)
 * @param $args['schema'] schema name (optional, performance)
 * @return true or false
 */
function pagemaster_userapi_editPub($args) {

	if (!isset ($args['data']))
	return LogUtil :: registerError("Missing argument 'data'");
	if (!isset ($args['commandName']))
	return LogUtil :: registerError("Missing argument 'commandName'. commandName has to be a valid workflow action for the currenct state.");

	$commandName = $args['commandName'];
	$data = $args['data'];
	$tid = $data['tid'];

	if (!isset ($args['pubfields']))
	$pubfields = DBUtil :: selectObjectArray("pagemaster_pubfields", "pm_tid = $tid");
	else
	$pubfields = $args['pubfields'];
	if (!isset ($args['schema'])) {
		$pubtype = DBUtil :: selectObjectByID("pagemaster_pubtypes", $tid, 'tid');
		$schema = str_replace('.xml', '', $pubtype['workflow']);
	} else
	$schema = $args['schema'];
	foreach ($pubfields as $field) {
		$plugin = pagemasterGetPlugin($field['fieldplugin']);
		if (method_exists($plugin, 'preSave'))
		$data[$field['name']] = $plugin->preSave($data[$field['name']], $field);
	}

	$ret = WorkflowUtil :: executeAction($schema, $data, $commandName, "pagemaster_pubdata" . $data['tid'], 'pagemaster');
	if (!$ret)
	return LogUtil :: registerError("Workflow action error");

	return $data;
}


/**
 * Returns pid
 * @author kundi
 * @param int $args['tid']
 * @param int $args['id']
 * @return int pid
 */
function pagemaster_userapi_getPid($args) {

	if (!isset ($args['tid']))
	return LogUtil :: registerError("Missing argument 'tid'");
	if (!isset ($args['id']))
	return LogUtil :: registerError("Missing argument 'id'");

	$tablename = "pagemaster_pubdata" . $args['tid'];
	$pub = DBUtil :: selectObjectByID($tablename, $args['id'], 'id');
	return $pub['id'];

}
/**
 * Returns id
 * @author kundi
 * @param int $args['tid']
 * @param int $args['pid']
 * @return int id
 */
function pagemaster_userapi_getId($args) {

	if (!isset ($args['tid']))
	return LogUtil :: registerError("Missing argument 'tid'");
	if (!isset ($args['pid']))
	return LogUtil :: registerError("Missing argument 'pid'");
	$pid = $args['pid'];
	$tablename = "pagemaster_pubdata" . $args['tid'];
	$pub = DBUtil :: selectObjectArray($tablename, "pm_online = 1 and pm_pid = $pid");
	return $pub[0]['id'];

}
/**
 * Returns a Publication
 * @author kundi
 * @param int $args['tid']
 * @param int $args['pid']
 * @param int $args['id']
 * @param bool $args['checkPerm']
 * @param bool $args['getApprovalState']
 * @param bool $args['handlePluginFields']
 * @return array publication
 */
function pagemaster_userapi_getPub($args) {

	if (!isset ($args['tid']))
	return LogUtil :: registerError("Missing argument 'tid'");
	if ((!isset ($args['id'])) and (!isset ($args['pid'])))
	return LogUtil :: registerError("Missing argument 'id' or 'pid'.");

	$getApprovalState = isset ($args['getApprovalState']) ? $args['getApprovalState'] : false;
	$checkPerm = isset ($args['checkPerm']) ? $args['checkPerm'] : false;
	$handlePluginFields = isset ($args['handlePluginFields']) ? $args['handlePluginFields'] : false;
	$tid = $args['tid'];
	$pid = $args['pid'];
	$id = $args['id'];
	$tablename = "pagemaster_pubdata" . $tid;
	$pubtype = DBUtil :: selectObjectByID("pagemaster_pubtypes", $tid, 'tid');
	$uid = pnUserGetVar('uid');
	if ($uid <> '' and $pubtype['enableeditown'] == 1)
	$where .= ' ( pm_cr_uid = '.$uid.' or pm_online = 1 )';
	else
	$where .= ' pm_online = 1 ';

	$where .= ' AND pm_indepot = 0 ';
	$where .= " AND (pm_language = '' or pm_language = '".language_current()."')";
	$where .= " AND (pm_publishdate <= NOW() or pm_publishdate is null) AND (pm_expiredate >= NOW() or pm_expiredate is null)";

	if ($id == '') {
		$where .= ' AND pm_pid = ' . $pid;
	} else
	$where .= ' AND pm_id = ' . $id;
	$publist = DBUtil :: selectObjectArray($tablename, $where);
	$pubfields = DBUtil :: selectObjectArray("pagemaster_pubfields", "pm_tid = $tid");

	if ($handlePluginFields){
		include_once ('includes/pnForm.php'); //have to load, otherwise plugins can not be loaded... TODO
		$publist = handlePluginFields($publist, $pubfields);
	}

	$pubdata = $publist[0];

	if (count($publist) == 0)
	return LogUtil :: registerError("Pub not found");
	elseif (count($publist) > 1)
	return LogUtil :: registerError("Too many Pubs found");

	if ($checkPerm){
		if (!SecurityUtil :: checkPermission('pagemaster:full:', "$tid:$publist[0][core_pid]:", ACCESS_READ))
		return LogUtil :: registerError(_NOT_AUTHORIZED);
	}

	if ($getApprovalState)
	WorkflowUtil :: getWorkflowForObject($pubdata, $tablename, 'id', 'pagemaster');

	return ($pubdata);
}

/**
 * Returns a Publication List
 * @author kundi
 * @param $args['tid']
 * @param $args['startnum']
 * @param $args['itemsperpage']
 * @param $args['countmode'] no, just, both
 * @param bool $args['checkperm']
 * @param bool $args['handlePluginFields']
 * @param bool $args['getApprovalState']
 * @param bool $args['justOwn']
 * @return array publication or count
 */
function pagemaster_userapi_pubList($args) {

	if (!isset ($args['tid']))
	return LogUtil :: registerError("Missing argument 'tid'");

	$handlePluginFields = isset ($args['handlePluginFields']) ? $args['handlePluginFields'] : false;
	$justOwn = isset ($args['justOwn']) ? $args['justOwn'] : false;
	$checkPerm = isset ($args['checkPerm']) ? $args['checkPerm'] : false;
	$getApprovalState = isset ($args['getApprovalState']) ? $args['getApprovalState'] : false;
	if ($checkPerm){
		if (!SecurityUtil :: checkPermission('pagemaster:list:', "$tid::", ACCESS_READ))
		return LogUtil :: registerError(_NOT_AUTHORIZED);
	}

	$filter = $args['filter'];
	$orderby = $args['orderby'];
	$tid = $args['tid'];
	

	// Optional arguments.
	if (!isset ($args['startnum']) || !is_numeric($args['startnum'])) {
		$args['startnum'] = 1;
	}
	if (!isset ($args['itemsperpage']) || !is_numeric($args['itemsperpage'])) {
		$args['itemsperpage'] = -1;
	}
	if (!isset ($args['justcount']) || !is_numeric($args['justcount'])) {
		$args['justcount'] = 'no';
	}
	if (!isset ($args['pubfields']))
	$pubfields = DBUtil :: selectObjectArray("pagemaster_pubfields", "pm_tid = $tid");
	else
	$pubfields = $args['pubfields'];

	if (!isset ($args['pubtype']))
	$pubtype = DBUtil :: selectObjectByID("pagemaster_pubtypes", $tid, 'tid');
	else
	$pubtype = $args['pubtype'];


	if ($orderby == '' or !isset($orderby)) {
		if ($pubtype['sortfield1'] <> '') {
			if ($pubtype['sortdesc1'] == 1)
			$orderby = $pubtype['sortfield1'] . ' desc ';
			else
			$orderby = $pubtype['sortfield1'] . ' asc ';

			if ($pubtype['sortfield2'] <> '') {
				if ($pubtype['sortdesc2'] == 1)
				$orderby .= ', ' . $pubtype['sortfield2'] . ' desc ';
				else
				$orderby .= ', ' . $pubtype['sortfield2'] . ' asc ';
			}
			if ($pubtype['sortfield3'] <> '') {
				if ($pubtype['sortdesc3'] == 1)
				$orderby .= ', ' . $pubtype['sortfield3'] . ' desc ';
				else
				$orderby .= ', ' . $pubtype['sortfield3'] . ' asc ';

			}
		}
	}
	include_once ('includes/pnForm.php'); //have to load, otherwise plugins can not be loaded... TODO


	Loader :: LoadClass("FilterUtil");

	foreach ($pubfields as $key => $field) {
		$plugin = pagemasterGetPlugin($field['fieldplugin']);
		if (isset ($plugin->filterClass))
		$filterPlugins[$plugin->filterClass]['fields'][] = $field['name'];
		//check for tables to join
		if ($args['countmode'] <> 'just'){
			//dont join for just
			if ($field['fieldplugin'] == 'function.pmformpubinput.php'){
				$vars = explode(';',$field['typedata']);
				$join_tid = $vars[0];
				$join_filter = $vars[1];
				$join =  $vars[2];
				$join_fields = $vars[3];
				$join_arr = explode(',',$join_fields);
				if ($join == 'on'){
					foreach ($join_arr as $value)
					{
						list($x, $y) = explode(':',$value);
						$join_field_arr[] = $x;
						$object_field_name_arr[] = $y;
					}
					$joinInfo[] = array ('join_table'         =>  'pagemaster_pubdata'.$join_tid,
                           'join_field'         =>  $join_field_arr,
                           'object_field_name'  =>  $object_field_name_arr,
                           'compare_field_table'=>  $field['name'],
                           'compare_field_join' =>  'core_pid');
				}
			}
		}
	}
	if (isset($joinInfo))
	$tbl_alias = "tbl.";
	else
	$tbl_alias = "";
	
	//check if some plugin specific orderby has to be done
	$orderby = handlePluginOrderBy($orderby, $pubfields,$tbl_alias);
	
	$tablename = "pagemaster_pubdata" . $tid;
	$fu = & new FilterUtil(array (
		'table' => $tablename,
		'plugins' => $filterPlugins
	));
	
	if ($filter <> '')
	$fu->setFilter($filter);
	else
	if ($pubtype['defaultfilter'] <> '')
	$fu->setFilter($pubtype['defaultfilter']);
	$filter_where = $fu->GetSQL();
	
	$uid = pnUserGetVar('uid');

	if ($uid <> '' and $pubtype['enableeditown'] == 1)
	$where .= '( '.$tbl_alias.'pm_cr_uid = '.$uid.' or '.$tbl_alias.'pm_online = 1 )';
	else
	$where .= ' '.$tbl_alias.'pm_online = 1 ';

	if ($uid <> '' and $pubtype['enableeditown'] == 1)
	$where .= ' AND ( '.$tbl_alias.'pm_cr_uid = '.$uid.' or '.$tbl_alias.'pm_showinlist = 1 )';
	else
	$where .= ' AND '.$tbl_alias.'pm_showinlist = 1 ';


	$where .= ' AND '.$tbl_alias.'pm_indepot = 0 ';
	$where .= " AND ( ".$tbl_alias."pm_language = '' or ".$tbl_alias."pm_language = '".language_current()."')";
	$where .= " AND ( ".$tbl_alias."pm_publishdate <= NOW() or ".$tbl_alias."pm_publishdate is null) AND ( ".$tbl_alias."pm_expiredate >= NOW() or ".$tbl_alias."pm_expiredate is null)";
	if ( $justOwn and $uid <> '')
		$where .= ' AND '.$tbl_alias.'pm_cr_uid = '.$uid;
	
	if ($filter_where['where'] <> '')
	$where .= ' AND ' . $filter_where['where'];
	if ($args['countmode'] <> 'just'){
		if (isset($joinInfo)){
			$publist = DBUtil :: selectExpandedObjectArray($tablename, $joinInfo, $where, $orderby, $args['startnum'] - 1, $args['itemsperpage']);
		}
		else{
			$publist = DBUtil :: selectObjectArray($tablename, $where, $orderby, $args['startnum'] - 1, $args['itemsperpage']);
		}
		if ($getApprovalState){
			foreach ($publist as $key => $pub){
				WorkflowUtil :: getWorkflowForObject($pub, $tablename, 'id', 'pagemaster');
				$publist [$key] = $pub;
			}
		}
		if ($handlePluginFields){
			$publist = handlePluginFields($publist, $pubfields);
		}
	}
	if ($args['countmode'] == 'just' or $args['countmode'] == 'both')
	$pubcount = DBUtil :: selectObjectCount($tablename, str_replace(' tbl.', ' ', $where));

	return array (
		'publist' => $publist,
		'pubcount' => $pubcount
	);
}