<?php
/**
 * PageMaster
 *
 * @copyright (c) 2008, PageMaster Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package     Zikula_3rd_party_Modules
 * @subpackage  pagemaster
 */

// Common items
define('_PAGEMASTER_PUBLICATION', 'Publication');

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
define('_PAGEMASTER_GENERIC_EDITPUB', 'This is a generic Template. Your can create individual Templates in the the directory  \'/pntemplates/pnForm/pubedit_{STEPNAME}_{$tid}.html\'.');
define('_PAGEMASTER_GENERIC_VIEWPUB', 'This is a generic Template. Your can create individual Templates in the the directory  \'/pntemplates/pubdata/viewpub_{$tid}.html\'.');
define('_PAGEMASTER_NOTUSERDEFINED', 'This is a generic Template. Your can create individual Templates in the the directory  \'/pntemplates/pubdata/publist_{$tid}.html\'. Take publist_template.htm as a template.');

// Error and warnings
define('_NOT_AUTHORIZED',	'No permission');
define('_PAGEMASTER_LIVEPIPE_NOTFOUND', 'Javascript livepipe package was not found or it\'s incomplete. It\'s required for the plugin configuration modalbox. Please <a href="http://code.zikula.org/pagemaster/downloads">download it</a> and copy into your site.');
define('_PAGEMASTER_NOPUBLICATIONSFOUND', 'No publications found');
define('_PAGEMASTER_MISSINGARG', 'Missing argument [%arg%]');
define('_PAGEMASTER_TEMPLATENOTFOUND', 'Template [%tpl%] not found');
define('_PAGEMASTER_TOOMANYPUBS', 'Too many pubs found');
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
define('_PAGEMASTER_PLUGIN_MULTILIST', 'MultiList');
define('_PAGEMASTER_PLUGIN_PUBLICATION', 'Publication');
define('_PAGEMASTER_PLUGIN_STRING', 'String');
define('_PAGEMASTER_PLUGIN_TEXT', 'Text');
define('_PAGEMASTER_PLUGIN_UPLOAD', 'Any Upload');
define('_PAGEMASTER_PLUGIN_URL', 'Url');

// Plugins defines
define('_PAGEMASTER_PUBFILTER', 'Filter');
define('_PAGEMASTER_PUBJOIN', 'Join');
define('_PAGEMASTER_PUBJOINFIELDS', 'Join fields (fieldname:alias,fieldname:alias..)');
define('_PAGEMASTER_USEDATETIME', 'Use datetime');
define('_PAGEMASTER_USESCRIBITE', 'Use scribite!');
