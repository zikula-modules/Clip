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
 *  - name   (string) Name of the .
 *  - assign (string) Variable name to assign the result to.
 *  - append (string) Variable name to append the result to.
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
 *  <samp>{clip_access tid=$pubtype.tid}</samp>
 *
 *  For Publication edit access check:
 *  <samp>{clip_access pub=$pubdata context='edit'}</samp>
 *
 * @param array       $params All parameters passed to this plugin from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return boolean
 */
function smarty_block_clip_capture($params, $content, Zikula_View $view)
{
    if (is_null($content)) {
        ob_start();
        return;
    }

    $assign = isset($params['assign']) ? $params['assign'] : null;
    $append = isset($params['append']) ? $params['append'] : null;

    if ($assign) {
        $view->assign($assign, ob_get_contents());
    } else if ($append) {
        $view->append($append, ob_get_contents());
    }

    ob_end_clean();
}