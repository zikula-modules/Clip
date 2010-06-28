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

function PageMaster_pntables()
{
    $tables = array ();
/*
    // relations table
    $tables['pagemaster_relations'] = DBUtil::getLimitedTablename('pagemaster_relations');
    $tables['pagemaster_relations_column'] = array (
        'tid1' => 'pm_tid',
        'pid1' => 'pm_pid',
        'id1'  => 'pm_id',
        'tid2' => 'pm_tid',
        'pid2' => 'pm_pid',
        'id2'  => 'pm_id'
    );
    $tables['pagemaster_relations_column_def'] = array (
        'tid1' => 'I4 NOTNULL',
        'pid1' => 'I4 NOTNULL',
        'id1'  => 'I4 NOTNULL',
        'tid2' => 'I4 NOTNULL',
        'pid2' => 'I4 NOTNULL',
        'id2'  => 'I4 NOTNULL',
    );
    ObjectUtil::addStandardFieldsToTableDefinition($tables['pagemaster_relations_column'], 'pm_');
    ObjectUtil::addStandardFieldsToTableDataDefinition($tables['pagemaster_relations_column_def']);
*/

    // pubfields table
    $tables['pagemaster_pubfields'] = DBUtil::getLimitedTablename('pagemaster_pubfields');
    $tables['pagemaster_pubfields_column'] = array (
        'id'             => 'pm_id',
        'tid'            => 'pm_tid',
        'name'           => 'pm_name',
        'title'          => 'pm_title',
        'description'    => 'pm_description',
        'fieldtype'      => 'pm_fieldtype',     // for performance reason, is also stored in plugin
        'fieldplugin'    => 'pm_fieldplugin',
        'fieldmaxlength' => 'pm_fieldmaxlength',
        'typedata'       => 'pm_typedata',
        'istitle'        => 'pm_istitle',
        'ispageable'     => 'pm_ispageable',
        'issearchable'   => 'pm_issearchable',
        'ismandatory'    => 'pm_ismandatory',
        'lineno'         => 'pm_lineno'
    );
    $tables['pagemaster_pubfields_column_def'] = array (
        'id'             => 'I4 PRIMARY AUTO',
        'tid'            => 'I4 NOTNULL',
        'name'           => "C(255) NOTNULL DEFAULT ''",
        'title'          => "C(255) NOTNULL DEFAULT ''",
        'description'    => "C(255) NOTNULL DEFAULT ''",
        'fieldtype'      => "C(50) NOTNULL DEFAULT ''",
        'fieldplugin'    => "C(50) NOTNULL DEFAULT ''",
        'fieldmaxlength' => 'I NULL',
        'typedata'       => 'C(4000) NULL',
        'istitle'        => 'L NOTNULL',
        'ispageable'     => 'L NOTNULL',
        'issearchable'   => 'L NOTNULL',
        'ismandatory'    => 'L NOTNULL',
        'lineno'         => 'I4 NOTNULL'
    );
    ObjectUtil::addStandardFieldsToTableDefinition($tables['pagemaster_pubfields_column'], 'pm_');
    ObjectUtil::addStandardFieldsToTableDataDefinition($tables['pagemaster_pubfields_column_def']);


    // pubtypes table
    $tables['pagemaster_pubtypes'] = DBUtil::getLimitedTablename('pagemaster_pubtypes');
    $tables['pagemaster_pubtypes_column'] = array (
        'tid'             => 'pm_tid',
        'title'           => 'pm_title',
        'urltitle'        => 'pm_urltitle',
        'filename'        => 'pm_filename',
        'formname'        => 'pm_formname',
        'description'     => 'pm_description',
        'itemsperpage'    => 'pm_itemsperpage',
        'sortfield1'      => 'pm_sortfield1',
        'sortdesc1'       => 'pm_sortdesc1',
        'sortfield2'      => 'pm_sortfield2',
        'sortdesc2'       => 'pm_sortdesc2',
        'sortfield3'      => 'pm_sortfield3',
        'sortdesc3'       => 'pm_sortdesc3',
        'defaultfilter'   => 'pm_defaultFilter',
        'workflow'        => 'pm_workflow',
        'enablerevisions' => 'pm_enablerevisions',
        'enableeditown'   => 'pm_enableeditown',
        'cachelifetime'   => 'pm_cachelifetime'
    );
    $tables['pagemaster_pubtypes_column_def'] = array (
        'tid'             => 'I4 PRIMARY AUTO',
        'title'           => "C(255) NOTNULL DEFAULT ''",
        'urltitle'        => "C(255) NOTNULL DEFAULT ''",
        'filename'        => "C(255) NOTNULL DEFAULT ''",
        'formname'        => "C(255) NOTNULL DEFAULT ''",
        'description'     => "C(255) NOTNULL DEFAULT ''",
        'itemsperpage'    => 'I4 NOTNULL',
        'sortfield1'      => "C(255)",
        'sortdesc1'       => 'L',
        'sortfield2'      => "C(255)",
        'sortdesc2'       => 'L',
        'sortfield3'      => "C(255)",
        'sortdesc3'       => 'L',
        'workflow'        => "C(255) NOTNULL",
        'defaultfilter'   => "C(255)",
        'enablerevisions' => 'L NOTNULL',
        'enableeditown'   => 'L NOTNULL',
        'cachelifetime'   => 'I8 NULL'
    );
    ObjectUtil::addStandardFieldsToTableDefinition($tables['pagemaster_pubtypes_column'], 'pm_');
    ObjectUtil::addStandardFieldsToTableDataDefinition($tables['pagemaster_pubtypes_column_def']);
    // indexes
    $tables['pagemaster_pubtypes_column_idx'] = array (
        'urltitle' => 'urltitle'
    );

/*
    // revisions table
    $tables['pagemaster_revisions'] = DBUtil::getLimitedTablename('pagemaster_revisions');
    $tables['pagemaster_revisions_column'] = array (
        'tid'         => 'pm_tid',
        'id'          => 'pm_id',
        'pid'         => 'pm_pid',
        'prevversion' => 'pm_prevversion'
    );
    $tables['pagemaster_revisions_column_def'] = array (
        'tid'         => 'I4 PRIMARY NOTNULL',
        'id'          => 'I4 PRIMARY NOTNULL',
        'pid'         => 'I4 NOTNULL',
        'prevversion' => 'I4 NOTNULL'
    );
    ObjectUtil::addStandardFieldsToTableDefinition($tables['pagemaster_revisions_column'], 'pm_');
    ObjectUtil::addStandardFieldsToTableDataDefinition($tables['pagemaster_revisions_column_def']);
*/

    // dynamic pubdata tables
    if (!function_exists('PageMaster_addtable')) {
        function PageMaster_addtable(&$tables, $tid, $tablecolumn, $tabledef)
        {
            $tablename = "pagemaster_pubdata{$tid}";

            $tables[$tablename] = DBUtil::getLimitedTablename($tablename);
            $tables[$tablename.'_column']     = $tablecolumn;
            $tables[$tablename.'_column_def'] = $tabledef;

            ObjectUtil::addStandardFieldsToTableDefinition($tables[$tablename.'_column'], 'pm_');
            ObjectUtil::addStandardFieldsToTableDataDefinition($tables[$tablename.'_column_def']);

            // TODO indexes
            /*
            $tables[$tablename.'_column_idx'] = array (
                'core_online' => 'core_online' //core_showinlist
            );
            */
        }
    }

    $existingtables = DBUtil::metaTables();

    if (in_array(DBUtil::getLimitedTablename('pagemaster_pubfields'), $existingtables)) {
        $sql = 'SELECT ' . $tables['pagemaster_pubfields_column']['tid']
                  . ', ' . $tables['pagemaster_pubfields_column']['id']
                  . ', ' . $tables['pagemaster_pubfields_column']['name']
                  . ', ' . $tables['pagemaster_pubfields_column']['fieldtype']
                  . ' FROM ' . $tables['pagemaster_pubfields']
                  . ' ORDER BY ' . $tables['pagemaster_pubfields_column']['tid'] . ' ASC, '
                                 . $tables['pagemaster_pubfields_column']['id']  . ' ASC ';

        $result = DBUtil::executeSQL($sql);

        if ($result == false) {
            return LogUtil::registerError('Error! Failed to load the pubfields.');
        }

        $old_tid = 0;

        $tableorder = array(
            'core_pid'         => 'pm_pid',
            'id'               => 'pm_id'
        );
        $tablecolumncore = array(
            'id'               => 'pm_id',
            'core_pid'         => 'pm_pid',
            'core_author'      => 'pm_author',
            'core_hitcount'    => 'pm_hitcount',
            'core_language'    => 'pm_language',
            'core_revision'    => 'pm_revision',
            'core_online'      => 'pm_online',
            'core_indepot'     => 'pm_indepot',
            'core_showinmenu'  => 'pm_showinmenu',
            'core_showinlist'  => 'pm_showinlist',
            'core_publishdate' => 'pm_publishdate',
            'core_expiredate'  => 'pm_expiredate'
        );
        $tabledefcore = array(
            'id'               => 'I4 PRIMARY AUTO',
            'core_pid'         => 'I4 NOTNULL',
            'core_author'      => 'I4 NOTNULL',
            'core_hitcount'    => 'I8 DEFAULT 0',
            'core_language'    => 'C(10) NOTNULL', //FIXME how many chars are needed for a gettext code?
            'core_revision'    => 'I4 NOTNULL',
            'core_online'      => 'L NOTNULL',
            'core_indepot'     => 'L NOTNULL',
            'core_showinmenu'  => 'L NOTNULL',
            'core_showinlist'  => 'L NOTNULL DEFAULT 1',
            'core_publishdate' => 'T',
            'core_expiredate'  => 'T'
        );

        // loop the pubfields adding their definitions
        // to their pubdata tables
        $tablecolumn = array();
        $tabledef    = array();

        $pubfields = DBUtil::marshallObjects($result, array('tid', 'id', 'name', 'fieldtype'));

        foreach ($pubfields as $pubfield) {
            // if we change of publication type
            if ($pubfield['tid'] != $old_tid && $old_tid != 0) {
                // add the table definition to the $tables array
                PageMaster_addtable($tables, $old_tid, array_merge($tableorder, $tablecolumn, $tablecolumncore), array_merge($tabledefcore, $tabledef));
                // and reset the columns and definitions for the next pubtype
                $tablecolumn = array();
                $tabledef    = array();
            }

            // add the column and definition for this field
            $tablecolumn[$pubfield['name']] = "pm_{$pubfield['id']}";
            $tabledef[$pubfield['name']]    = "{$pubfield['fieldtype']} NULL";

            // set the actual tid to check a pubtype change in the next cycle
            $old_tid = $pubfield['tid'];
        }

        // the final one doesn't trigger a tid change
        if (!empty($tablecolumn)) {
            PageMaster_addtable($tables, $old_tid, array_merge($tableorder, $tablecolumn, $tablecolumncore), array_merge($tabledefcore, $tabledef));
        }
    }

    return $tables;
}
