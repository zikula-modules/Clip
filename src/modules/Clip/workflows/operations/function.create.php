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
 * create operation.
 *
 * @param object $pub              Publication object to create.
 * @param bool   $params['online'] Online value for the new publication (optional) (default: 0).
 * @param bool   $params['silent'] Hide or display a status/error message (optional) (default: false).
 *
 * @return bool|array False on failure or Publication core_uniqueid as index with true as value.
 */
function Clip_operation_create(&$pub, $params)
{
    $dom = ZLanguage::getModuleDomain('Clip');

    // parameters processing
    $pub['core_online'] = isset($params['online']) ? (int)(bool)$params['online'] : 0;
    $silent             = isset($params['silent']) ? (bool)$params['silent'] : false;

    // utility vars
    $tbl = Doctrine_Core::getTable('ClipModels_Pubdata'.$pub['core_tid']);

    // initializes the result flag
    $result = false;

    // validate or find a new pid
    if (isset($pub['core_pid']) && !empty($pub['core_pid'])) {
        if (count($tbl->findBy('core_pid', $pub['core_pid']))) {
            return LogUtil::registerError(__('Error! The fixed publication id already exists on the database. Please contact the administrator.', $dom));
        }
    } else {
        $pub['core_pid'] = $tbl->selectFieldFunction('core_pid', 'MAX') + 1;
    }

    // assign the author
    $pub['core_author'] = (int)UserUtil::getVar('uid');

    // fills the publish date automatically
    if (empty($pub['core_publishdate'])) {
        $pub['core_publishdate'] = DateUtil::getDatetime();
    }

    // save the object
    if ($pub->isValid()) {
        $pub->trySave();
        $pub->clipValues();
        $result = array($pub['core_uniqueid'] => true);

        // TODO HOOKS let know that a publication was created
    }

    // output message
    if (!$silent) {
        if ($result) {
            if ($pub['core_online']) {
                LogUtil::registerStatus(__('Done! Publication created.', $dom));
            } else {
                // setup a redirect to the pending template
                $result['goto'] = ModUtil::url('Clip', 'user', 'main',
                                               array('tid' => $pub['core_tid'],
                                                     'template' => 'pending'));
            }
        } else {
            LogUtil::registerError(__('Error! Failed to create the publication.', $dom));
        }
    }

    // returns the operation result
    return $result;
}
