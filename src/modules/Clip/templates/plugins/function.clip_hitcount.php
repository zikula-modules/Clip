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
 * Increase Hit Counter.
 *
 * This logic is implemented in a plugin to let the user decide if he wants to use it or not
 * Hitcount breaks mysql table cache.
 *
 * @param $args['tid'] tid.
 * @param $args['pid'] pid.
 */
function smarty_function_clip_hitcount($params, $view)
{
    $dom = ZLanguage::getModuleDomain('Clip');

    $tid = (int)$params['tid'];
    $pid = (int)$params['pid'];

    if (!$tid) {
        return LogUtil::registerError(__f('Error! Missing argument [%s].', 'tid', $dom));
    }

    if (!$pid) {
        return LogUtil::registerError(__f('Error! Missing argument [%s].', 'pid', $dom));
    }

    DBUtil::incrementObjectFieldByID('clip_pubdata'.$tid, 'core_hitcount', $pid, 'core_pid');
}
