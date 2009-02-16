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

function pagemaster_operation_updateOnlineState(&$obj, $params)
{
    // set the online parameter, or set it offline if is not set
    $obj['core_online'] = isset($params['online']) ? (int)$params['online'] : 0;

    // return the updated object
    return DBUtil::updateObject($obj, $obj['__WORKFLOW__']['obj_table']);
}
