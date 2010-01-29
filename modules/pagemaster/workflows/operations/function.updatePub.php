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
 * updatePub operation
 *
 * @param  array  $obj                    object to process
 * @param  int    $params['online']       (optional) online value for the object
 * @param  bool   $params['newrevision']  (optional) flag to create a new revision or not, default: true
 * @param  string $params['nextstate']    (optional) state of the updated object
 * @return array  object id as index with boolean value: true if success, false otherwise
 */
function pagemaster_operation_updatePub(&$obj, $params)
{
    // set the online parameter if set
    if (isset($params['online'])) {
        $obj['core_online'] = (int)$params['online'];
    }

    $newrevision = isset($params['newrevision']) ? (bool)$params['newrevision'] : true;

    $pubtype = PMgetPubType($obj['tid']);
    // overrides newrevision in pubtype. gives the dev. the possibility to not genereate a new revision
    // e.g. when revision is in state wating and will be updated

    if ($pubtype['enablerevisions'] && $obj['core_online'] == 1) {
        // set all other to offline
        $data = array('core_online' => 0);
        if (!DBUtil::updateObject($data, $obj['__WORKFLOW__']['obj_table'], "pm_online = '1' and pm_pid = '{$obj['core_pid']}'")) {
            return array($obj['id'] => false);
        }
    }

    if ($pubtype['enablerevisions'] && $newrevision) {
        $nextState = isset($params['nextstate']) ? $params['nextstate'] : $obj['__WORKFLOW__']['state'];

        // build the new record
        $obj['core_revision']++;

        unset($obj['id']);
        DBUtil::insertObject($obj, $obj['__WORKFLOW__']['obj_table'], 'id');

        $obj['__WORKFLOW__']['obj_id'] = $obj['id'];
        unset($obj['__WORKFLOW__']['id']);

        // register the new workflow, return false if failure
        $workflow = new pnWorkflow($obj['__WORKFLOW__']['schemaname'], 'pagemaster');

        if (!$workflow->registerWorkflow($obj, $nextState)) {
            return array($obj['id'] => false);
        }

    } else {
        // update the object without a new revision
        $obj['core_revision']++;
        $obj = DBUtil::updateObject($obj, $obj['__WORKFLOW__']['obj_table'], null, 'id');
    }

    // let know that this item was updated
    pnModCallHooks('item', 'update', $obj['tid'].'-'.$obj['core_pid'], array('module' => 'pagemaster'));

    // return the updated object
    return array($obj['id'] => true);
}
