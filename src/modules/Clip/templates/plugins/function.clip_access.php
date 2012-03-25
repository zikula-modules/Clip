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
 * Plugin to perform a Clip's permission check.
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
 *  - assign  (string)  Optional variable name to assign the result to.
 *
 * Examples:
 *
 *  For Clip access check:
 *  <samp>{clip_access permlvl=ACCESS_ADMIN}</samp>
 *
 *  For Grouptype access check:
 *  <samp>{clip_access gid=$gid}</samp>
 *
 *  For Pubtype access check:
 *  (available contexts: access, main, list, edit, editor, submit, admin)
 *  <samp>{clip_access context='editor'}</samp>
 *
 *  For Publication edit access check:
 *  (available contexts: access, display, form, edit, exec, execinline)
 *  <samp>{clip_access pub=$pubdata context='edit'}</samp>
 *
 * @param array       $params All parameters passed to this plugin from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return boolean
 */
function smarty_function_clip_access($params, Zikula_View &$view)
{
    if (isset($params['pub'])) {
        $params['tid'] = $params['pub']['core_tid'];
        $params['pid'] = $params['pub'];
        $params['id']  = $params['pub']['id'];
        unset($params['pub']);
    }

    $params['tid'] = isset($params['tid']) ? $params['tid'] : $view->getTplVar('pubtype')->tid;
    $params['pid'] = isset($params['pid']) ? $params['pid'] : null;
    $params['id']  = isset($params['id'])  ? $params['id']  : null;

    $context = isset($params['context']) ? $params['context'] : null;
    $tplid   = isset($params['tplid']) ? $params['tplid'] : '';
    $permlvl = isset($params['permlvl']) ? constant($params['permlvl']) : null;
    $assign  = isset($params['assign']) ? $params['assign'] : null;

    $result  = false;

    // check the parameters and figure out the method to use
    if ($permlvl) {
        // module check
        $result = Clip_Access::toClip($permlvl);

    } else if (isset($params['gid'])) {
        // grouptype check
        $result = Clip_Access::toGrouptype($params['gid']);

    } else if (isset($params['pid']) || isset($params['id'])) {
        // pub check
        $result = Clip_Access::toPub($params['tid'], $params['pid'], $params['id'], $context, $tplid, $permlvl);

    } else {
        // pubtype check
        $result = Clip_Access::toPubtype($params['tid'], $context, $tplid);
    }

    if ($assign) {
        $view->assign($assign, $result);
    } else {
        return $result;
    }
}
