<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Workflows_Operations
 */

/**
 * update operation.
 *
 * @param object $pub                   Publication to update.
 * @param bool   $params['newrevision'] Flag to disable a new revision creation (default: true) (optional).
 * @param bool   $params['silent']      Hide or display a status/error message, (default: false) (optional).
 * @param string $params['nextstate']   State for the updated publication if revisions enabled (optional).
 * @param array  $params                Fixed value(s) to change in the publication.
 *
 * @return bool|array False on failure or Publication core_uniqueid as index with true as value.
 */
function Clip_operation_update(&$pub, &$params)
{
    $dom = ZLanguage::getModuleDomain('Clip');

    // process the available parameters
    if (isset($params['online'])) {
        $pub['core_online'] = (int)(bool)$params['online'];
    }
    $params['newrevision'] = isset($params['newrevision']) ? (bool)$params['newrevision'] : true;
    $params['silent']      = isset($params['silent']) ? (bool)$params['silent'] : false;

    // utility vars
    $tbl = Doctrine_Core::getTable('ClipModels_Pubdata'.$pub['core_tid']);

    // overrides newrevision in pubtype. gives the dev. the possibility to not generate a new revision
    // e.g. when the revision is pending (waiting state) and will be updated
    $pubtype = Clip_Util::getPubType($pub['core_tid']);

    // checks if there are fixed operation values to update
    $update = array();
    foreach ($params as $key => $val) {
        if (!in_array($key, array('newrevision', 'silent', 'nextstate')) && $pub->contains($key)) {
            $pub[$key] = $val;
        }
    }

    // initializes the result flag
    $result = false;

    // get the max revision
    $maxrev = $tbl->selectFieldFunction('core_revision', 'MAX', array(array('core_pid = ?', $pub['core_pid'])));

    if ($pubtype['enablerevisions'] && $params['newrevision']) {
        // create the new revision
        $rev = $pub->copy();

        $rev['core_revision'] = $maxrev + 1;

        if ($rev->isValid()) {
            if ($pub['core_online'] == 1) {
                // set all other to offline
                $tbl->createQuery()
                    ->update()
                    ->set('core_online', 0)
                    ->where('core_online = ?', 1)
                    ->andWhere('core_pid = ?', $pub['core_pid'])
                    ->execute();
            }

            $rev->trySave();
            $result = true;

            // register the new workflow, return false if failure
            $rev->mapValue('__WORKFLOW__', $pub['__WORKFLOW__']);

            $workflow = new Clip_Workflow($pubtype, $rev);

            if (!$workflow->registerWorkflow($params['nextstate'])) {
                $result = false;

                // delete the previously inserted record
                $rev->delete();
            }

            // revert the next state of the old revision
            $params['nextstate'] = $pub['__WORKFLOW__']['state'];
        }
    } else {
        // update the object with a new revision
        $pub['core_revision'] = $maxrev + 1;

        if ($pub->isValid()) {
            $pub->trySave();
            $result = true;
        }
    }

    if ($result) {
        // hooks: let know that the publication was updated
        $pub->notifyHooks('process_edit');

        // event: notify the operation data
        $pub = Clip_Event::notify('data.edit.operation.update', $pub, $params)->getData();
    }

    // output message
    if (!$params['silent']) {
        if ($result) {
            LogUtil::registerStatus(__('Done! Publication updated.', $dom));
        } else {
            LogUtil::registerError(__('Error! Failed to update the publication.', $dom));
            if (ModUtil::getVar('Clip', 'devmode', false)) {
                LogUtil::registerError($pub->getErrorStackAsString());
            }
        }
    }

    // returns the operation result
    return $result;
}
