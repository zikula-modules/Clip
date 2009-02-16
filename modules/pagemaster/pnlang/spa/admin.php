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
define('_PAGEMASTER_PUBTYPES', 'Publication types');
define('_PAGEMASTER_PUBTYPE_EDITFIELDS', 'Edit Publication fields');
define('_PAGEMASTER_PUBTYPE_NEWARTICLE', 'New Publication');
define('_PAGEMASTER_PUBTYPE_PUBLIST', 'Show Publication list');
define('_PAGEMASTER_PUBTYPE_PUBLISTADMIN', 'Show PubList Admin');
define('_PAGEMASTER_PUBTYPE_SHOWINPUTCODE', 'Show Form Code');
define('_PAGEMASTER_PUBTYPE_SHOWOUTPUTCODEFULL', 'Show PubView Code');
define('_PAGEMASTER_PUBTYPE_SHOWOUTPUTCODELIST', 'Show PubList Code');
define('_PAGEMASTER_SHOWCODE', 'Show Code');

// admin suboptions
define('_PAGEMASTER_EDIT_FIELDS', 'Publication fields');
define('_PAGEMASTER_LIST', 'Publications list');
define('_PAGEMASTER_PUBTYPE_FORM', 'Pubtype form');

// screen titles
define('_PAGEMASTER_ADDPUBFIELDS', 'Add Publication fields');
define('_PAGEMASTER_CREATEEDIT_TID', 'Create / Edit Publication type');
define('_PAGEMASTER_CREATEPUBFIELDS', 'Manage Publication fields');
define('_PAGEMASTER_CREATEPUBTYPE', 'Create a new Publication type');
define('_PAGEMASTER_EXISTINGPUBFIELDS', 'Existing Publication fields');
define('_PAGEMASTER_MANAGE_TITLE', 'Manage Publication types');
define('_PAGEMASTER_NEWPUBFIELD', 'New publication field');

// pubfields
define('_PAGEMASTER_PUBFIELD_NAME', 'Name');
define('_PAGEMASTER_PUBFIELD_NAME_HELP', 'Name of this field (is used e.g. an the template Variables)');
define('_PAGEMASTER_PUBFIELD_TITLE', 'Title');
define('_PAGEMASTER_PUBFIELD_TITLE_HELP', 'Title (is shown e.g. in the automaticaly generated templates) and can be a language define');
define('_PAGEMASTER_PUBFIELD_DESCRIPTION', 'Description');
define('_PAGEMASTER_PUBFIELD_DESCRIPTION_HELP', 'Description of this field (used as tooltip on the input form)');
define('_PAGEMASTER_PUBFIELD_FIELDPLUGIN', 'Fieldtype (Plugin)');
define('_PAGEMASTER_PUBFIELD_FIELDPLUGIN_HELP', 'Which kind of fieldtype is used. (can be extended by plugins). Detailed informations about the individual plugins can be found in the documentation.');
define('_PAGEMASTER_PUBFIELD_ISTITLE', 'Title field');
define('_PAGEMASTER_PUBFIELD_ISTITLE_HELP', 'The content of this field will be shown as the title');
define('_PAGEMASTER_PUBFIELD_ISPAGEABLE', 'Pageable');
define('_PAGEMASTER_PUBFIELD_ISPAGEABLE_HELP', 'Pageable');
define('_PAGEMASTER_PUBFIELD_ISMANDATORY', 'Mandatory');
define('_PAGEMASTER_PUBFIELD_ISMANDATORY_HELP', 'This field has to be completely filled out');
define('_PAGEMASTER_PUBFIELD_ISSEARCHABLE', 'Searchable');
define('_PAGEMASTER_PUBFIELD_ISSEARCHABLE_HELP', 'The content of this field can be searched');
define('_PAGEMASTER_PUBFIELD_MAXLENGTH', 'Max. Lenght');
define('_PAGEMASTER_PUBFIELD_MAXLENGTH_HELP', 'The maximum lenght for the content of this field');
define('_PAGEMASTER_PUBFIELD_LINENO', 'Line No.');
define('_PAGEMASTER_PUBFIELD_LINENO_HELP', 'No. of this field');

// pubtype fields
define('_PAGEMASTER_TITLE', 'Title');
define('_PAGEMASTER_TITLE_HELP', 'Title of the publication type (can be a language define)');
define('_PAGEMASTER_DESCRIPTION', 'Description');
define('_PAGEMASTER_DESCRIPTION_HELP', 'Description of the publication type (can be a language define)');
define('_PAGEMASTER_LISTCOUNT', 'Items per page');
define('_PAGEMASTER_LISTCOUNT_HELP', 'After how many publications the list will be paged. 0 for no paging');
define('_PAGEMASTER_SORTFIELD', 'Sort field');
define('_PAGEMASTER_SORTFIELD_HELP', 'Field for sorting');
define('_PAGEMASTER_SORTDESC', 'Sort descending');
define('_PAGEMASTER_SORTDESC_HELP', '');
define('_PAGEMASTER_DEFAULTFILTER', 'Default filter');
define('_PAGEMASTER_DEFAULTFILTER_HELP', 'The filter which is used by default');
define('_PAGEMASTER_WORKFLOW', 'Workflow');
define('_PAGEMASTER_WORKFLOW_HELP', 'You can choose a special workflow for the publications');
define('_PAGEMASTER_ENABLEREVISION', 'Revision');
define('_PAGEMASTER_ENABLEREVISION_HELP', 'Revisioning');
define('_PAGEMASTER_FILENAME', 'Output template name');
define('_PAGEMASTER_FILENAME_HELP', 'Name of the output template');
define('_PAGEMASTER_FORMNAME', 'Input template name');
define('_PAGEMASTER_FORMNAME_HELP', 'Name of the template for the formular');
define('_PAGEMASTER_EDITOWN', 'Edit own');
define('_PAGEMASTER_EDITOWN_HELP', 'Allow editing of own publications');
define('_PAGEMASTER_CACHESECONDS', 'Caching time');
define('_PAGEMASTER_CACHESECONDS_HELP', 'How long should the publications be cached. Empty for no cache.');
define('_PAGEMASTER_MANDATORY', 'This field is mandatory');
define('_PAGEMASTER_UPDATETABLEDEF', 'Update DB tables');

// modify config
define('_PAGEMASTER_IMPORTFROMPAGESETTER', 'Import Pagesetter Publications');
define('_PAGEMASTER_UPLOADPATH', 'Upload path');
define('_PAGEMASTER_UPLOADPATH_HELP', 'Path where uploaded files will be stored, relative to the site root (%siteroot%)');
define('_PAGEMASTER_UPLOADPATH_WRITEABLE', 'The given path is writeable');
define('_PAGEMASTER_UPLOADPATH_NONWRITEABLE', 'The given path is not writeable');
define('_PAGEMASTER_UPLOADPATH_NOTDIRECTORY', 'The given path is not a directory');
define('_PAGEMASTER_UPLOADPATH_NOTEXISTS', 'The given path doesn\'t exists');

// messages
define('_PAGEMASTER_ATLEASTONE', 'There has to be at least one publication, to generate the template code.');
define('_PAGEMASTER_CREATEATEMPLATENAMED', 'Create a template named <b>%tpl%</b> and store it in the the directory <b>/config/templates/pagemaster/%pmsubfolder%/</b> or within your theme in the <b>/templates/modules/pagemaster/%pmsubfolder%/</b> subfolder.');
define('_PAGEMASTER_NAMEUNIQUE', 'Name has to be unique');
define('_PAGEMASTER_NEEDTOUPDATEDB', 'When Publication fields are changed or added you need to update the DB Tables');
define('_PAGEMASTER_PUBTYPE_EXISTING', 'Here the list of the existing Publication types. If you don\'t have one yet, go to the <a href="'.pnModURL('pagemaster', 'admin', 'create_tid').'">New Publication Type form</a> and create one; after that, you\'ll be able to add the fields for the publications within that type, and once you have the fields you want, <a href="'.pnModURL('pagemaster', 'admin', 'create_tid').'">Update the DB</a>. After create/update the table of your pubtype, you can add publications and customize the templates for your pubtype starting of the code that pagemaster generate for you. Enjoy!');
define('_PAGEMASTER_TID_MUSTCREATETABLE', 'The table of this publication type seems not to exist. Please, update the DB Tables at the bottom of this form.');
define('_PAGEMASTER_VARNOTSET', '%var% no set');
