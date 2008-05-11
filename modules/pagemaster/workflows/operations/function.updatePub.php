<?php

function pagemaster_operation_updatePub(&$obj, $params)
{
    $online = isset($params['online']) ? $params['online'] : false;
    $obj['core_online'] = $online;
    return (bool)DBUtil::updateObject($obj, $obj['__WORKFLOW__']['obj_table'],null, 'id');
}
?>
