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
 * Plugin to include a pubfield filter inside a filter form.
 *
 * Available parameters:
 *   - id: Name of the pubfield to include.
 *
 * Example:
 *
 *  <samp>{clip_filter_plugin id='core_title'}</samp>
 *
 * @param array       $params All parameters passed to this plugin from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return string Filter plugin output.
 */
function smarty_function_clip_filter_plugin($params, Zikula_View &$view)
{
    // only works inside list templates
    if ($view->getTplVar('func') != 'list') {
        return;
    }

    // validate the parameters
    if ((!isset($params['id']) || !$params['id']) && (!isset($params['for']) || !$params['for'])) {
        $view->trigger_error($view->__f('Error! Missing argument [%s].', 'id | for'));
    }

    if (!isset($params['p']) || !$params['p']) {
        $view->trigger_error($view->__f('Error! Missing argument [%s].', 'p (plugin)'));
    }

    // resolves the plugin class to use
    $className = "Clip_Filter_Plugin_{$params['p']}";
    unset($params['p']);

    // get the plugin output
    $output = '';

    if (class_exists($className)) {
        $filter = $view->get_registered_object('clip_filter');

        $params['field'] = isset($params['field']) ? $params['field'] : $params['id'];
        $params['id']    = $filter->getFieldID($params['id']);

        $plugin = new $className($params, $filter);

        $filter->addPlugin($plugin);

        $output = $plugin->render($view);
    }

    return $output;
}
