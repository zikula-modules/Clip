<?php

function pagemaster_operation_createPub(&$obj, $params)
{
    $online = isset($params['online']) ? $params['online'] : false;
    $obj['core_online'] = $online;
    $maxpid = DBUtil :: selectFieldMax($obj['__WORKFLOW__']['obj_table'], 'core_pid', 'MAX');
    $obj['core_pid'] = $maxpid+1;
    return DBUtil::insertObject($obj, $obj['__WORKFLOW__']['obj_table'], 'id');
}
?>
