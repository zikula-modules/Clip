<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Bootstrap
 */

// boot Clip if it's active
if (ModUtil::available('Clip')) {
    Clip_Util::boot();
}

if (FormUtil::getPassedValue('type') == 'admin') {
    // handler to decorate the some admin outputs
    EventUtil::getManager()->attach('module_dispatch.postexecute', array('Clip_EventHandler_Listeners', 'decorateOutput'));
}
