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

function pagemaster_operation_moveOthersToDepot(& $obj, $params)
{
    $pubtype = DBUtil::selectObjectByID('pagemaster_pubtypes', $obj['tid'], 'tid');

    $upd_obj['core_indepot'] = 1;
    $upd_obj['core_online'] = 0;

    if ($pubtype['enablerevisions']) {
        return (bool) DBUtil::updateObject($upd_obj, $obj['__WORKFLOW__']['obj_table'], 'pm_pid = '.$obj['core_pid'].' AND pm_id <> '.$obj['id']);
    } else {
        //TODO Workflows has to be deleted 
    	return (bool) DBUtil::deleteObject(null, $obj['__WORKFLOW__']['obj_table'], 'pm_pid = '.$obj['core_pid'].' AND pm_id <> '.$obj['id']);
    }
}
