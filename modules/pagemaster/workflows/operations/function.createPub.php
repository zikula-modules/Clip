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

function pagemaster_operation_createPub(&$obj, $params)
{
    // set the online value if set
    $obj['core_online'] = isset($params['online']) ? (int)$params['online'] : 0;

    // find a new pid
    $maxpid = DBUtil::selectFieldMax($obj['__WORKFLOW__']['obj_table'], 'core_pid', 'MAX');
    $obj['core_pid'] = $maxpid + 1;

    // assign the author
    $obj['core_author'] = pnUserGetVar('uid');

    // save the object
    $obj = DBUtil::insertObject($obj, $obj['__WORKFLOW__']['obj_table'], 'id');

    // let know that an item was created
    pnModCallHooks('item', 'create', $obj['tid'].'_'.$obj['core_pid'], array('module' => 'pagemaster'));

    // return the created item
    return $obj;
}
