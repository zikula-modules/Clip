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
 * delete operation.
 *
 * @param object $pub              Publication to delete.
 * @param bool   $params['allrev'] Wheter to delete only the pub or all its revisions (optional) (default: true).
 * @param bool   $params['silent'] Hide or display a status/error message (optional) (default: false).
 *
 * @return bool|array False on failure or Publication core_uniqueid as index with true as value.
 */
function Clip_operation_delete(&$pub, $params)
{
    $dom = ZLanguage::getModuleDomain('Clip');

    // process the available parameters
    // TODO implement allrev, dleeting all workflows
    $params['silent'] = isset($params['silent']) ? (bool)$params['silent'] : false;

    // process the deletion
    $result = false;

    // utility vars
    $pubtype = Clip_Util::getPubType($pub['core_tid']);

    $workflow = new Clip_Workflow($pubtype, $pub);

    if ($workflow->deleteWorkflow()) {
        // event: notify the operation data
        $pub = Clip_Event::notify('data.edit.operation.delete', $pub, $params)->getData();

        $result = array($pub['core_uniqueid'] => true);

        $tbl = Doctrine_Core::getTable('ClipModels_Pubdata'.$pub['core_tid']);

        // checks if there's any other revision of this publication
        $count = $tbl->selectFieldFunction('1', 'COUNT', array(array('core_pid = ?', $pub['core_pid']))) + 1;

        if ($count == 0) {
            // hooks: if no other revisions, let know that a publication was deleted
            $pub->notifyHooks('process_delete');
        }
    }

    // output message
    if (!$params['silent']) {
        if ($result) {
            LogUtil::registerStatus(__('Done! Publication deleted.', $dom));
        } else {
            LogUtil::registerError(__('Error! Failed to delete the publication.', $dom));
        }
    }

    // returns the operation result
    return $result;
}
