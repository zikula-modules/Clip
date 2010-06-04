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
 * @param  array  $pub               publication to delete
 * @param  bool   $params['silent']  (optional) hide or display a status/error message, default: false
 * @return array  publication id as index with boolean value: true if success, false otherwise
 */
function PageMaster_operation_deletePub(&$pub, $params)
{
    $dom = ZLanguage::getModuleDomain('PageMaster');

    // process the available parameters
    $silent = isset($params['silent']) ? (bool)$params['silent'] : false;

    // process the deletion
    $result = false;
    if (WorkflowUtil::deleteWorkflow($pub)) {
        $result = true;

        // checks if there's any other revision of this publication
        $count = DBUtil::selectObjectCount($pub['__WORKFLOW__']['obj_table'], "pm_pid = '{$pub['core_pid']}'");

        if ($count == 0) {
            // if not, let know that the publication was deleted
            pnModCallHooks('item', 'delete', $pub['tid'].'-'.$pub['core_pid'], array('module' => 'PageMaster'));
        }
    }

    // output message
    if (!$silent) {
        if ($result) {
            LogUtil::registerStatus(__('Done! Publication deleted.', $dom));
        } else {
            LogUtil::registerError(__('Error! Failed to delete the publication.', $dom));
        }
    }

    // returns the result
    return $result;
}
