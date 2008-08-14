<?php
/**
 * PageMaster
 *
 * @copyright (c) 2008, PageMaster Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package     Zikula_3rd_party_Modules
 * @subpackage  pagemaster
 */

function pagemaster_operation_deletePub(&$obj, $params)
{
    pnModCallHooks('item', 'delete', $obj['tid'].'_'.$obj['core_pid'], array('module' => 'pagemaster'));
	return (bool)WorkflowUtil::deleteWorkflow($obj);
}
