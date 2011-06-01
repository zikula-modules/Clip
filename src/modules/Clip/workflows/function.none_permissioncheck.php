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
 * Permission check for workflow schema 'none'
 *
 * @param array $obj
 * @param int $permLevel
 * @param int $currentUser
 * @param string $actionId
 * @return bool
 */
function Clip_workflow_none_permissioncheck($obj, $permLevel, $currentUser, $actionId)
{
    $pubtype = Clip_Util::getPubType($obj['core_tid']);

    if ($obj->exists()) {
        // check existing publication author and granular permission access check
        if ($pubtype['enableeditown'] == 1 && $obj['core_author'] == $currentUser) {
            // FIXME allow this only on update operations (actionId standard)
            return true;
        }
    }

    return Clip_Access::toPub($obj['core_tid'], $obj, null, $permLevel, $currentUser);
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
