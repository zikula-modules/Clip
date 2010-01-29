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

/**
 * deletePub operation
 *
 * @param  array  $obj     object to delete
 * @param  array  $params  (none)
 * @return array  object id as index with boolean value: true if success, false otherwise
 */
function pagemaster_operation_deletePub(&$obj, $params)
{
    // returns false if fails
    if (!PmWorkflowUtil::deleteWorkflow($obj)) {
        return false;
    }

    // let know that the item was deleted
    pnModCallHooks('item', 'delete', $obj['tid'].'-'.$obj['core_pid'], array('module' => 'pagemaster'));

    return array($obj['id'] => true);
}
