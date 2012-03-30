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
 *  - type (string) Target function type (default: current type).
 *  - func (string) Target function name.
 *  - args (array)  URL arguments.
 *  - *    Remaining parameters goes as url arguments.
 *
 * Examples:
 *
 *  <samp>{clip_url func='main'}</samp>
 *
 *  <samp>{clip_url func='list'}</samp>
 *
 *  <samp>{clip_url func='display' pub=$pubdata}</samp>
 *
 *  <samp>{clip_url func='edit' pub=$pubdata}</samp>
 *
 *  <samp>{clip_url func='edit'}</samp>
 *
 * @param array       $params All parameters passed to this plugin from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return mixed Context URL href value.
 */
function smarty_function_clip_url($params, Zikula_View &$view)
{
    if (!isset($params['func']) || !$params['func']) {
        $view->trigger_error($view->__f('Error! in %1$s: the %2$s parameter must be specified.', array('clip_url', 'func')));
        return false;
    }

    if (isset($params['pub']) && !$params['pub'] instanceof Clip_Doctrine_Pubdata && !is_array($params['pub']) && !isset($params['tid'])) {
        $view->trigger_error($view->__f('Error! in %1$s: the %2$s parameter is not valid.', array('clip_url', 'pub | tid [pid]')));
        return false;
    }

    $assign = isset($params['assign']) ? $params['assign'] : null;
    unset($params['assign']);
    
    // discard empty or null values
    foreach ($params as $k => $v) {
        if (is_null($v) || $v === '') {
            unset($params[$k]);
        }
    }

    // set the required parameters
    $params['modname'] = 'Clip';
    $params['type']    = isset($params['type']) ? $params['type'] : $view->getRequest()->getControllerName();
    $params['type']    = $params['type'] ? $params['type'] : 'user';

    if (isset($params['pub'])) {
        $params['tid'] = $params['pub']['core_tid'];
        if ($params['func'] == 'display' || $params['func'] == 'edit') {
            $params['pid'] = $params['pub']['core_pid'];
            if ($params['func'] == 'edit') {
                $params['id'] = $params['pub']['id'];
            }
            $params['urltitle'] = $params['pub']['core_urltitle'];
        }
        unset($params['pub']);
    }

    // setup the tid if not set
    if (!isset($params['tid'])) {
        $pubtype = $view->getTplVar('pubtype');
        $params['tid'] = $pubtype['tid'];
    }

    // dispatch any non-ajax request with modurl
    if ($params['type'] != 'ajax') {
        $args = (isset($params['args']) && $params['args']) ? $params['args'] : array();
        unset($params['args']);
        $params = array_merge($params, $args);

        include_once('lib/viewplugins/function.modurl.php');
        $url = smarty_function_modurl($params, $view);

    } else {
        // process the internal Clip ajax request output
        $type = (isset($params['type']) && $params['type']) ? $params['type'] : 'ajax';
        $func = (isset($params['func']) && $params['func']) ? $params['func'] : 'pubtypeinfo';
        $args = (isset($params['args']) && $params['args']) ? $params['args'] : array();

        unset($params['modname'], $params['type'], $params['func'], $params['fragment'], $params['args']);

        $params = json_encode(array_merge($params, $args));
        $params = str_replace('"', "'", $params);

        $url = DataUtil::formatForDisplay("javascript:Zikula.Clip.Ajax.Request($params, '$func', '$type')");
    }

    if ($assign) {
        $view->assign($assign, $url);
    } else {
        return $url;
    }
}
