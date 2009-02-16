<?php
/**
 * PageMaster
 *
 * @copyright   (c) PageMaster Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package     Zikula_3rdParty_Modules
 * @subpackage  pagemaster
 *
 */

function pagemaster_operation_updatePub(&$obj, $params)
{
    // set the online parameter if set
    if (isset($params['online'])) { 
        $obj['core_online'] = (int)$params['online'];
    }
    // increase the revision number
    $obj['core_revision']++;

    // update the object
    $obj = DBUtil::updateObject($obj, $obj['__WORKFLOW__']['obj_table'], null, 'id');

    // let know that this item was updated
    pnModCallHooks('item', 'update', $obj['tid'].'_'.$obj['core_pid'], array('module' => 'pagemaster'));

    // return the updated object
    return $obj;
}
