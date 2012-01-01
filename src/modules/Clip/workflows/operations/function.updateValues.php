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
 * updateValues operation.
 *
 * @param object $pub                 Publication to change update.
 * @param bool   $params['allrev']    Wheter to update only the pub or all its revisions, (optional) (default: false).
 * @param bool   $params['silent']    Hide or display a status/error message, (optional) (default: false).
 * @param string $params['nextstate'] State for the updated publication if revisions enabled (optional).
 * @param array  $params              Value(s) to change in the publication.
 *
 * @return bool|array False on failure or Publication core_uniqueid as index with true as value.
 */
function Clip_operation_updateValues(&$pub, $params)
{
    $dom = ZLanguage::getModuleDomain('Clip');

    // process the available parameters
    $allrev = isset($params['allrev']) ? (bool)$params['allrev'] : false;
    $silent = isset($params['silent']) ? (bool)$params['silent'] : false;
    unset($params['allrev'], $params['silent'], $params['nextstate']);

    // initializes the result flag
    $result = false;

    // build the array of values to update
    $update = array();
    foreach ($params as $key => $val) {
        if ($pub->contains($key)) {
            $update[$key] = $val;
        }
    }

    if ($update) {
        if (!$allrev) {
            // update the passed pub only
            foreach ($update as $key => $val) {
                $pub[$key] = $val;
            }

            // validate and save the publication
            if ($pub->isValid()) {
                $pub->trySave();
                $result = true;
            }
        } else {
            // update all the revisions
            $q = Doctrine_Core::getTable('ClipModels_Pubdata'.$pub['core_tid'])
                     ->createQuery()
                     ->update()
                     ->where('core_pid = ?', $pub->core_pid);

            foreach ($update as $key => $val) {
                $q->set($key, $val);
            }

            $q->execute();
            $result = true;
        }
    } else {
        // not having fields to update is not a failure
        // then do not interrupt the workflow action execution
        $result = true;
    }

    if ($result) {
        $result = array($pub['core_uniqueid'] => true);

        // hooks: let know that the publication was updated
        $pub->notifyHooks('process_edit');
    }

    // output message
    if (!$silent) {
        if ($result) {
            if (isset($update['core_online'])) {
                if ($update['core_online'] == 1) {
                    LogUtil::registerStatus(__("Publication status set to 'published'.", $dom));
                } else {
                    LogUtil::registerStatus(__("Publication status set to 'unpublished'.", $dom));
                }
            }
            if (isset($update['core_intrash'])) {
                if ($update['core_intrash'] == 1) {
                    LogUtil::registerStatus(__("Publication moved to the recycle bin.", $dom));
                } else {
                    LogUtil::registerStatus(__("Publication was recovered from the recycle bin.", $dom));
                }
            }
        } else {
            LogUtil::registerError(__('Error! Failed to update the publication.', $dom));
        }
    }

    // returns the operation result
    return $result;
}
