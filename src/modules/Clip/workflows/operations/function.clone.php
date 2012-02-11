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
 * clone operation.
 *
 * @param object $pub              Publication to clone.
 * @param string $params['state']  State for the cloned publication (optional) (default: initial).
 * @param bool   $params['silent'] Hide or display a status/error message (optional) (default: false).
 * @param array  $params           Value(s) to setup in the cloned publication.
 *
 * @return bool|array False on failure or Publication core_uniqueid as index with true as value.
 */
function Clip_operation_clone(&$pub, $params)
{
    $dom = ZLanguage::getModuleDomain('Clip');

    // copies the publication record
    // FIXME consider better the copy of relations
    $copy = $pub->copy(false);
    $copy->clipValues();

    // process the available parameters
    $params['state']  = isset($params['state']) ? $params['state'] : 'initial';
    $params['silent'] = isset($params['silent']) ? (bool)$params['silent'] : false;

    // initializes the result flag
    $result = false;

    // utility vars
    //$tbl = Doctrine_Core::getTable('ClipModels_Pubdata'.$pub['core_tid']);

    // update any other parameter as that exists
    foreach ($params as $key => $val) {
        if (!in_array($key, array('state', 'silent', 'nextstate')) && $copy->contains($key)) {
            $copy[$key] = $val;
        }
    }

    // save the publication
    if ($copy->isValid()) {
        $copy->trySave();

        // event: notify the operation data
        $copy = Clip_Event::notify('data.edit.operation.clone.pre', $copy, $params)->getData();

        // register the new workflow
        $copy->mapValue('__WORKFLOW__', $pub['__WORKFLOW__']);

        $pubtype = Clip_Util::getPubType($pub['core_tid']);

        $workflow = new Clip_Workflow($pubtype, $copy);

        // be sure that the state is valid
        $params['state'] = $workflow->isValidState($params['state']) ? $params['state'] : 'initial';

        if (!$workflow->registerWorkflow($params['state'])) {
            $result = array($pub['core_uniqueid'] => true);

            // hooks: let know that a publication was created
            $copy->notifyHooks('process_edit');

            // event: notify the operation data
            $copy = Clip_Event::notify('data.edit.operation.clone.post', $copy, $params)->getData();

        } else {
            // delete the previously inserted record
            $copy->delete();
        }
    }

    // output message
    if (!$params['silent']) {
        if ($result) {
            LogUtil::registerStatus(__('Done! Publication copied.', $dom));
        } else {
            LogUtil::registerError(__('Error! Failed to copy the publication.', $dom));
        }
    }

    // returns the operation result
    return $result;
}
