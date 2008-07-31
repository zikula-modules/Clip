<?php
/**
 * PageMaster
 *
 * @copyright (c) 2008, PageMaster Team
 * @link        http://code.zikula.org/projects/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package     Zikula_3rd_party_Modules
 * @subpackage  pagemaster
 *
 */

function pagemaster_operation_updatePub(&$obj, $params)
{
    if (isset($params['online'])) 
        $obj['core_online'] = $params['online'];

    return (bool)DBUtil::updateObject($obj, $obj['__WORKFLOW__']['obj_table'],null, 'id');
}
