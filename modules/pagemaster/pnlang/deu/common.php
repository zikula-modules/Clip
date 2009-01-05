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
define('_PAGEMASTER_LANGUAGE','Deutsch');
define('_PAGEMASTER_HITCOUNT','Aufrufe');

// Common publication fields
define('_PAGEMASTER_AUTHOR', 'Author');
define('_PAGEMASTER_CREATIONDATE', 'Erstelldatum');
define('_PAGEMASTER_UPDDATE', 'Updatedatum');
define('_PAGEMASTER_CREATOR', 'Ersteller');
define('_PAGEMASTER_EXPIREDATE', 'Ablaufdatum');
define('_PAGEMASTER_HISTORY', 'History');
define('_PAGEMASTER_INDEPOT', 'In depot');
define('_PAGEMASTER_ONLINE', 'Online');
define('_PAGEMASTER_PID', 'PID');
define('_PAGEMASTER_PUBLISHDATE', 'Ver�ffentlicht');
define('_PAGEMASTER_REVISION', 'Revision');
define('_PAGEMASTER_SHOWINLIST', 'In Listenansicht anzeigen');
define('_PAGEMASTER_UPDATER', 'Updater');

// Generic template messages
define('_PAGEMASTER_GENERIC_EDITPUB', 'Dies ist ein generisches Template. Sie k�nnen im Verzeichnis \'/pntemplates/input/pubedit_{FORMNAME}_{STEPNAME}.htm\' oder \'/pntemplates/input/pubedit_{TID}_all.htm\' (f�r alle Steps) ein eigenes Template erstellen.');
define('_PAGEMASTER_GENERIC_VIEWPUB', 'Dies ist ein generisches Template. Sie k�nnen im Verzeichnis \'/pntemplates/output/viewpub_{TEMPLATENAME}.htm\' ein eigenes Template erstellen.');
define('_PAGEMASTER_NOTUSERDEFINED', 'Dies ist ein generisches Template. Sie k�nnen im Verzeichnis \'/pntemplates/output/publist_{TEMPLATENAME}.htm\' ein eigenes Template erstellen. Nehmen Sie dazu publist_template.htm als Vorlage.');

// Error and warnings
define('_NOT_AUTHORIZED', 'Zugriff verwehrt');
define('_PAGEMASTER_LIVEPIPE_NOTFOUND', 'Javascript livepipe package was not found or it\'s incomplete. It\'s required for the plugin configuration modalbox. Please <a href="http://code.zikula.org/pagemaster/downloads">download it</a> and copy into your site.');
define('_PAGEMASTER_NOPUBLICATIONSFOUND', 'No publications found');
define('_PAGEMASTER_MISSINGARG', 'Missing argument [%arg%]');
define('_PAGEMASTER_TEMPLATENOTFOUND', 'Template [%tpl%] not found');
define('_PAGEMASTER_TOOMANYPUBS', 'Too many pubs found');
define('_PAGEMASTER_WORKFLOW_ACTIONERROR', 'Workflow action error');
define('_PAGEMASTER_WORKFLOW_ACTIONCN', 'commandName has to be a valid workflow action for the currenct state');
define('_PAGEMASTER_WORKFLOW_NOACTIONSFOUND', 'Es wurden keine Workflowaktionen gefunden, zu welchen eine Berechtigung besteht.');

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
define('_PAGEMASTER_PUBORDERBY', 'Orderby field');
define('_PAGEMASTER_USEDATETIME', 'Use datetime');
define('_PAGEMASTER_USESCRIBITE', 'Use scribite!');


// zikula search api plugin
define('_PAGEMASTER_SEARCH_TITLE', 'Durchsuche folgende Inhalte');
