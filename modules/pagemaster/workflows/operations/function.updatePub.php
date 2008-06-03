<?php

function pagemaster_operation_updatePub(&$obj, $params)
{
    if (isset($params['online'])) 
    	$obj['core_online'] = $params['online'];

    return (bool)DBUtil::updateObject($obj, $obj['__WORKFLOW__']['obj_table'],null, 'id');
}
?>
