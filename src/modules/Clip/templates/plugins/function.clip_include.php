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
 * Plugin to include a pubtype specific or a common Clip template.
 *
 * clip_include will use the common templates always, if the requested one is a main|list|display template.
 *
 * Available parameters:
 *  - file    (string)  Template filename to render.
 *  - dir     (string)  Folder name of the pubtype (defaults to the current pubtype).
 *  - assign  (string)  Optional variable name to assign the result to.
 *
 * Example:
 *
 *  <samp>{clip_include file='navbar_options.tpl' dir=$clipdirs.anotherpubtype}</samp>
 *
 * @param array       $params All parameters passed to this plugin from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return void
 */
function smarty_function_clip_include($params, Zikula_View &$view)
{
    if (!isset($params['file']) || !$params['file']) {
        $view->trigger_error($view->__f('Error! in %1$s: the %2$s parameter must be specified.', array('clip_include', 'file')));
        return false;
    }

    $pubtype  = $view->getTplVar('pubtype');

    $file   = $params['file'];
    $dir    = isset($params['dir']) ? $params['dir'] : $pubtype->folder;
    $assign = isset($params['assign']) ? $params['assign'] : null;
    unset($params['file'], $params['dir'], $params['assign']);

    // check if the file is inside the pubtype's folder or just use the generic name passed
    if ($view->template_exists("$dir/$file")) {
        $file = "$dir/$file";

    // check if need to search a common main, list or display template
    } else if (preg_match('/^(main|list|display)_/', $file)) {
        $file = "common_$file";
    }

    // backup the current tpl vars
    $tpl_vars = $view->_tpl_vars;

    // include the passed parameters into the existing clipvalues
    $view->_tpl_vars['clipvalues'] = array_merge((array)$view->getTplVar('clipvalues'), (array)$params);

    if ($assign) {
        ob_start();
    }

    // compile and include the template
    $view->_smarty_include(
        array(
            'smarty_include_tpl_file' => $file,
            'smarty_include_vars'     => (array)$params
        )
    );

    // restore the original tpl vars
    $view->_tpl_vars = $tpl_vars;
    unset($tpl_vars);

    if ($assign) {
        $view->assign($assign, ob_get_contents());
        ob_end_clean();
    }
}
