<?php
/**
 * Clip
 *
 * @copyright   (c) Clip Team
 * @link        http://code.zikula.org/clip/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package     Zikula_3rdParty_Modules
 * @subpackage  clip
 */

$modinfo = ModUtil::getInfoFromName('Clip');

if ($modinfo['state'] == ModUtil::STATE_ACTIVE) {
    Clip_Generator::loadDataClasses();
}
