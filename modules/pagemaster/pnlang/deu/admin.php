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

define('_PAGEMASTER', 'Pagemaster');

// main admin options
define('_PAGEMASTER_PUBTYPES', 'Publikationstypen');
define('_PAGEMASTER_PUBTYPE_EDITFIELDS', 'Publikationsfelder editieren');
define('_PAGEMASTER_PUBTYPE_NEWARTICLE', 'Artikel erstellen');
define('_PAGEMASTER_PUBTYPE_PUBLIST', 'Pub. Liste anzeigen');
define('_PAGEMASTER_PUBTYPE_PUBLISTADMIN', 'Admin Pub. Liste anzeigen');
define('_PAGEMASTER_PUBTYPE_SHOWINPUTCODE', 'pnForm Code anzeigen');
define('_PAGEMASTER_PUBTYPE_SHOWOUTPUTCODELIST', 'PubList Code anzeigen');
define('_PAGEMASTER_PUBTYPE_SHOWOUTPUTCODEFULL', 'Kompletten Code anzeigen');
define('_PAGEMASTER_SHOWCODE', 'Show Code');

// admin suboptions
define('_PAGEMASTER_EDIT_FIELDS', 'Publikationsfelder');
define('_PAGEMASTER_LIST', 'Publikationsliste');
define('_PAGEMASTER_PUBTYPE_FORM', 'Pubtype form');

// screen titles
define('_PAGEMASTER_ADDPUBFIELDS', 'Publikationsfeld hinzufügen');
define('_PAGEMASTER_CREATEEDIT_TID', 'Publikationstyp erstellen / editieren');
define('_PAGEMASTER_CREATEPUBFIELDS', 'Publikationsfelder verwalten');
define('_PAGEMASTER_CREATEPUBTYPE', 'neuen Publikationstyp erstellen');
define('_PAGEMASTER_EXISTINGPUBFIELDS', 'existierende Publikationsfelder');
define('_PAGEMASTER_MANAGE_TITLE', 'Publikationstypen verwalten');
define('_PAGEMASTER_NEWPUBFIELD', 'Neues Publikationsfeld');

// pubfields
define('_PAGEMASTER_PUBFIELD_NAME', 'Name');
define('_PAGEMASTER_PUBFIELD_NAME_HELP', 'Name des feldes (wird z.B. in den template-variablen verwendet)');
define('_PAGEMASTER_PUBFIELD_TITLE', 'Titel');
define('_PAGEMASTER_PUBFIELD_TITLE_HELP', 'Titel des feldes (wird z.B. in den automatisch generierten templates angezeigt) can be a language define');
define('_PAGEMASTER_PUBFIELD_DESCRIPTION', 'Beschreibung');
define('_PAGEMASTER_PUBFIELD_DESCRIPTION_HELP', 'Beschreibung des feldes (used as tooltip on the input form)');
define('_PAGEMASTER_PUBFIELD_FIELDPLUGIN', 'Feldtyp (Plugin)');
define('_PAGEMASTER_PUBFIELD_FIELDPLUGIN_HELP', 'Was für ein feldtyp benutzt wird (kann über plugins erweitert werden). Informationen zu den einzelnen typen finden sie in der dokumentation.');
define('_PAGEMASTER_PUBFIELD_ISTITLE', 'Titelfeld');
define('_PAGEMASTER_PUBFIELD_ISTITLE_HELP', 'Der inhalt wird im titel verwendet');
define('_PAGEMASTER_PUBFIELD_ISPAGEABLE', 'Mehrseitig');
define('_PAGEMASTER_PUBFIELD_ISPAGEABLE_HELP', 'Mehrseitige ansicht aktivieren');
define('_PAGEMASTER_PUBFIELD_ISMANDATORY', 'Erforderlich');
define('_PAGEMASTER_PUBFIELD_ISMANDATORY_HELP', 'Dieses feld muss ausgefüllt werden');
define('_PAGEMASTER_PUBFIELD_ISSEARCHABLE', 'Durchsuchbar');
define('_PAGEMASTER_PUBFIELD_ISSEARCHABLE_HELP', 'Dieses feld kann von der suchfunktion durchsucht werden');
define('_PAGEMASTER_PUBFIELD_MAXLENGTH', 'max. Länge');
define('_PAGEMASTER_PUBFIELD_MAXLENGTH_HELP', 'Wie lang darf das feld maximal sein');
define('_PAGEMASTER_PUBFIELD_LINENO', 'Zeilen Nr.');
define('_PAGEMASTER_PUBFIELD_LINENO_HELP', 'Nr. des feldes');

// pubtype fields
define('_PAGEMASTER_TITLE', 'Titel');
define('_PAGEMASTER_TITLE_HELP', 'Titel des publikationstypen (can be a language define)');
define('_PAGEMASTER_DESCRIPTION', 'Beschreibung');
define('_PAGEMASTER_DESCRIPTION_HELP', 'Beschreibung des publikationstypen (can be a language define)');
define('_PAGEMASTER_LISTCOUNT', 'Einträge pro seite');
define('_PAGEMASTER_LISTCOUNT_HELP', 'Nach wievielen publikationen die liste in mehrere seiten aufgeteilt wird (0 für keine aufteilung)');
define('_PAGEMASTER_SORTFIELD', 'Sortieren nach');
define('_PAGEMASTER_SORTFIELD_HELP', 'Feld nachdem sortiert werden soll');
define('_PAGEMASTER_SORTDESC', 'Absteigend sortieren');
define('_PAGEMASTER_SORTDESC_HELP', '');
define('_PAGEMASTER_DEFAULTFILTER', 'Standard filter');
define('_PAGEMASTER_DEFAULTFILTER_HELP', 'Filter der standardmäßig angewandt wird');
define('_PAGEMASTER_WORKFLOW', 'Workflow');
define('_PAGEMASTER_WORKFLOW_HELP', 'Welcher arbeitsablauf benutzt werden soll');
define('_PAGEMASTER_ENABLEREVISION', 'Revisionierung');
define('_PAGEMASTER_ENABLEREVISION_HELP', 'Revisionierung');
define('_PAGEMASTER_FILENAME', 'Output template name');
define('_PAGEMASTER_FILENAME_HELP', 'Name des ausgabe-templates');
define('_PAGEMASTER_FORMNAME', 'Input template name');
define('_PAGEMASTER_FORMNAME_HELP', 'Name des templates für das formular');
define('_PAGEMASTER_EDITOWN', 'Eigene editieren');
define('_PAGEMASTER_EDITOWN_HELP', 'Eigene editieren');
define('_PAGEMASTER_CACHESECONDS', 'Cache zeit ');
define('_PAGEMASTER_CACHESECONDS_HELP', 'Wie lange sollen die dateien zwischengespeichert werden. Leer lassen für kein cache');
define('_PAGEMASTER_MANDATORY', 'Dieses feld muss ausgefüllt sein');
define('_PAGEMASTER_UPDATETABLEDEF', 'DB tabellen aktualisieren');

// modify config
define('_PAGEMASTER_IMPORTFROMPAGESETTER', 'Import von Pagesetter Publikationen');
define('_PAGEMASTER_UPLOADPATH', 'Uploadpfad');
define('_PAGEMASTER_UPLOADPATH_HELP', 'Pfad in dem dateien der uploadfelder hochgeladen werden sollen');
define('_PAGEMASTER_UPLOADPATH_WRITEABLE', 'Der angegebene pfad ist beschreibbar');
define('_PAGEMASTER_UPLOADPATH_NONWRITEABLE', 'Der angegebene pfad ist nicht beschreibbar');
define('_PAGEMASTER_UPLOADPATH_NOTDIRECTORY', 'The given path is not a directory');
define('_PAGEMASTER_UPLOADPATH_NOTEXISTS', 'The given path doesn\'t exists');

// messages
define('_PAGEMASTER_ATLEASTONE', 'Es muss mindestens eine publikation vorhanden sein, um den template-code erzeugen zu können.');
define('_PAGEMASTER_CREATEATEMPLATENAMED', 'Create a template named <b>%tpl%</b> and store it in the the directory <b>/config/templates/pagemaster/%pmsubfolder%/</b> or within your theme in the <b>/templates/modules/pagemaster/%pmsubfolder%/</b> subfolder.');
define('_PAGEMASTER_NAMEUNIQUE', 'Name muss eindeutig sein');
define('_PAGEMASTER_NEEDTOUPDATEDB', 'Nach änderungen an den publikationsfeldern müssen die DB tabellen aktualisiert werden');
define('_PAGEMASTER_PUBTYPE_EXISTING', 'Here the list of the existing Publication types. If you don\'t have one yet, go to the <a href="'.pnModURL('pagemaster', 'admin', 'create_tid').'">New Publication Type form</a> and create one; after that, you\'ll be able to add the fields for the publications within that type, and once you have the fields you want, <a href="'.pnModURL('pagemaster', 'admin', 'create_tid').'">Update the DB</a>. After create/update the table of your pubtype, you can add publications and customize the templates for your pubtype starting of the code that pagemaster generate for you. Enjoy!');
define('_PAGEMASTER_TID_MUSTCREATETABLE', 'The table of this publication type seems not to exist. Please, update the DB Tables at the bottom of this form.');
define('_PAGEMASTER_VARNOTSET', '%var% no set');
