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
 *  TO increment the counter of the current $pubdata.
 *  <samp>{clip_hitcount}</samp>
 *
 * @param array       $params All parameters passed to this plugin from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return mixed False on failure, void otherwise.
 */
function smarty_function_clip_hitcount($params, Zikula_View &$view)
{
    $pubdata = $view->getTplVar('pubdata');

    $tid = isset($params['tid']) && $params['tid'] ? $params['tid'] : $pubdata['core_tid'];
    $pid = isset($params['pid']) && $params['pid'] ? $params['pid'] : $pubdata['core_pid'];

    Doctrine_Core::getTable('ClipModels_Pubdata'.$tid)->incrementFieldBy('core_hitcount', $pid, 'core_pid');
}
