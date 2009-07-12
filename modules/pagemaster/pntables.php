<?php
// $Id$
// =======================================================================
// Pagesetter by Jorn Lind-Nielsen (C) 2003.
// ----------------------------------------------------------------------
// For POST-NUKE Content Management System
// Copyright (C) 2002 by the PostNuke Development Team.
// http://www.postnuke.com/
// ----------------------------------------------------------------------
// LICENSE
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License (GPL)
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WIthOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// To read the license please visit http://www.gnu.org/copyleft/gpl.html
// =======================================================================

/**
 * This function is called internally by the core whenever the module is
 * loaded.  It adds in information about the tables that the module uses.
 */
function pagesetter_pntables()
{
  $pntable = array();

    // Publication types table setup

  $tableName = pnConfigGetVar('prefix') . '_pagesetter_pubtypes';

  $pntable['pagesetter_pubtypes'] = $tableName;

  $pntable['pagesetter_pubtypes_column'] = array('id'                 => 'pg_id',
                                                 'title'              => 'pg_title',
                                                 'filename'           => 'pg_filename',
                                                 'formname'           => 'pg_formname',
                                                 'description'        => 'pg_description',
                                                 'authorID'           => 'pg_authorid',
                                                 'created'            => 'pg_createddate',
                                                 'listCount'          => 'pg_listcount',
                                                 'sortField1'         => 'pg_sortfield1',
                                                 'sortDesc1'          => 'pg_sortdesc1',
                                                 'sortField2'         => 'pg_sortfield2',
                                                 'sortDesc2'          => 'pg_sortdesc2',
                                                 'sortField3'         => 'pg_sortfield3',
                                                 'sortDesc3'          => 'pg_sortdesc3',
                                                 'defaultFilter'      => 'pg_defaultFilter',
                                                 'enableHooks'        => 'pg_enablehooks',
                                                 'workflow'           => 'pg_workflow',
                                                 'enableRevisions'    => 'pg_enablerevisions',
                                                 'enableEditOwn'      => 'pg_enableeditown',
                                                 'enableTopicAccess'  => 'pg_enabletopicaccess',
                                                 'defaultFolder'      => 'pg_defaultfolder',
                                                 'defaultSubFolder'   => 'pg_defaultsubfolder',
                                                 'defaultFolderTopic' => 'pg_defaultfoldertopic');


    // Publication fields table setup

  $tableName = pnConfigGetVar('prefix') . '_pagesetter_pubfields';

  $pntable['pagesetter_pubfields'] = $tableName;

  $pntable['pagesetter_pubfields_column'] = array('id'            => 'pg_id',
                                                  'tid'           => 'pg_tid',
                                                  'name'          => 'pg_name',
                                                  'title'         => 'pg_title',
                                                  'description'   => 'pg_description',
                                                  'type'          => 'pg_type',
                                                  'typeName'      => 'pg_typename',
                                                  'typeData'      => 'pg_typedata',
                                                  'isTitle'       => 'pg_istitle',
                                                  'isPageable'    => 'pg_ispageable',
                                                  'isSearchable'  => 'pg_issearchable',
                                                  'isMandatory'   => 'pg_ismandatory',
                                                  'lineno'        => 'pg_lineno');

    // Lists tables setup

  $tableName = pnConfigGetVar('prefix') . '_pagesetter_lists';

  $pntable['pagesetter_lists'] = $tableName;

  $pntable['pagesetter_lists_column'] = array('id'          => 'pg_id',
                                              'authorID'    => 'pg_authorid',
                                              'created'     => 'pg_created',
                                              'title'       => 'pg_title',
                                              'description' => 'pg_description');


  $tableName = pnConfigGetVar('prefix') . '_pagesetter_listitems';

  $pntable['pagesetter_listitems'] = $tableName;

  $pntable['pagesetter_listitems_column'] = array('id'          => 'pg_id',
                                                  'lid'         => 'pg_lid',
                                                  'parentID'    => 'pg_parentid',
                                                  'title'       => 'pg_title',
                                                  'fullTitle'   => 'pg_fulltitle',
                                                  'value'       => 'pg_value',
                                                  'description' => 'pg_description',
                                                  'lineno'      => 'pg_lineno',
                                                  'indent'      => 'pg_indent',
                                                  'lval'        => 'pg_lval',
                                                  'rval'        => 'pg_rval');


    // Publication header tables setup

  $tableName = pnConfigGetVar('prefix') . '_pagesetter_pubheader';

  $pntable['pagesetter_pubheader'] = $tableName;

  $pntable['pagesetter_pubheader_column'] = array('tid'         => 'pg_tid', // Key
                                                  'pid'         => 'pg_pid', // Key
                                                  'hitCount'    => 'pg_hitcount',
                                                  'onlineID'    => 'pg_onlineid',
                                                  'deleted'     => 'pg_deleted');


    // Revisions tables setup

  $tableName = pnConfigGetVar('prefix') . '_pagesetter_revisions';

  $pntable['pagesetter_revisions'] = $tableName;

  $pntable['pagesetter_revisions_column'] = array('tid'             => 'pg_tid', // Key
                                                  'id'              => 'pg_id',  // Key
                                                  'pid'             => 'pg_pid',
                                                  'previousVersion' => 'pg_prevversion',
                                                  'user'            => 'pg_user',
                                                  'timestamp'       => 'pg_timestamp');


    // Workflow configuration tables setup

  $tableName = pnConfigGetVar('prefix') . '_pagesetter_wfcfg';

  $pntable['pagesetter_wfcfg'] = $tableName;

  $pntable['pagesetter_wfcfg_column'] = array('workflow'        => 'pg_workflow', // Key
                                              'tid'             => 'pg_tid',      // Key
                                              'setting'         => 'pg_setting',  // Key
                                              'value'           => 'pg_value');


    // Counters tables setup

  $tableName = pnConfigGetVar('prefix') . '_pagesetter_counters';

  $pntable['pagesetter_counters'] = $tableName;

  $pntable['pagesetter_counters_column'] = array('name'            => 'pg_name', // Key
                                                 'count'           => 'pg_count');


    // Guppy session data

  $tableName = pnConfigGetVar('prefix') . '_pagesetter_session';

  $pntable['pagesetter_session'] = $tableName;

  $pntable['pagesetter_session_column'] = array('sessionId' => 'pg_sessionid',
                                                'cache'     => 'pg_cache',
                                                'lastUsed'  => 'pg_lastused');
                                                

    // Relations data
    
  $tableName = pnConfigGetVar('prefix') . '_pagesetter_relations';
  
  $pntable['pagesetter_relations'] = $tableName;
  
  $pntable['pagesetter_relations_column'] = array('tid1'         => 'pg_tid1',
                                                  'pid1'         => 'pg_pid1',
                                                  'fieldId1'     => 'pg_fieldid1',
                                                  'tid2'         => 'pg_tid2',
                                                  'pid2'         => 'pg_pid2',
                                                  'fieldId2'     => 'pg_fieldid2');


    // Publication data (the fixed fields)

  $tableName = pnConfigGetVar('prefix') . '_pagesetter_pubdata'; 

  $pntable['pagesetter_pubdata'] = $tableName; // Unused since we create it dynamically

  $pntable['pagesetter_pubdata_column'] = array('id'                => 'pg_id',  // database ID
                                                'pid'               => 'pg_pid', // publication ID
                                                'approvalState'     => 'pg_approvalState',
                                                'online'            => 'pg_online', // Duplicate/redundant by purpose
                                                'inDepot'           => 'pg_indepot',
                                                'revision'          => 'pg_revision',
                                                'topic'             => 'pg_topic',
                                                'showInMenu'        => 'pg_showInMenu',
                                                'showInList'        => 'pg_showInList',
                                                'author'            => 'pg_author',  // Modifieable author name
                                                'creatorID'         => 'pg_creator', // Non-modifiable author ID
                                                'created'           => 'pg_created',
                                                'hitCount'          => 'pg_hitCount', // Unused, left to enable upgrades
                                                'lastUpdated'       => 'pg_lastUpdatedDate',
                                                'publishDate'       => 'pg_publishDate',
                                                'expireDate'        => 'pg_expireDate',
                                                'language'          => 'pg_language');

  return $pntable;
}

?>