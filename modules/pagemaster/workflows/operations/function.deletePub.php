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

function pagemaster_operation_deletePub(&$obj, $params)
{
	// if delete is successful 
    if (WorkflowUtil::deleteWorkflow($obj)) {
        // let know that the item was deleted
        pnModCallHooks('item', 'delete', $obj['tid'].'_'.$obj['core_pid'], array('module' => 'pagemaster'));
	    return array('core_online' => 0, 'deleted' => 1);

	// false otherwise
    } else {
	    return false;
	}
}
