<?php
$serviceManager = ServiceUtil::getManager();
$eventManager = ServiceUtil::getManager();
$serviceManager->attachService('module.pagemaster.util', new PageMaster_Util($serviceManager));
$eventManager->attach('pagemaster.get_form_plugins', array('PageMaster_EventHandler_Listeners', 'getFormPlugins'));
include_once 'modules/PageMaster/common.php';