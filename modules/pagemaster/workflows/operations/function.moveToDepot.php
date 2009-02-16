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

function pagemaster_operation_moveToDepot($obj, $params)
{
    $pubtype = DBUtil::selectObjectByID('pagemaster_pubtypes', $obj['tid'], 'tid');

    // if revisions enabled for this pubtype
    // move the object to depot and return the updated object
    if ($pubtype['enablerevisions']) {
        $obj['core_indepot'] = 1;
        $obj['core_online']  = 0;
        return DBUtil::updateObject($obj, $obj['__WORKFLOW__']['obj_table']);

    // delete the object otherwise
    } else {
        if (WorkflowUtil::deleteWorkflow($obj)) {
            return array('core_online' => 0);
        }
        return false;
    }
}
