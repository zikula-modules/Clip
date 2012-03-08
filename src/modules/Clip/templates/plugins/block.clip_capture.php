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
 * Block to capture a template section into a variable.
 *
 * Available parameters:
 *  - assign (string) Variable name to assign the result to.
 *  - append (string) Variable name to append the result to.
 *
 * Example:
 *
 *  Calpure a post-filtered output:
 *  <samp>{clip_capture assign='mylist'}{clip_func func='list' tid=$cliptids.blog template='mini'}{/clip_capture}</samp>
 *
 * @param array       $params All parameters passed to this plugin from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return void
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
