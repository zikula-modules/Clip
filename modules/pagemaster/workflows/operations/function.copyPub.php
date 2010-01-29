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
 * copyPub operation
 *
 * @param  array  $obj                   object to copy
 * @param  int    $params['copyonline']  (optional) online value for the copy object
 * @param  int    $params['copystate']   (optional) state for the object copy object, default: initial
 * @return array  object id as index with boolean value: copy object if success, false otherwise
 */
function pagemaster_operation_copyPub(&$obj, $params)
{
    // assign the copy online and copypaste parameters if set
    if (isset($params['copyonline'])) {
        $obj['core_online'] = $params['copyonline'];
    }

    $copystate = isset($params['copystate']) ? $params['copystate'] : 'initial';

    // finds the higher pid
    $maxpid = DBUtil::selectFieldMax($obj['__WORKFLOW__']['obj_table'], 'core_pid', 'MAX');
    $obj['core_pid'] = $maxpid + 1;

    // save the object
    unset($obj['id']);
    DBUtil::insertObject($obj, $obj['__WORKFLOW__']['obj_table'], 'id');

    $obj['__WORKFLOW__']['obj_id'] = $obj['id'];
    unset($obj['__WORKFLOW__']['id']);

    // register the new workflow, return false if failure
    $workflow = new pnWorkflow($obj['__WORKFLOW__']['schemaname'], 'pagemaster');

    if (!$workflow->registerWorkflow($obj, $copystate)) {
        return array($obj['id'] => false);
    }

    return array($obj['id'] => $obj);
}
