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

$modinfo = ModUtil::getInfoFromName('Clip');

if ($modinfo['state'] == ModUtil::STATE_ACTIVE) {
    Clip_Generator::loadDataClasses();
}
