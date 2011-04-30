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
 * @param $args['modname'] module name (unsetted by now)
 * @param $args['type']    type (default: ajax)
 * @param $args['func']    function
 * @param $args['args']    arguments to the function (optional)
 * @param $args            remaining parameters goes as url arguments
 *
 * @return string
 */
function smarty_function_clip_url($params, &$view)
{
    $params['modname'] = 'Clip';
    $params['type']    = $view->getRequest()->getControllerName();

    // dispatch any non-ajax request with modurl
    if ($params['type'] != 'ajax') {
        $args = (isset($params['args']) && $params['args']) ? $params['args'] : array();
        unset($params['args']);
        $params = array_merge($params, $args);

        return smarty_function_modurl($params, $view);
    }

    // process the internal Clip ajax request output
    $type = (isset($params['type']) && $params['type']) ? $params['type'] : 'ajax';
    $func = (isset($params['func']) && $params['func']) ? $params['func'] : 'publist';
    $args = (isset($params['args']) && $params['args']) ? $params['args'] : array();
    unset($params['modname']);
    unset($params['type']);
    unset($params['func']);
    unset($params['args']);

    $params = json_encode(array_merge($params, $args));
    $params = str_replace('"', "'", $params);

    return "javascript:Zikula.Clip.AjaxRequest($params, '$func', '$type')";
}
