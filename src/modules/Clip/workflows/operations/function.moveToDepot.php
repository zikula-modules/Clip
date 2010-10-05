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
 * moveToDepot operation
 *
 * @param  array  $pub               publication to archive
 * @param  bool   $params['silent']  (optional) hide or display a status/error message, default: false
 * @return array  publication id as index with boolean value: true if success, false otherwise
 */
function PageMaster_operation_moveToDepot($pub, $params)
{
    $dom = ZLanguage::getModuleDomain('PageMaster');

    // process the available parameters
    $silent = isset($params['silent']) ? (bool)$params['silent'] : false;

    // set the corresponding publication values
    $pub['core_indepot'] = 1;
    $pub['core_online']  = 0;

    $result = false;

    if ($pub->isValid()) {
        $pub->save();
        $result = true;

        // let know that the publication was updated
        ModUtil::callHooks('item', 'update', $pub['core_uniqueid'], array('module' => 'PageMaster'));
    }

    // output message
    if (!$silent) {
        if ($result) {
            LogUtil::registerStatus(__('Done! Publication archived.', $dom));
        } else {
            LogUtil::registerError(__('Error! Failed to update publication.', $dom));
        }
    }

    // returns the result
    return $result;
}
