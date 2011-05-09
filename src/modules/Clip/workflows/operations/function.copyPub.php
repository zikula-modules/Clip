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
 * copyPub operation
 *
 * @param  array  $pub                   publication to copy
 * @param  int    $params['copyonline']  (optional) online value for the copy publication
 * @param  int    $params['copystate']   (optional) state for the copied publication, default: initial
 * @param  bool   $params['silent']      (optional) hide or display a status/error message, default: false
 * @return array  publication id as index with boolean value: clone publication if success, false otherwise
 */
function Clip_operation_copyPub(&$pub, $params)
{
    $dom = ZLanguage::getModuleDomain('Clip');

    // copies the publication record
    // FIXME consider better the copy of relations
    $copy = $pub->copy(false);
    $copy->clipProcess();

    // process the available parameters
    if (isset($params['copyonline'])) {
        $copy['core_online'] = $params['copyonline'];
    }
    $copystate = isset($params['copystate']) ? $params['copystate'] : 'initial';
    $silent    = isset($params['silent']) ? (bool)$params['silent'] : false;

    // initializes the result flag
    $result = false;

    $tbl = Doctrine_Core::getTable('Clip_Model_Pubdata'.$pub['core_tid']);

    // finds the higher pid
    $copy['core_pid'] = $tbl->selectFieldFunction('core_pid', 'MAX') + 1;

    // save the publication
    if ($copy->isValid()) {
        $copy->trySave();
        $result = true;

        $copy['__WORKFLOW__']['obj_id'] = $copy['id'];
        unset($copy['__WORKFLOW__']['id']);

        // register the new workflow, return false if failure
        $workflow = new Zikula_Workflow($copy['__WORKFLOW__']['schemaname'], 'Clip');

        if ($workflow->registerWorkflow($copy, $copystate)) {
            // TODO let know hooks that a publication was created

        } else {
            $result = false;

            // delete the previously inserted record
            $copy->delete();
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
    return $result ? $copy : false;
}
