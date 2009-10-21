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

    if (isset($params['newrevision'])) {
        $newrevision = $params['newrevision'];
    } else {
        $newrevision = 1;
    }

    $pubtype = DBUtil::selectObjectByID('pagemaster_pubtypes', $obj['tid'], 'tid');
    // overrides newrevision in pubtype. gives the dev. the possibility to not genereate a new revision
    // e.g. when revision is in state wating and will be updated

    if ($pubtype['enablerevisions'] && $obj['core_online'] == 1) {
        // set all other to offline
        $data = array('core_online' => 0);
        $result = DBUtil::updateObject($data, $obj['__WORKFLOW__']['obj_table'], 'pm_online = 1 and pm_pid = '.$obj['core_pid']);
    }

    if ($pubtype['enablerevisions'] && $newrevision == 1) {
        if (isset($params['online'])) {
            $nextState = $params['nextstate'];
        } else {
            $nextState = $obj['__WORKFLOW__']['state'];
        }

        $new_rev = $obj;
        unset($new_rev['id']);
        $new_rev['core_revision'] = $new_rev['core_revision']  + 1 ;

        $obj = $new_rev = DBUtil::insertObject($new_rev, $obj['__WORKFLOW__']['obj_table'], 'id');

        $new_rev['__WORKFLOW__']['obj_id'] = $new_rev['id'];
        unset($new_rev['__WORKFLOW__']['id']);
        $workflow = new pnWorkflow($obj['__WORKFLOW__']['schemaname'],'pagemaster');
        $workflow->registerWorkflow($new_rev, $nextState);

    } else {
        // update the object without a new revision
        $obj['core_revision']++;
        $obj = DBUtil::updateObject($obj, $obj['__WORKFLOW__']['obj_table'], null, 'id');
    }

    // let know that this item was updated
    pnModCallHooks('item', 'update', $obj['tid'].'_'.$obj['core_pid'], array('module' => 'pagemaster'));

    // return the updated object
    return $obj;
}
