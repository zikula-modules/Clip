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

function pagemaster_pntables()
{
    $pntable = array ();


    // relations table
    $pntable['pagemaster_relations'] = DBUtil::getLimitedTablename('pagemaster_relations');
    $pntable['pagemaster_relations_column'] = array (
        'tid1' => 'pm_tid',
        'pid1' => 'pm_pid',
        'id1'  => 'pm_id',
        'tid2' => 'pm_tid',
        'pid2' => 'pm_pid',
        'id2'  => 'pm_id'
    );
    $pntable['pagemaster_relations_column_def'] = array (
        'tid1' => 'I NOTNULL',
        'pid1' => 'I NOTNULL',
        'id1'  => 'I NOTNULL',
        'tid2' => 'I NOTNULL',
        'pid2' => 'I NOTNULL',
        'id2'  => 'I NOTNULL',
    );
    ObjectUtil::addStandardFieldsToTableDefinition($pntable['pagemaster_relations_column'], 'pm_');
    ObjectUtil::addStandardFieldsToTableDataDefinition($pntable['pagemaster_relations_column_def']);


    // pubfields table
    $pntable['pagemaster_pubfields'] = DBUtil::getLimitedTablename('pagemaster_pubfields');
    $pntable['pagemaster_pubfields_column'] = array (
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
    $pntable['pagemaster_pubfields_column_def'] = array (
        'id'             => 'I PRIMARY AUTO',
        'tid'            => 'I NOTNULL',
        'name'           => "C(255) NOTNULL DEFAULT ''",
        'title'          => "C(255) NOTNULL DEFAULT ''",
        'description'    => "C(255) NOTNULL DEFAULT ''",
        'fieldtype'      => "C(50) NOTNULL DEFAULT ''",
        'fieldplugin'    => "C(50) NOTNULL DEFAULT ''",
        'fieldmaxlength' => 'I NULL',
        'typedata'       => 'C(4000) NULL',
        'istitle'        => 'I4 NOTNULL',
        'ispageable'     => 'I4 NOTNULL',
        'issearchable'   => 'I4 NOTNULL',
        'ismandatory'    => 'I4 NOTNULL',
        'lineno'         => 'I NOTNULL'
    );
    ObjectUtil::addStandardFieldsToTableDefinition($pntable['pagemaster_pubfields_column'], 'pm_');
    ObjectUtil::addStandardFieldsToTableDataDefinition($pntable['pagemaster_pubfields_column_def']);


    // pubtypes table
    $pntable['pagemaster_pubtypes'] = DBUtil::getLimitedTablename('pagemaster_pubtypes');
    $pntable['pagemaster_pubtypes_column'] = array (
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
    $pntable['pagemaster_pubtypes_column_def'] = array (
        'tid'             => 'I PRIMARY AUTO',
        'title'           => "C(255) NOTNULL DEFAULT ''",
        'urltitle'        => "C(255) NOTNULL DEFAULT ''",
        'filename'        => "C(255) NOTNULL DEFAULT ''",
        'formname'        => "C(255) NOTNULL DEFAULT ''",
        'description'     => "C(255) NOTNULL DEFAULT ''",
        'itemsperpage'    => 'I NOTNULL',
        'sortfield1'      => "C(255)",
        'sortdesc1'       => 'I4',
        'sortfield2'      => "C(255)",
        'sortdesc2'       => 'I4',
        'sortfield3'      => "C(255)",
        'sortdesc3'       => 'I4',
        'workflow'        => "C(255) NOTNULL",
        'defaultfilter'   => "C(255)",
        'enablerevisions' => 'I4 NOTNULL',
        'enableeditown'   => 'I4 NOTNULL',
        'cachelifetime'   => 'I8 NULL'
    );
    ObjectUtil::addStandardFieldsToTableDefinition($pntable['pagemaster_pubtypes_column'], 'pm_');
    ObjectUtil::addStandardFieldsToTableDataDefinition($pntable['pagemaster_pubtypes_column_def']);

/*
    // revisions table
    $pntable['pagemaster_revisions'] = DBUtil::getLimitedTablename('pagemaster_revisions');
    $pntable['pagemaster_revisions_column'] = array (
        'tid'         => 'pm_tid',
        'id'          => 'pm_id',
        'pid'         => 'pm_pid',
        'prevversion' => 'pm_prevversion'
    );
    $pntable['pagemaster_revisions_column_def'] = array (
        'tid'         => 'I PRIMARY NOTNULL',
        'id'          => 'I PRIMARY NOTNULL',
        'pid'         => 'I NOTNULL',
        'prevversion' => 'I NOTNULL'
    );
    ObjectUtil::addStandardFieldsToTableDefinition($pntable['pagemaster_revisions_column'], 'pm_');
    ObjectUtil::addStandardFieldsToTableDataDefinition($pntable['pagemaster_revisions_column_def']);
*/

    // dynamic pubdata tables
    if (!function_exists('pagemaster_addtable')) {
        function pagemaster_addtable(&$pntable, $tid, $tablecolumn, $tabledef)
        {
            $tablename = "pagemaster_pubdata{$tid}";

            $pntable[$tablename] = DBUtil::getLimitedTablename($tablename);
            $pntable[$tablename.'_column']     = $tablecolumn;
            $pntable[$tablename.'_column_def'] = $tabledef;

            ObjectUtil::addStandardFieldsToTableDefinition($pntable[$tablename.'_column'], 'pm_');
            ObjectUtil::addStandardFieldsToTableDataDefinition($pntable[$tablename.'_column_def']);
        }
    }

    // Can't use DBUtil because the pagemaster table definitions are not loaded yet
    list($dbconn) = pnDBGetConn();
    $sql = 'SELECT ' . $pntable['pagemaster_pubfields_column']['tid']
              . ', ' . $pntable['pagemaster_pubfields_column']['id']
              . ', ' . $pntable['pagemaster_pubfields_column']['name']
              . ', ' . $pntable['pagemaster_pubfields_column']['fieldtype']
              . ' FROM ' . $pntable['pagemaster_pubfields']
              . ' ORDER BY ' . $pntable['pagemaster_pubfields_column']['tid'] . ' ASC, '
                             . $pntable['pagemaster_pubfields_column']['id']  . ' ASC ';

    $result = $dbconn->execute($sql); 
    if ($dbconn->errorNo() != 0) {
        // installation
    } else {
        $old_tid = 0;

        $tablefirst = array(
            'core_title'       => 'pm_pid', // field to be overriden by the title field
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
            'id'               => 'I PRIMARY AUTO',
            'core_pid'         => 'I NOTNULL',
            'core_author'      => 'I(11) NOTNULL',
            'core_hitcount'    => 'I(9) DEFAULT 0',
            'core_language'    => 'C(3) NOTNULL',
            'core_revision'    => 'I NOTNULL',
            'core_online'      => 'I4 NOTNULL',
            'core_indepot'     => 'I4 NOTNULL',
            'core_showinmenu'  => 'I4 NOTNULL',
            'core_showinlist'  => 'I4 NOTNULL DEFAULT 1',
            'core_publishdate' => 'T',
            'core_expiredate'  => 'T'
        );

        // loop the pubfields adding their definitions
        // to their pubdata tables
        $tablecolumn = array();
        $tabledef    = array();

        for (; !$result->EOF; $result->MoveNext()) {
            $tid       = $result->fields[0];
            $id        = $result->fields[1];
            $name      = $result->fields[2];
            $fieldtype = $result->fields[3];

            // if we change of publication type
            if ($tid != $old_tid && $old_tid != 0) {
                // add the table definition to the $pntable array
                pagemaster_addtable($pntable, $old_tid, array_merge($tablefirst, $tablecolumn, $tablecolumncore), array_merge($tabledefcore, $tabledef));
                // and reset the columns and definitions for the next pubtype
                $tablecolumn = array();
                $tabledef    = array();
            }

            // add the column and definition for this field
            $tablecolumn[$name] = "pm_{$id}";
            $tabledef[$name]    = "{$fieldtype} NULL";

            // set the actual tid to check a pubtype change in the next cycle
            $old_tid = $tid;
        }

        // the final one doesn't trigger a tid change
        if (isset($tablecolumn) && !empty($tablecolumn)) {
            pagemaster_addtable($pntable, $old_tid, array_merge($tablefirst, $tablecolumn, $tablecolumncore), array_merge($tabledefcore, $tabledef));
        }
    }

    return $pntable;
}
