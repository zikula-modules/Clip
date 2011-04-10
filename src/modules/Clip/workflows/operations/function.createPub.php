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
 * createPub operation
 *
 * @param  array  $pub               object to create
 * @param  int    $params['online']  (optional) online value for the object, default: false
 * @param  bool   $params['silent']  (optional) hide or display a status/error message, default: false
 * @return array  publication id as index with boolean value: true if success, false otherwise
 */
function Clip_operation_createPub(&$pub, $params)
{
    $dom = ZLanguage::getModuleDomain('Clip');

    // process the available parameters
    $pub['core_online'] = isset($params['online']) ? (int)$params['online'] : 0;
    $silent             = isset($params['silent']) ? (bool)$params['silent'] : false;

    // initializes the result flag
    $result = false;

    $tbl = Doctrine_Core::getTable('Clip_Model_Pubdata'.$pub['core_tid']);

    // validate or find a new pid
    if (isset($pub['core_pid']) && !empty($pub['core_pid'])) {
        if (count($tbl->findBy('core_pid', $pub['core_pid']))) {
            return LogUtil::registerError(__('Error! The fixed publication id already exists on the database. Please contact the administrator.', $dom));
        }
    } else {
        $pub['core_pid'] = $tbl->selectFieldFunction('core_pid', 'MAX') + 1;
    }

    // assign the author
    $pub['core_author'] = UserUtil::getVar('uid');

    // fills the publish date automatically
    if (empty($pub['core_publishdate'])) {
        $pub['core_publishdate'] = DateUtil::getDatetime();
    }

    // save the object
    if ($pub->isValid()) {
        $pub->save();
        $result = true;

        // TODO let know that a publication was created
    }

    // output message
    if (!$silent) {
        if ($result) {
            if ($pub['core_online']) {
                LogUtil::registerStatus(__('Done! Publication created.', $dom));
            } else {
                // redirect to the simple pending template
                $result = array('goto' => ModUtil::url('Clip', 'user', 'display',
                                                   array('tid' => $pub['core_tid'],
                                                         'pid' => $pub['core_pid'],
                                                         'template' => 'pending')));
            }
        } else {
            LogUtil::registerError(__('Error! Failed to create the publication.', $dom));
        }
    }

    // returns the result
    return $result;
}
