<?php
function pagemaster_operation_moveToDepot(& $obj, $params) {

	$pubtype = DBUtil :: selectObjectByID("pagemaster_pubtypes", $obj['tid'], 'tid');

	if ($pubtype['enablerevisions']) {
		$obj['core_indepot'] = 1;
		$obj['core_online'] = 0;
		return (bool) DBUtil :: updateObject($obj, $obj['__WORKFLOW__']['obj_table']);
	} else {
		return (bool) DBUtil :: deleteObject($obj, $obj['__WORKFLOW__']['obj_table']);
	}

}
?>
