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
 * Returns a url depending on the context.
 *
 * Available parameters:
 *  - type    (string) Target function type (default: current type).
 *  - func    (string) Target function name.
 *  - args    (array)  URL arguments.
 *  - *       Remaining parameters goes as url arguments.
 *
 * Example:
 *
 *  <samp>{clip_url func='pubtypeinfo' tid=$pubtype.tid}</samp>
 *
 * @param array       $params All parameters passed to this plugin from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return mixed Context URL href value.
 */
function smarty_function_clip_url($params, Zikula_View &$view)
{
    $params['modname'] = 'Clip';
    $params['type']    = isset($params['forcetype']) ? $params['forcetype'] : $view->getRequest()->getControllerName();

    // dispatch any non-ajax request with modurl
    if ($params['type'] != 'ajax') {
        $args = (isset($params['args']) && $params['args']) ? $params['args'] : array();
        unset($params['args']);
        $params = array_merge($params, $args);

        include_once('lib/viewplugins/function.modurl.php');
        return smarty_function_modurl($params, $view);
    }

    // process the internal Clip ajax request output
    $type = (isset($params['type']) && $params['type']) ? $params['type'] : 'ajax';
    $func = (isset($params['func']) && $params['func']) ? $params['func'] : 'pubtypeinfo';
    $args = (isset($params['args']) && $params['args']) ? $params['args'] : array();

    unset($params['modname'], $params['type'], $params['func'], $params['fragment'], $params['args'], $params['forcetype']);

    $params = json_encode(array_merge($params, $args));
    $params = str_replace('"', "'", $params);

    return "javascript:Zikula.Clip.Ajax.Request($params, '$func', '$type')";
}
