<?php
/**
 * PageMaster
 *
 * @copyright (c) 2008, PageMaster Team
 * @link        http://code.zikula.org/projects/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package     Zikula_3rd_party_Modules
 * @subpackage  pagemaster
 */

/**
 * Permission check for workflow schema 'none'
 *
 * @param array $obj
 * @param int $permLevel
 * @param int $currentUser
 * @param string $actionId
 * @return bool
 */
function pagemaster_workflow_none_permissioncheck($obj, $permLevel, $currentUser, $actionId)
{
    if (!empty($obj)) {
        // process $obj and calculate an instance
        $pid = $obj['core_pid'];
        $tid = getTidFromTablename($obj['__WORKFLOW__']['obj_table']);

        $pubtype = DBUtil::selectObjectByID('pagemaster_pubtypes', $tid, 'tid');
        if ($pubtype['enableeditown'] == 1 and $obj['pm_cr_uid'] == pnUserGetVar('uid')) {
            return true;
        } else {
            return SecurityUtil :: checkPermission('pagemaster:input:', "$tid:$pid:$obj[__WORKFLOW__][state]", $permLevel, $currentUser);
        }

    } else {
        $tid = FormUtil::getPassedValue('tid');
        return SecurityUtil::checkPermission('pagemaster:input:', "$tid::", $permLevel, $currentUser);
    }
}
