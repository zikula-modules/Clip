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
 * Plugin to include a modfunc without interrupt the current Clip template.
 *
 * Available parameters:
 *   - modname:  The well-known name of a module to execute a function from (default: Clip)
 *   - type:     The type of function to execute; currently one of 'user', 'editor' or 'admin' (default: user)
 *   - func:     The name of the module function to execute (required)
 *   - all remaining parameters are passed to the module function
 *
 * Examples:
 *
 *  <samp>{clip_func func='list' tid=$tid filter="relation:eq:`$id`"}</samp>
 *  <samp>{clip_func func='display' pub=$pubdata}</samp>
 *
 * @param array       $params All parameters passed to this plugin from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return mixed The results of the module function.
 */
function smarty_function_clip_func($params, Zikula_View &$view)
{
    if (!isset($params['func'])) {
        $view->trigger_error($view->__f('Error! in %1$s: the %2$s parameter must be specified.', array('clip_func', 'func')));
        return false;
    }

    $view->load_filter('output', 'clip_func');

    $params['modname'] = isset($params['modname']) ? $params['modname'] : 'Clip';
    $params['type']    = isset($params['type']) ? $params['type'] : 'user';

    if ($params['modname'] == 'Clip') {
        $pubtype = $view->getTplVar('pubtype');

        // setup the tid if not set
        if (!isset($params['tid'])) {
            $params['tid'] = $pubtype->tid;
        }

        // get the publication parameters if passed
        if (isset($params['pub'])) {
            $params['tid'] = $params['pub']['core_tid'];
            if ($params['func'] == 'display' || $params['func'] == 'edit') {
                $params['pid'] = $params['pub']['core_pid'];
                $params['id'] = $params['pub']['id'];
            }
            unset($params['pub']);
        }

        // be sure to define a default filter for list calls
        if ($params['func'] == 'list') {
            $defaultfilter    = $pubtype->defaultfilter ? $pubtype->defaultfilter : '()';
            $params['filter'] = isset($params['filter']) && !empty($params['filter']) ? $params['filter'] : $defaultfilter;
        }
    }

    // serialize the parameters to let the output filter to work later
    return 'CLIPFUNC:'.serialize($params);
}
