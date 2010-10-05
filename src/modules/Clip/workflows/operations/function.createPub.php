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
 * createPub operation
 *
 * @param  array  $pub               object to create
 * @param  int    $params['online']  (optional) online value for the object, default: false
 * @param  bool   $params['silent']  (optional) hide or display a status/error message, default: false
 * @return array  publication id as index with boolean value: true if success, false otherwise
 */
function PageMaster_operation_createPub(&$pub, $params)
{
    $dom = ZLanguage::getModuleDomain('PageMaster');

    // process the available parameters
    $pub['core_online'] = isset($params['online']) ? (int)$params['online'] : 0;
    $silent             = isset($params['silent']) ? (bool)$params['silent'] : false;

    // initializes the result flag
    $result = false;

    // validate or find a new pid
    if (isset($pub['core_pid']) && !empty($pub['core_pid'])) {
        if (DBUtil::selectFieldByID($pub['__WORKFLOW__']['obj_table'], 'id', $pub['core_pid'], 'core_pid')) {
            return LogUtil::registerError(__('Error! The fixed publication id already exists on the database. Please contact the administrator.', $dom));
        }
    } else {
        $maxpid = DBUtil::selectFieldMax($pub['__WORKFLOW__']['obj_table'], 'core_pid', 'MAX');
        $pub['core_pid'] = $maxpid + 1;
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

        // let know that a publication was created
        ModUtil::callHooks('item', 'create', $pub['core_uniqueid'], array('module' => 'PageMaster'));
    }

    // output message
    if (!$silent) {
        if ($result) {
            if ($pub['core_online']) {
                LogUtil::registerStatus(__('Done! Publication created.', $dom));
            } else {
                // redirect to the simple pending template
                $result = array('goto' => ModUtil::url('PageMaster', 'user', 'display',
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
