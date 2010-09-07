<?php
/**
 * PageMaster
 *
 * @copyright   (c) PageMaster Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package     Zikula_3rdParty_Modules
 * @subpackage  pagemaster
 */

$modinfo = ModUtil::getInfoFromName('PageMaster');

if ($modinfo['state'] == ModUtil::STATE_ACTIVE) {
    PageMaster_Generator::loadDataClasses();
}
