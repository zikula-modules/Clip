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
 * Plugin to copy a DOM element contents to the browser's Clipboard.
 *
 * Available parameters:
 *  - id      (string)  ID of the DOM element.
 *  - clippy  (string)  Custom SWF file to use (default: clippy.swf).
 *  - width   (integer) Width of the custom SWF animation (default: 14).
 *  - height  (integer) Height of the custom SWF animation (default: 14).
 *  - bgcolor (hexcode) Background color of the HTML object (default: FFFF).
 *  - wmode   (string)  Window mode of the embeded object (default: opaque).
 *  - scale   (string)  Scale parameter of the embeded object (default: noscale).
 *  - class   (string)  CSS class(es) to use in the SPAN wrapper object (default: clippy_wrap).
 *
 * Examples:
 *
 *  <samp>{clip_copytoclipboard id='myDiv'}</samp>
 *  <samp>{clip_copytoclipboard id='myInputID' class='myClass'}</samp>
 *
 * @param array       $params All parameters passed to this plugin from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return mixed HTML output.
 */
function smarty_function_clip_copytoclipboard($params, Zikula_View &$view)
{
    if (!isset($params['id']) || !$params['id']) {
        $view->trigger_error($view->__f('Error! in %1$s: the %2$s parameter must be specified.', array('clip_copytoclipboard', 'id')));
        return false;
    }

    $clippy = (isset($params['clippy']) && $params['clippy']) ? $params['clippy'] : null;
    if ($clippy) {
        $width  = (isset($params['width']) && $params['width']) ? $params['width'] : '14';
        $height = (isset($params['height']) && $params['height']) ? $params['height'] : '14';
    } else {
        $clippy = 'clippy.swf';
        $width  = '14';
        $height = '14';
    }
    $bgcolor = (isset($params['bgcolor']) && $params['bgcolor']) ? $params['bgcolor'] : 'FFFFFF';
    $wmode   = (isset($params['wmode']) && $params['wmode']) ? $params['wmode'] : 'opaque';
    $scale   = (isset($params['scale']) && $params['scale']) ? $params['scale'] : 'noscale';
    $class   = (isset($params['class']) && $params['class']) ? $params['class'] : 'clippy_wrap';

    $clippypath = $view->get_template_path($clippy);

    if (!$clippypath) {
        $view->trigger_error($view->__f('Error! Clippy.swf not found on this view paths [%s].', $view->getModuleName()));
        return false;
    }

    $clippypath = $view->getTplVar('baseurl') . $clippypath . '/'. $clippy;
    $flashvars  = 'id='.$params['id'].'&amp;copied=&amp;copyto=';

    // build the output
    $output = '<span id="clippy_'.$params['id'].'" class="'.$class.'" title="'.$view->__('Copy to clipboard').'">
                   <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000"
                           id="clippy_'.$params['id'].'_obj"
                           class="clippy"
                           width="'.$width.'"
                           height="'.$height.'" >
                       <param name="movie" value="'.$clippypath.'"/>
                       <param name="allowScriptAccess" value="always" />
                       <param name="quality" value="high" />
                       <param name="scale" value="'.$scale.'">
                       <param name="FlashVars" value="'.$flashvars.'">
                       <param name="bgcolor" value="#'.$bgcolor.'">
                       <param name="wmode" value="'.$wmode.'">
                       <embed src="'.$clippypath.'"
                              width="'.$width.'"
                              height="'.$height.'"
                              name="clippy_'.$params['id'].'_obj"
                              quality="high"
                              allowScriptAccess="always"
                              type="application/x-shockwave-flash"
                              pluginspage="http://www.macromedia.com/go/getflashplayer"
                              FlashVars="'.$flashvars.'"
                              bgcolor="#'.$bgcolor.'"
                              wmode="'.$wmode.'"
                       />
                   </object>
               </span>';

    return $output;
}
