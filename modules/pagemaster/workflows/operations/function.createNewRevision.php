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

function pagemaster_operation_createNewRevision(&$obj, $params)
{

    if (!isset($params['nextstate'])) {
        return;
    }

    $online = (isset($params['online']) ) ? $params['online'] : false;
    $obj['core_online'] = $online;

    $nextState = $params['nextstate'];

    if ($online == 1) {
        //set all other to offline
        $data = array('core_online' => 0);
        $result = DBUtil::updateObject($data, $obj['__WORKFLOW__']['obj_table'], 'pm_online = 1 and pm_pid = '.$obj['core_pid']);
    }
    $new_rev = $obj;
    unset($new_rev['id']);
    $new_rev['core_revision'] = $new_rev['core_revision']  + 1 ; 
    
    
    DBUtil::insertObject($new_rev, $obj['__WORKFLOW__']['obj_table'], 'id');
    
    $obj = $new_rev;
    
    $new_rev['__WORKFLOW__']['obj_id'] = $new_rev['id'];
    unset($new_rev['__WORKFLOW__']['id']);
    $workflow = new pnWorkflow($obj['__WORKFLOW__']['schemaname'],'pagemaster');
    $workflow->registerWorkflow($new_rev, $nextState);
    pnModCallHooks('item', 'update', $obj['tid'].'_'.$obj['core_pid'], array('module' => 'pagemaster'));
    return true;
    
    /*$revision = array('tid' => $obj['tid'],
                      'id'  => $new_rev['id'],
                      'pid' => $obj['core_pid'],
                      'prevversion' => 1 );
    return DBUtil::insertObject($revision, 'pagemaster_revisions');*/

}
