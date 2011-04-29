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
 * moveToDepot operation
 *
 * @param  array  $pub               publication to archive
 * @param  bool   $params['silent']  (optional) hide or display a status/error message, default: false
 * @return array  publication id as index with boolean value: true if success, false otherwise
 */
function Clip_operation_moveToDepot($pub, $params)
{
    $dom = ZLanguage::getModuleDomain('Clip');

    // process the available parameters
    $silent = isset($params['silent']) ? (bool)$params['silent'] : false;

    // set the corresponding publication values
    $pub['core_indepot'] = 1;
    $pub['core_online']  = 0;

    $result = false;

    if ($pub->isValid()) {
        $pub->trySave();
        $result = true;

        // TODO let know hooks that the publication was updated
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
