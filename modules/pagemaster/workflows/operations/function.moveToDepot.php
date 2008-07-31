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

function pagemaster_operation_moveToDepot($obj, $params)
{
    $pubtype = DBUtil::selectObjectByID('pagemaster_pubtypes', $obj['tid'], 'tid');

    if ($pubtype['enablerevisions']) {
        $obj['core_indepot'] = 1;
        $obj['core_online'] = 0;
        return (bool) DBUtil::updateObject($obj, $obj['__WORKFLOW__']['obj_table']);
    } else {
        return WorkflowUtil::deleteWorkflow($obj);
    	//return (bool) DBUtil::deleteObject($obj, $obj['__WORKFLOW__']['obj_table']);
    }
}
