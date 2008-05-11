<?php


/**
 * Increase Hit Counter
 * This logic is implemented in a plugin to let the user decide if he wants to use it or not
 * Hitcount breaks mysql table cache
 * @author kundi
 * @param $args['tid'] tid
 * @param $args['pid'] pid
 */
function smarty_function_hitcount($params, & $smarty) {


	$tid = $params['tid'];
	$pid = $params['pid'];

	if (!$tid)
	return 'Required parameter [tid] not provided in smarty_function_hitcount';
	if (!$pid)
	return 'Required parameter [pid] not provided in smarty_function_hitcount';
	
	$tablename = 'pn_pagemaster_pubdata' . $tid;
	
	$sql = "update $tablename set pm_hitcount = pm_hitcount + 1 where pm_pid = $pid ";  
	DBUtil :: executeSQL($sql);
	


}