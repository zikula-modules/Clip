<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage View_Plugins
 */

/**
 * Block to perform a Clip's permission check.
 *
 * Available parameters:
 *  - gid     (integer) Grouptype ID.
 *  - tid     (mixed)   Publication type instance or ID.
 *  - pub     (object)  Publication instance.
 *  - pid     (integer) Publication ID.
 *  - id      (integer) Publication revision ID.
 *  - context (string)  Context to evaluate.
 *  - tplid   (string)  Template id to evaluate.
 *  - permlvl (integer) Required permission level for the check.
 *
 * Examples:
 *
 *  For Clip access check:
 *  <samp>{clip_accessblock permlvl=ACCESS_ADMIN} your code {/clip_accessblock}</samp>
 *
 *  For Grouptype access check:
 *  <samp>{clip_accessblock gid=$gid} your code {/clip_accessblock}</samp>
 *
 *  For Pubtype access check:
 *  <samp>{clip_accessblock tid=$pubtype.tid} your code {/clip_accessblock}</samp>
 *
 *  For Publication edit access check:
 *  <samp>{clip_accessblock pub=$pubdata context='edit'} your code {/clip_accessblock}</samp>
 *
 * @param array       $params All parameters passed to this plugin from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return boolean
 */
function smarty_block_clip_accessblock($params, $content, Zikula_View $view)
{
    if (is_null($content)) {
        return;
    }

    $params['pid'] = isset($params['pid']) ? $params['pid'] : null;
    $params['id']  = isset($params['id']) ? $params['id'] : null;

    if (isset($params['pub'])) {
        $params['tid'] = $params['pub']['core_tid'];
        $params['pid'] = $params['pub'];
        $params['id']  = $params['pub']['id'];
        unset($params['pub']);
    }

    $context = isset($params['context']) ? $params['context'] : null;
    $tplid   = isset($params['tplid']) ? $params['tplid'] : '';
    $permlvl = isset($params['permlvl']) ? constant($params['permlvl']) : null;

    $result  = false;

    // check the parameters and figure out the method to use
    if (isset($params['gid'])) {
        // grouptype check
        $result = Clip_Access::toGrouptype($params['gid']);

    } elseif (isset($params['tid'])) {
        if (!isset($params['pid']) && !isset($params['id'])) {
            // pubtype check
            $result = Clip_Access::toPubtype($params['tid'], $context, $tplid);
        } else {
            // pub check
            $result = Clip_Access::toPub($params['tid'], $params['pid'], $params['id'], $context, $tplid, $permlvl);
        }
    } else {
        // module check
        $result = Clip_Access::toClip($permlvl);
    }

    if (!$result) {
        return;
    }

    return $content;
}
