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

// Common items
define('_PAGEMASTER_PUBLICATION', 'Publication');
define('_PAGEMASTER_LANGUAGE', 'English');
define('_PAGEMASTER_HITCOUNT', 'Number of Clicks');

// Common publication fields
define('_PAGEMASTER_AUTHOR', 'Author');
define('_PAGEMASTER_CREATIONDATE', 'Creation date');
define('_PAGEMASTER_UPDDATE', 'Update date');
define('_PAGEMASTER_CREATOR', 'Creator');
define('_PAGEMASTER_EXPIREDATE', 'Expire Date');
define('_PAGEMASTER_HISTORY', 'History');
define('_PAGEMASTER_INDEPOT', 'In depot');
define('_PAGEMASTER_ONLINE', 'Online');
define('_PAGEMASTER_PID', 'PID');
define('_PAGEMASTER_PUBLISHDATE', 'Publish Date');
define('_PAGEMASTER_REVISION', 'Revision');
define('_PAGEMASTER_SHOWINLIST', 'Show in List');
define('_PAGEMASTER_UPDATER', 'Updater');

// Generic template messages
define('_PAGEMASTER_GENERIC_EDITPUB', 'This is a generic template. Your can create your customized template (<b>pubedit_{$pubtype_name}_{$stepname}.htm</b> or <b>pubedit_{$pubtype_name}_all.htm</b>) and store it in the directory <b>config/templates/pagemaster/input/</b> or within your theme in the <b>/templates/modules/pagemaster/input/</b> subfolder.');
define('_PAGEMASTER_GENERIC_VIEWPUB', 'This is a generic template. Your can create a customized template (<b>viewpub_{$pubtype_name}.htm</b>) and store it in the the directory <b>/config/templates/pagemaster/input/</b> or within your theme in the <b>/templates/modules/pagemaster/input/</b> subfolder.');
define('_PAGEMASTER_GENERIC_PUBLIST', 'This is a generic template. Your can create a customized template (<b>publist_{$pubtype_name}.htm</b>) and store it in the the directory <b>/config/templates/pagemaster/input/</b> or within your theme in the <b>/templates/modules/pagemaster/input/</b> subfolder. Check generic_publist.htm as an example for this list.');

// Error and warnings
define('_NOT_AUTHORIZED', 'No permission');
define('_PAGEMASTER_LIVEPIPE_NOTFOUND', 'Javascript livepipe package was not found or it\'s incomplete. It\'s required for the plugin configuration modalbox. Please <a href="http://code.zikula.org/pagemaster/downloads">download it</a> and copy into your site.');
define('_PAGEMASTER_NOPUBTYPEDEFINED', 'No publication type defined');
define('_PAGEMASTER_NOPUBFIELDSFOUND', 'No publication fields found');
define('_PAGEMASTER_NOPUBLICATIONSFOUND', 'No publications found');
define('_PAGEMASTER_MISSINGARG', 'Missing argument [%arg%]');
define('_PAGEMASTER_TEMPLATENOTFOUND', 'Template [%tpl%] not found');
define('_PAGEMASTER_TOOMANYPUBS', 'Too many pubs found');
define('_PAGEMASTER_STOREDDATAINVALID', 'Stored data is invalid');
define('_PAGEMASTER_WORKFLOW_ACTIONERROR', 'Workflow action error');
define('_PAGEMASTER_WORKFLOW_ACTIONCN', 'commandName has to be a valid workflow action for the currenct state');
define('_PAGEMASTER_WORKFLOW_NOACTIONSFOUND', 'No workflow actions with permission found.');

// Plugin titles
define('_PAGEMASTER_PLUGIN_CHECKBOX', 'Checkbox');
define('_PAGEMASTER_PLUGIN_DATE', 'Date');
define('_PAGEMASTER_PLUGIN_EMAIL', 'Email');
define('_PAGEMASTER_PLUGIN_FLOAT', 'Float Value');
define('_PAGEMASTER_PLUGIN_IMAGE', 'Image Upload');
define('_PAGEMASTER_PLUGIN_INTEGER', 'Integer Value');
define('_PAGEMASTER_PLUGIN_LIST', 'List');
define('_PAGEMASTER_PLUGIN_MULTICHECK', 'MultiCheckbox List');
define('_PAGEMASTER_PLUGIN_MULTILIST', 'Multiple selector');
define('_PAGEMASTER_PLUGIN_PUBLICATION', 'Publication');
define('_PAGEMASTER_PLUGIN_STRING', 'String');
define('_PAGEMASTER_PLUGIN_TEXT', 'Text');
define('_PAGEMASTER_PLUGIN_UPLOAD', 'File Upload');
define('_PAGEMASTER_PLUGIN_URL', 'Url');

// Plugins defines
define('_PAGEMASTER_DATAADDITEM', 'Add a new item type');
define('_PAGEMASTER_DATAAJAXCALLTO', 'Ajax call');
define('_PAGEMASTER_DATAAPITOUSE', 'API to use');
define('_PAGEMASTER_DATADISPLAY', 'Display name');
define('_PAGEMASTER_DATANAME', 'Type name');
define('_PAGEMASTER_SIZE', 'Size');
define('_PAGEMASTER_INCLUDEEMPTYITEM', 'Include an empty item?');
define('_PAGEMASTER_PUBFILTER', 'Filter');
define('_PAGEMASTER_PUBJOIN', 'Join');
define('_PAGEMASTER_PUBJOINFIELDS', 'Join fields (fieldname:alias,fieldname:alias..)');
define('_PAGEMASTER_PUBORDERBY', 'Orderby field');
define('_PAGEMASTER_SCRIBITETIP', 'Install Scribite! if you want to use it in this text field');
define('_PAGEMASTER_USEDATETIME', 'Use datetime');
define('_PAGEMASTER_USESCRIBITE', 'Use Scribite!');

// zikula search api plugin
define('_PAGEMASTER_SEARCH_TITLE', 'Include the following content types');
