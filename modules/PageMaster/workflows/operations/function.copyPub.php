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
 * copyPub operation
 *
 * @param  array  $pub                   publication to copy
 * @param  int    $params['copyonline']  (optional) online value for the copy publication
 * @param  int    $params['copystate']   (optional) state for the copied publication, default: initial
 * @param  bool   $params['silent']      (optional) hide or display a status/error message, default: false
 * @return array  publication id as index with boolean value: clone publication if success, false otherwise
 */
function PageMaster_operation_copyPub(&$pub, $params)
{
    $dom = ZLanguage::getModuleDomain('PageMaster');

    // process the available parameters
    if (isset($params['copyonline'])) {
        $pub['core_online'] = $params['copyonline'];
    }
    $copystate = isset($params['copystate']) ? $params['copystate'] : 'initial';
    $silent    = isset($params['silent']) ? (bool)$params['silent'] : false;

    // initializes the result flag
    $result = false;

    // finds the higher pid
    $maxpid = DBUtil::selectFieldMax($pub['__WORKFLOW__']['obj_table'], 'core_pid', 'MAX');
    $pub['core_pid'] = $maxpid + 1;

    // save the publication
    unset($pub['id']);
    if (DBUtil::insertObject($pub, $pub['__WORKFLOW__']['obj_table'], 'id')) {
        $result = true;

        $pub['__WORKFLOW__']['obj_id'] = $pub['id'];
        unset($pub['__WORKFLOW__']['id']);

        // register the new workflow, return false if failure
        $workflow = new pnWorkflow($pub['__WORKFLOW__']['schemaname'], 'PageMaster');

        if ($workflow->registerWorkflow($pub, $copystate)) {
            // let know that a publication was created
            pnModCallHooks('item', 'create', $pub['tid'].'-'.$pub['core_pid'], array('module' => 'PageMaster'));

        } else {
            $result = false;

            // delete the previously inserted record
            DBUtil::deleteObjectByID($pub['__WORKFLOW__']['obj_table'], $pub['id'], 'id');
        }
    }

    // output message
    if (!$silent) {
        if ($result) {
            LogUtil::registerStatus(__('Done! Publication copied.', $dom));
        } else {
            LogUtil::registerError(__('Error! Failed to copy the publication.', $dom));
        }
    }

    // returns the cloned publication if success, false otherwise
    return $result ? $pub : false;
}
