<?php
/**
 * Clip
 *
 * @copyright   (c) Clip Team
 * @link        http://code.zikula.org/clip/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @version     $ Id $
 * @package     Zikula_3rdParty_Modules
 * @subpackage  clip
 */

/**
 * Increase Hit Counter
 * This logic is implemented in a plugin to let the user decide if he wants to use it or not
 * Hitcount breaks mysql table cache
 *
 * @author kundi
 * @param $args['tid'] tid
 * @param $args['pid'] pid
 */
function smarty_function_clip_hitcount($params, &$smarty)
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
