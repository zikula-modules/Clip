<?php
function pagemaster_operation_deletePub(&$obj, $params)
{
    return (bool)WorkflowUtil::deleteWorkflow($obj);
}
?>
