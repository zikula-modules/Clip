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

$serviceManager = ServiceUtil::getManager();
$eventManager   = EventUtil::getManager();

$serviceManager->attachService('module.pagemaster.util',
                               new PageMaster_Util($serviceManager));

$eventManager->attach('pagemaster.get_form_plugins',
                      array('PageMaster_EventHandler_Listeners', 'getFormPlugins'));
