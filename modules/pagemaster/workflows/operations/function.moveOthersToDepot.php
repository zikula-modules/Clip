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

function pagemaster_operation_moveOthersToDepot(&$obj, $params)
{
    $pubtype = DBUtil::selectObjectByID('pagemaster_pubtypes', $obj['tid'], 'tid');

    // move to depot and makes offline all the records in that pid with different id 
    $upd_obj['core_indepot'] = 1;
    $upd_obj['core_online'] =  0;

    if ($pubtype['enablerevisions']) {
        return (bool) DBUtil::updateObject($upd_obj, $obj['__WORKFLOW__']['obj_table'], 'pm_pid = '.$obj['core_pid'].' AND pm_id <> '.$obj['id']);
    } else {
        // Select all the records' IDs to remove and delete their workflows
        $records = DBUtil::selectObjectArray($obj['__WORKFLOW__']['obj_table'], 'pm_pid = '.$obj['core_pid'].' AND pm_id <> '.$obj['id'], '', null, null, 'id', null, null, array('id'));
        if ($records && DBUtil::deleteWhere('workflows', 'obj_table = \''.$obj['__WORKFLOW__']['obj_table'].'\' AND obj_id IN ('.implode(',', array_keys($records)).')')) {
            // delete the records
            if (DBUtil::deleteObject(null, $obj['__WORKFLOW__']['obj_table'], 'pm_pid = '.$obj['core_pid'].' AND pm_id <> '.$obj['id'])) {
                return $obj;
            }
        }
        // if there's an error, return false
        return false; 
    }
}
