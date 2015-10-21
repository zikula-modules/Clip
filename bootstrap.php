<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://github.com/zikula-modules/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Bootstrap
 */

// boot Clip if it's active
if (ModUtil::available('Clip')) {
    Matheo\Clip\Util::boot();
}

if (FormUtil::getPassedValue('type') == 'admin') {
    // handler to decorate the some admin outputs
    EventUtil::getManager()->attach('module_dispatch.postexecute', array('Clip_EventHandler_Listeners', 'decorateOutput'));
}
