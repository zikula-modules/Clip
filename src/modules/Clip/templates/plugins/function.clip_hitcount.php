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
 * Hit counter.
 *
 * Presentation layer plugin to increment the reads count when desired.
 * Hitcount breaks mysql table cache.
 *
 * Available parameters:
 *  - tid (integer) Publication type ID.
 *  - pid (integer) Publication id.
 *
 * Example:
 *
 *  <samp>{clip_hitcount tid=$pubtype.tid pid=$pubdata.core_pid}</samp>
 *
 * @param array       $params All parameters passed to this plugin from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return mixed False on failure, void otherwise.
 */
function smarty_function_clip_hitcount($params, Zikula_View &$view)
{
    if (!isset($params['tid']) || !$params['tid']) {
        $view->trigger_error($view->__f('Error! in %1$s: the %2$s parameter must be specified.', array('clip_hitcount', 'tid')));
        return false;
    }

    if (!isset($params['pid']) || !$params['pid']) {
        $view->trigger_error($view->__f('Error! in %1$s: the %2$s parameter must be specified.', array('clip_hitcount', 'pid')));
        return false;
    }

    Doctrine_Core::getTable('ClipModels_Pubdata'.$params['tid'])->incrementFieldBy('core_hitcount', $params['pid'], 'core_pid');
}
