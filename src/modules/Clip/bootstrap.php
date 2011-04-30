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

// handler to decorate the some outputs
EventUtil::getManager()->attach('module_dispatch.postexecute', array('Clip_EventHandler', 'decorateOutput'));

// load models check
$modinfo = ModUtil::getInfoFromName('Clip');

if ($modinfo['state'] == ModUtil::STATE_ACTIVE) {
    Clip_Generator::loadModelClasses();
}
