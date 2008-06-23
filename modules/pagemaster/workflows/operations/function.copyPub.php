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

function pagemaster_operation_copyPub(&$obj, $params)
{
    if (isset($params['copyonline'])) 
        $obj['core_online'] = $params['copyonline'];
        
    $copystate = isset($params['copystate']) ? $params['copystate'] : 'initial';
    
    unset($obj['id']);
    $maxpid = DBUtil::selectFieldMax($obj['__WORKFLOW__']['obj_table'], 'core_pid', 'MAX');
    $obj['core_pid'] = $maxpid+1;
    DBUtil::insertObject($obj, $obj['__WORKFLOW__']['obj_table'], 'id');
    $obj['__WORKFLOW__']['obj_id'] = $obj['id'];
    unset($obj['__WORKFLOW__']['id']);

    $workflow = new pnWorkflow($obj['__WORKFLOW__']['schemaname'],'pagemaster');
    return $workflow->registerWorkflow($obj, $copystate);
}
