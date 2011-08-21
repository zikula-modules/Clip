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

// load models check
$modinfo = ModUtil::getInfoFromName('Clip');

if ($modinfo['state'] == ModUtil::STATE_ACTIVE) {
    Clip_Generator::checkModels();
}

if (FormUtil::getPassedValue('type') == 'admin') {
    // handler to decorate the some admin outputs
    EventUtil::getManager()->attach('module_dispatch.postexecute', array('Clip_EventHandler_Listeners', 'decorateOutput'));
}

// add the dynamic models path
ZLoader::addAutoloader('ClipModels', realpath(StringUtil::left(ModUtil::getVar('Clip', 'modelspath'), -11)));
