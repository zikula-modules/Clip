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
 * updateOnlineState operation
 *
 * @param  array  $pub               publication to set online
 * @param  int    $params['online']  (optional) online value for the publication
 * @param  bool   $params['silent']  (optional) hide or display a status/error message, default: false
 * @return array  publication id as index with boolean value: true if success, false otherwise
 */
function PageMaster_operation_updateOnlineState(&$pub, $params)
{
    $dom = ZLanguage::getModuleDomain('PageMaster');

    // process the available parameters
    // set the online parameter, or defaults to offline if it's not set
    $pub['core_online'] = isset($params['online']) ? (int)$params['online'] : 0;
    $silent             = isset($params['silent']) ? (bool)$params['silent'] : false;

    $result = (bool)DBUtil::updateObject($pub, $pub['__WORKFLOW__']['obj_table']);

    if ($result) {
        // let know that the publication was updated
        pnModCallHooks('item', 'update', $pub['tid'].'-'.$pub['core_pid'], array('module' => 'PageMaster'));
    }

    // output message
    if (!$silent) {
        if ($result) {
            if ($pub['core_online'] == 1) {
                LogUtil::registerStatus(__("Done! Publication status set to 'published'.", $dom));
            } else {
                LogUtil::registerStatus(__("Done! Publication status set to 'unpublished'.", $dom));
            }
        } else {
            LogUtil::registerError(__('Error! Failed to update the publication.', $dom));
        }
    }

    // returns the result
    return $result;
}
