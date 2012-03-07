<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Workflows
 */

/**
 * Permission check for workflow schema 'none'.
 *
 * @param object $pub         Publication instance.
 * @param int    $permLevel   Permission level.
 * @param int    $currentUser Current User ID.
 * @param string $actionId    Action ID.
 *
 * @todo Take in account $actionId processing?
 *
 * @return bool True if allowed to execute the action, false otherwise.
 */
function Clip_workflow_none_permissioncheck($pub, $permLevel, $currentUser, $actionId)
{
    $pubtype = Clip_Util::getPubType($pub->core_tid);

    if ($pub->exists()) {
        // check existing publication author and granular permission access check
        if ($pubtype->enableeditown == 1 && $pub->core_author == $currentUser) {
            // FIXME allow this only on update operations on offline publications (actionId standard)
            return true;
        }
    }

    return Clip_Access::toPub($pubtype, $pub, null, 'exec', null, $permLevel, $currentUser, $actionId);
}

function Clip_workflow_none_gettextstrings()
{
    return array(
        'title' => no__('None'),
        'description' => no__('This is like a non-existing workflow. Everything is online immediately after creation.'),

        // state titles
        'states' => array(
            no__('Approved') => no__('Content has been approved and is available online')
        ),

        // action titles and descriptions for each state
        'actions' => array(
            'initial' => array(
                no__('Submit') => no__('Submit a publication')
            ),
            'approved' => array(
                no__('Update') => no__('Update the publication content'),
                no__('Trash') => no__('Move the publication to the recycle bin'),
                no__('Recover') => no__('Recover the publication from the recycle bin'),
                no__('Delete') => no__('Delete the publication permanently')
            )
        )
    );
}
