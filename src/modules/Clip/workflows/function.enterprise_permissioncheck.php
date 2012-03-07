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
 * Permission check for workflow schema 'enterprise'.
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
function Clip_workflow_enterprise_permissioncheck($pub, $permLevel, $currentUser, $actionId)
{
    $pubtype = Clip_Util::getPubType($pub->core_tid);

    if ($pub->exists()) {
        // check existing publication author and granular permission access check
        if ($pubtype->enableeditown == 1 && $pub->core_author == $currentUser) {
            // FIXME allow this only on update operations on offline publications, and notify (actionId standard)
            return true;
        }
    }

    return Clip_Access::toPub($pubtype, $pub, null, 'exec', null, $permLevel, $currentUser, $actionId);
}

function Clip_workflow_enterprise_gettextstrings()
{
    no__('Are you sure you want to delete this publication?');

    return array(
        'title' => no__('Enterprise'),
        'description' => no__("This is a three staged workflow with stages for untrusted submissions, moderator's acceptance, and approval control by a editor; approved publications are handled by authors staff."),

        // state titles
        'states' => array(
            no__('Waiting') => no__('Content has been submitted and is waiting for acceptance'),
            no__('Accepted') => no__('Content has been accepted and is waiting for approval'),
            no__('Approved') => no__('Content has been approved is available online')
        ),

        // action titles and descriptions for each state
        'actions' => array(
            'initial' => array(
                no__('Submit and Approve') => no__('Submit a publication and approve immediately'),
                no__('Submit and Accept') => no__('Submit a publication and accept immediately'),
                no__('Submit') => no__('Submit a publication for acceptance by a moderator')
            ),
            'waiting' => array(
                no__('Update and Approve') => no__('Update the content and approve for immediate publishing'),
                no__('Approve') => no__('Approve the publication for immediate publishing'),
                no__('Accept') => no__('Accept the publication for editors approval'),
                no__('Update') => no__('Update the content of the publication'),
                no__('Trash') => no__('Move the publication to the recycle bin'),
                no__('Recover') => no__('Recover the publication from the recycle bin'),
                no__('Reject') => no__('Reject and delete the submitted content permanently')
            ),
            'accepted' => array(
                no__('Update and Approve') => no__('Approve the publication for immediate publishing'),
                no__('Update') => no__('Update the content of the publication'),
                no__('Trash') => no__('Move the publication to the recycle bin'),
                no__('Recover') => no__('Recover the publication from the recycle bin'),
                no__('Delete') => no__('Delete the publication permanently')
            ),
            'approved' => array(
                no__('Update') => no__('Update the content of the publication'),
                no__('Disapprove') => no__('Disapprove this publication'),
                no__('Publish') => no__('Make the publication available'),
                no__('Unpublish') => no__('Hide the publication'),
                no__('Trash') => no__('Move the publication to the recycle bin'),
                no__('Recover') => no__('Recover the publication from the recycle bin'),
                no__('Delete') => no__('Delete the publication permanently')
            )
        )
    );
}
