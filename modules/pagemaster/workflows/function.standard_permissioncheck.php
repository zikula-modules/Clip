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
 * Permission check for workflow schema 'standard'
 *
 * @param array $obj
 * @param int $permLevel
 * @param int $currentUser
 * @param string $actionId
 * @return bool
 */
function pagemaster_workflow_standard_permissioncheck($obj, $permLevel, $currentUser, $actionId)
{
    if (!empty ($obj)) {
        // process $obj and calculate an instance
        $pid = $obj['core_pid'];
        $tid = getTidFromTablename($obj['__WORKFLOW__']['obj_table']);
        $pubtype = DBUtil::selectObjectByID('pagemaster_pubtypes', $tid, 'tid');
        if ($pubtype['enableeditown'] == 1 and $obj['core_author'] == pnUserGetVar('uid')) {
            return true;
        } else {
            return SecurityUtil::checkPermission('pagemaster:input:', "$tid:$pid:$obj[__WORKFLOW__][state]", $permLevel, $currentUser);
        }
    } else {
        $tid = FormUtil::getPassedValue('tid');
        return SecurityUtil::checkPermission('pagemaster:input:', "$tid::", $permLevel, $currentUser);
    }
}
