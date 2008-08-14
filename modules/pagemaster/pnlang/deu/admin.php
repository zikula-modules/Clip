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

define('_PAGEMASTER', 'Pagemaster');
define('_PAGEMASTER_IMPORTFROMPAGESETTER', 'Import von Pagesetter Publikationen');

define('_PAGEMASTER_PUBTYPES', 'Publikationstypen');
define('_PAGEMASTER_PUBTYPE_TITLE', 'Titel');
define('_PAGEMASTER_PUBTYPE_DESCRIPTION', 'Beschreibung');
define('_PAGEMASTER_PUBTYPE_EXISTING', 'Existierende Publikationstypen');
define('_PAGEMASTER_PUBTYPE_NEWARTICLE', 'Artikel erstellen');
define('_PAGEMASTER_PUBTYPE_EDITFIELDS', 'Publikationsfelder editieren');
define('_PAGEMASTER_PUBTYPE_PUBLIST', 'Pub. Liste anzeigen');
define('_PAGEMASTER_PUBTYPE_PUBLISTADMIN', 'Admin Pub. Liste anzeigen');

define('_PAGEMASTER_SHOWCODE', 'Show Code');
define('_PAGEMASTER_PUBTYPE_SHOWINPUTCODE', 'pnForm Code anzeigen');
define('_PAGEMASTER_PUBTYPE_SHOWOUTPUTCODELIST', 'PubList Code anzeigen');
define('_PAGEMASTER_PUBTYPE_SHOWOUTPUTCODEFULL', 'Kompletten Code anzeigen');

define('_PAGEMASTER_UPDATETABLEDEF', 'DB Tabellen updaten');
define('_PAGEMASTER_MANAGE_TITLE', 'Publikationstypen verwalten');
define('_PAGEMASTER_CREATEPUBFIELDS', 'Publikationsfelder verwalten');
define('_PAGEMASTER_ADDPUBFIELDS', 'Publikationsfeld hinzuf�gen');
define('_PAGEMASTER_EXISTINGPUBFIELDS', 'Existierende Publikationsfelder');
define('_PAGEMASTER_CREATEPUBTYPE', 'Neuen Publikationstyp erstellen');
define('_PAGEMASTER_CREATEEDIT_TID', 'Publikationstyp erstellen / editieren');

define('_PAGEMASTER_EDIT_FIELDS', 'Publikationsfelder');
define('_PAGEMASTER_LIST', 'Publikationsliste');

define('_PAGEMASTER_PUBFIELD_NAME', 'Name');
define('_PAGEMASTER_PUBFIELD_NAME_HELP', 'Name des Feldes (wird z.b. in den Template Variablen verwendet)');
define('_PAGEMASTER_PUBFIELD_TITLE', 'Titel');
define('_PAGEMASTER_PUBFIELD_TITLE_HELP', 'Titel des Feldes (wird z.B. in den Automatisch Generieten Tempaltes angezeigt.) ');
define('_PAGEMASTER_PUBFIELD_DESCRIPTION', 'Beschreibung');
define('_PAGEMASTER_PUBFIELD_DESCRIPTION_HELP', 'Beschreibung des Feldes ');
define('_PAGEMASTER_PUBFIELD_FIELDPLUGIN', 'Feldtyp (Plugin)');
define('_PAGEMASTER_PUBFIELD_FIELDPLUGIN_HELP', 'Was f�r ein Feldtyp benutzt wird. (Kann �ber Plugins erweitert werden). Informationen zu den Einzelnen Typen finden sie in der Dokumentation.');
define('_PAGEMASTER_PUBFIELD_ISTITLE', 'Titelfeld');
define('_PAGEMASTER_PUBFIELD_ISTITLE_HELP', 'Der Inhalt wird im Titel verwendet');
define('_PAGEMASTER_PUBFIELD_ISPAGEABLE', 'Pageable');
define('_PAGEMASTER_PUBFIELD_ISPAGEABLE_HELP', 'Pageable');
define('_PAGEMASTER_PUBFIELD_ISMANDATORY', 'Erforderlich');
define('_PAGEMASTER_PUBFIELD_ISMANDATORY_HELP', 'Dieses Feld muss ausgef�llt werden');
define('_PAGEMASTER_PUBFIELD_ISSEARCHABLE', 'Durchsuchbar');
define('_PAGEMASTER_PUBFIELD_ISSEARCHABLE_HELP', 'Dieses Feld kann von der Suchfunktion durchsucht werden');
define('_PAGEMASTER_PUBFIELD_MAXLENGTH', 'Max. L�nge');
define('_PAGEMASTER_PUBFIELD_MAXLENGTH_HELP', 'Wie Lang darf das Feld maximal sein');
define('_PAGEMASTER_PUBFIELD_LINENO', 'Zeilen Nr.');
define('_PAGEMASTER_PUBFIELD_LINENO_HELP', 'Nr. Des Feldes');

define('_PAGEMASTER_PUBFIELD_ACTIONS', 'Aktionen');

define('_PAGEMASTER_UPLOADPATH', 'Uploadpfad');
define('_PAGEMASTER_UPLOADPATH_HELP', 'Pfad in dem Dateien der Uploadfelder hochgeladen werden sollen');
define('_PAGEMASTER_UPLOADPATH_WRITEABLE', 'Der angegebene Pfad ist beschreibbar');
define('_PAGEMASTER_UPLOADPATH_NONWRITEABLE', 'Der angegebene Pfad ist nicht beschreibbar');

define('_PAGEMASTER_NAMEUNIQUE', 'Name muss eindeutig sein');

define('_PAGEMASTER_TITLE', 'Titel');
define('_PAGEMASTER_TITLE_HELP', 'Titel des Publikationstypen');
define('_PAGEMASTER_DESCRIPTION', 'Beschreibung');
define('_PAGEMASTER_DESCRIPTION_HELP', 'Beschreibung des Publikationstypen');
define('_PAGEMASTER_LISTCOUNT', 'List Count');
define('_PAGEMASTER_LISTCOUNT_HELP', 'Nach wievielen Publikationen die Liste in Mehrere Seiten aufgeteilt wird (0 f�r keine aufteilung)');
define('_PAGEMASTER_SORTFIELD', 'Sortieren nach');
define('_PAGEMASTER_SORTFIELD_HELP', 'Feld nachdem sortiert werden soll');
define('_PAGEMASTER_SORTDESC', 'Absteigend sortieren');
define('_PAGEMASTER_SORTDESC_HELP', '');
define('_PAGEMASTER_DEFAULTFILTER', 'Standard Filter');
define('_PAGEMASTER_DEFAULTFILTER_HELP', 'Filter der standardm��ig angewandt wird');
define('_PAGEMASTER_WORKFLOW', 'Workflow');
define('_PAGEMASTER_WORKFLOW_HELP', 'Welcher Arbeitsablauf benutzt werden soll');
define('_PAGEMASTER_ENABLEREVISION', 'Revisionierung');
define('_PAGEMASTER_ENABLEREVISION_HELP', 'Revisionierung');
define('_PAGEMASTER_FILENAME', 'Output Template Name');
define('_PAGEMASTER_FILENAME_HELP', 'Name des Ausgabe-Templates');
define('_PAGEMASTER_FORMNAME', 'Input Template Name');
define('_PAGEMASTER_FORMNAME_HELP', 'Name des Templates f�r das Formular');
define('_PAGEMASTER_EDITOWN', 'Eigene Editieren');
define('_PAGEMASTER_EDITOWN_HELP', 'Eigene Editieren');
define('_PAGEMASTER_CACHESECONDS', 'Cache Zeit ');
define('_PAGEMASTER_CACHESECONDS_HELP', 'Wie lange sollen die Dateien Zwischengespeichert werden. Leer lassen für kein Cache');

define('_PAGEMASTER_MANDATORY', 'Dieses Feld muss ausgef�llt sein');
define('_PAGEMASTER_ATLEASTONE', 'Es muss mindestens eine Publikation vorhanden sein, um den Template Code erzeugen zu k�nnen.');

define('_PAGEMASTER_CREATIONDATE', 'Erstelldatum');
define('_PAGEMASTER_EDITDATE', '�nderungsdatum');
define('_PAGEMASTER_CREATOR', 'Ersteller');
define('_PAGEMASTER_UPDATER', 'Updater');
define('_PAGEMASTER_HISTORY', 'History');

define('_PAGEMASTER_VARNOTSET', '%var% no set');
define('_PAGEMASTER_MISSINGARG', 'Missing argument [%arg%]');
define('_PAGEMASTER_COPYTHECODEIN', 'Code in %l% kopieren');
