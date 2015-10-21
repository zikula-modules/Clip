<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://github.com/zikula-modules/clip/
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
 *  <samp>{Accessblock permlvl=ACCESS_ADMIN} your code {/Accessblock}</samp>
 *
 *  For Grouptype access check:
 *  <samp>{Accessblock gid=$gid} your code {/Accessblock}</samp>
 *
 *  For Pubtype access check:
 *  (available contexts: access, main, list, edit, editor, submit, admin)
 *  <samp>{Accessblock context='editor'} your code {/Accessblock}</samp>
 *
 *  For Publication edit access check:
 *  (available contexts: access, display, form, edit, exec, execinline)
 *  <samp>{Accessblock pub=$pubdata context='edit'} your code {/Accessblock}</samp>
 *
 * @param array       $params All parameters passed to this plugin from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return boolean
 */
function smarty_block_Accessblock($params, $content, Zikula_View $view)
{
    if (is_null($content)) {
        return;
    }
    if (isset($params['pub'])) {
        $params['tid'] = $params['pub']['core_tid'];
        $params['pid'] = $params['pub'];
        $params['id'] = $params['pub']['id'];
        unset($params['pub']);
    }
    $params['tid'] = isset($params['tid']) ? $params['tid'] : $view->getTplVar('pubtype')->tid;
    $params['pid'] = isset($params['pid']) ? $params['pid'] : null;
    $params['id'] = isset($params['id']) ? $params['id'] : null;
    $context = isset($params['context']) ? $params['context'] : null;
    $tplid = isset($params['tplid']) ? $params['tplid'] : '';
    $permlvl = isset($params['permlvl']) ? constant($params['permlvl']) : null;
    $result = false;
    // check the parameters and figure out the method to use
    if ($permlvl) {
        // module check
        $result = Access::toClip($permlvl);
    } else {
        if (isset($params['gid'])) {
            // grouptype check
            $result = Access::toGrouptype($params['gid']);
        } else {
            if (isset($params['pid']) || isset($params['id'])) {
                // pub check
                $result = Access::toPub($params['tid'], $params['pid'], $params['id'], $context, $tplid, $permlvl);
            } else {
                // pubtype check
                $result = Access::toPubtype($params['tid'], $context, $tplid);
            }
        }
    }
    if (!$result) {
        return;
    }
    return $content;
}
