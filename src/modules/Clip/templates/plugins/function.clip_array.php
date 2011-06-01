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
 * Plugin to show an array in a human readable way.
 *
 * Available parameters:
 *  - array (array) Array to display.
 *
 * Examples:
 *
 *  <samp>{clip_array array=$data}</samp>
 *  <samp>{clip_array array=$pubdata->toArray()}</samp>
 *
 * @param array       $params All parameters passed to this plugin from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return mixed HTML output.
 */
function smarty_function_clip_array($params, Zikula_View &$view)
{
    if (!isset($params['array'])) {
        $view->trigger_error($view->__f('Error! in %1$s: the %2$s parameter must be specified.', array('clip_array', 'array')));
        return false;
    }

    // removes a useless "\n1" ending
    return substr(DataUtil::formatForDisplayHTML(print_r($params['array'])), 0, -2);
}
