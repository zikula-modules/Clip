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
 * deletePub operation
 *
 * @param  array  $pub               publication to delete
 * @param  bool   $params['silent']  (optional) hide or display a status/error message, default: false
 * @return array  publication id as index with boolean value: true if success, false otherwise
 */
function Clip_operation_deletePub(&$pub, $params)
{
    $dom = ZLanguage::getModuleDomain('Clip');

    // process the available parameters
    $silent = isset($params['silent']) ? (bool)$params['silent'] : false;

    // process the deletion
    $result = false;
    if (Zikula_Workflow_Util::deleteWorkflow($pub)) {
        $result = true;

        $tbl = Doctrine_Core::getTable('Clip_Model_Pubdata'.$pub['core_tid']);

        // checks if there's any other revision of this publication
        $count = $tbl->selectFieldFunction('1', 'COUNT', array(array('core_pid = ?', $pub['core_pid']))) + 1;

        if ($count == 0) {
            // TODO if no other revisions, let know hooks that the publication was deleted
        }
    }

    // output message
    if (!$silent) {
        if ($result) {
            LogUtil::registerStatus(__('Done! Publication deleted.', $dom));
        } else {
            LogUtil::registerError(__('Error! Failed to delete the publication.', $dom));
        }
    }

    // returns the result
    return $result;
}
