<?php
// Used to completely remove non-approved content
function pagemaster_operation_ereaseRevision(& $obj, $params) {
	return (bool) WorkflowUtil :: deleteWorkflow($obj);
}