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
 *  - pid     (mixed)   Publication instance or ID.
 *  - context (string)  Context to evaluate.
 *  - permlvl (integer) Required permission level for the check.
 *  - tplid   (string)  Template id to evaluate.
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
 *  <samp>{clip_access tid=$pubtype}</samp>
 *
 *  For Publication edit access check:
 *  <samp>{clip_access tid=$pubtype.tid pid=$pubdata context='edit'}</samp>
 *
 * @param array       $params All parameters passed to this plugin from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return boolean
 */
function smarty_function_clip_access($params, Zikula_View &$view)
{
    $params['pid'] = isset($params['pid']) ? $params['pid'] : null;
    $params['id']  = isset($params['id']) ? $params['id'] : null;

    $context = isset($params['context']) ? $params['context'] : null;
    $permlvl = isset($params['permlvl']) ? constant($params['permlvl']) : null;
    $tplid   = isset($params['tplid']) ? $params['tplid'] : '';
    $assign  = isset($params['assign']) ? $params['assign'] : null;

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

    if ($assign) {
        $view->assign($assign, $result);
    } else {
        return $result;
    }
}
