<?php
function pagemaster_operation_moveOthersToDepot(& $obj, $params) {

	$pubtype = DBUtil :: selectObjectByID("pagemaster_pubtypes", $obj['tid'], 'tid');
	$upd_obj['core_indepot'] = 1;
	$upd_obj['core_online'] = 0;
	
	if ($pubtype['enablerevisions']) {
		return (bool) DBUtil :: updateObject($upd_obj, $obj['__WORKFLOW__']['obj_table'], 'pm_pid = ' . $obj['core_pid'] . ' AND pm_id <> ' . $obj['id']);
	} else {
		return (bool) DBUtil :: deleteObject(null, $obj['__WORKFLOW__']['obj_table'], 'pm_pid = ' . $obj['core_pid'] . ' AND pm_id <> ' . $obj['id']);
	}
	

}
?>
