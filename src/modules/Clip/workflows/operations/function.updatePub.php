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
 * @param  array  $pub                    publication to update
 * @param  int    $params['online']       (optional) online value for the publication
 * @param  bool   $params['newrevision']  (optional) flag to disable a new revision creation, default: true
 * @param  string $params['nextstate']    (optional) state for the updated publication
 * @param  bool   $params['silent']       (optional) hide or display a status/error message, default: false
 * @return array  publication id as index with boolean value: true if success, false otherwise
 */
function PageMaster_operation_updatePub(&$pub, &$params)
{
    $dom = ZLanguage::getModuleDomain('PageMaster');

    // process the available parameters
    if (isset($params['online'])) {
        $pub['core_online'] = (int)(bool)$params['online'];
    }
    $newrevision = isset($params['newrevision']) ? (bool)$params['newrevision'] : true;
    $silent      = isset($params['silent']) ? (bool)$params['silent'] : false;

    // overrides newrevision in pubtype. gives the dev. the possibility to not genereate a new revision
    // e.g. when the revision is pending (waiting state) and will be updated
    $pubtype = PageMaster_Util::getPubType($pub['tid']);

    if ($pubtype['enablerevisions'] && $pub['core_online'] == 1) {
        // set all other to offline
        $data = array('core_online' => 0);

        if (!DBUtil::updateObject($data, $pub['__WORKFLOW__']['obj_table'], "pm_online = '1' AND pm_pid = '{$pub['core_pid']}'")) {
            if (!$silent) {
                LogUtil::registerError(__('Error! Could not unpublish the other revisions of this publication.', $dom));
            }
            return array($pub['id'] => false);
        }
    }

    // initializes the result flag
    $result = false;

    // get the max revision
    $maxrev = DBUtil::selectFieldMax($pub['__WORKFLOW__']['obj_table'], 'core_revision', 'MAX', "pm_pid = '{$pub['core_pid']}'");

    if ($pubtype['enablerevisions'] && $newrevision) {
        // create the new revision
        $rev = $pub;

        $rev['core_revision'] = $maxrev + 1;
        unset($rev['id']);

        if (DBUtil::insertObject($rev, $pub['__WORKFLOW__']['obj_table'], 'id')) {
            $result = true;

            $rev['__WORKFLOW__']['obj_id'] = $rev['id'];
            unset($rev['__WORKFLOW__']['id']);

            // register the new workflow, return false if failure
            $workflow = new Zikula_Workflow($rev['__WORKFLOW__']['schemaname'], 'PageMaster');

            if (!$workflow->registerWorkflow($rev, $params['nextstate'])) {
                $result = false;

                // delete the previously inserted record
                DBUtil::deleteObjectByID($rev['__WORKFLOW__']['obj_table'], $rev['id'], 'id');
            }

            // revert the next state of the old revision
            $params['nextstate'] = $pub['__WORKFLOW__']['state'];
        }
    } else {
        // update the object with a new revision
        $pub['core_revision'] = $maxrev + 1;

        if (DBUtil::updateObject($pub, $pub['__WORKFLOW__']['obj_table'], null, 'id')) {
            $result = true;
        }
    }

    if ($result) {
        // let know that the publication was updated
        ModUtil::callHooks('item', 'update', $pub['tid'].'-'.$pub['core_pid'], array('module' => 'PageMaster'));
    }

    // output message
    if (!$silent) {
        if ($result) {
            LogUtil::registerStatus(__('Done! Publication updated.', $dom));
        } else {
            LogUtil::registerError(__('Error! Failed to update the publication.', $dom));
        }
    }

    // return the update result
    return $result;
}
