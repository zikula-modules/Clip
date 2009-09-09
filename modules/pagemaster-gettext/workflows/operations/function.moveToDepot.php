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
    $obj['core_indepot'] = 1;
    $obj['core_online']  = 0;
    return DBUtil::updateObject($obj, $obj['__WORKFLOW__']['obj_table']);
}
