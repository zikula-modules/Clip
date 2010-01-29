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
 * moveToDepot operation
 *
 * @param  array  $obj     object to move
 * @param  array  $params  (none)
 * @return array  object id as index with boolean value: true if success, false otherwise
 */
function pagemaster_operation_moveToDepot($obj, $params)
{
    $obj['core_indepot'] = 1;
    $obj['core_online']  = 0;

    $res = (bool)DBUtil::updateObject($obj, $obj['__WORKFLOW__']['obj_table']);

    return array($obj['id'] => $res);
}
