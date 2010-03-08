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

require_once("modules/pagesetter/common.php");


function pagesetter_adminapi_getPublicationTypes($args)
{
    // Check access
  if (!pnSecAuthAction(0, 'pagesetter::', '::', ACCESS_READ))
    return pagesetterErrorAPI(__FILE__, __LINE__, _PGNOAUTH);

  $getForGuppyDropdown = (isset($args['getForGuppyDropdown']) ? $args['getForGuppyDropdown'] : false);

  list($dbconn) = pnDBGetConn();
  $pntable = pnDBGetTables();

  $pubTypesTable = $pntable['pagesetter_pubtypes'];
  $pubTypesColumn = &$pntable['pagesetter_pubtypes_column'];

  $sql = "SELECT $pubTypesColumn[id],
                 $pubTypesColumn[title],
                 $pubTypesColumn[description],
                 $pubTypesColumn[authorID],
                 $pubTypesColumn[created]
          FROM   $pubTypesTable
          ORDER BY $pubTypesColumn[title]";

  $result = $dbconn->execute($sql);

  if ($dbconn->errorNo() != 0)
    return pagesetterErrorAPI(__FILE__, __LINE__, '"getPublicationTypes" failed: '
                                                  . $dbconn->errorMsg() . " while executing: $sql");

  $pubTypes = array();

  for (; !$result->EOF; $result->MoveNext())
  {
    $tid = $result->fields[0];

    if (pnSecAuthAction(0, 'pagesetter::', "$tid::", ACCESS_READ))
      if ($getForGuppyDropdown)
        $pubTypes[] = array('value' => $tid,
                            'title' => $result->fields[1]);
      else
        $pubTypes[] = array('id'          => $tid,
                            'title'       => $result->fields[1],
                            'description' => $result->fields[2],
                            'authorID'    => $result->fields[3],
                            'author'      => pnUserGetVar('uname', $result->fields[3]),
                            'created'     => $result->fields[4]);
  }

  $result->Close();

  return $pubTypes;
}


function pagesetter_adminapi_getPubTypeInfo($args)
{
  if (!isset($args['tid']))
    return pagesetterErrorAPI(__FILE__, __LINE__, "Missing argument 'tid' in 'pagesetter_adminapi_getPubTypeInfo'");

  $tid = (int)$args['tid'];

    // Check access
  if (!pnSecAuthAction(0, 'pagesetter::', "$tid::", ACCESS_READ))
    return pagesetterErrorAPI(__FILE__, __LINE__, _PGNOAUTH);

  static $cache = array();
  if (isset($cache[$tid]))
    return $cache[$tid];

  list($dbconn) = pnDBGetConn();
  $pntable = pnDBGetTables();

    // Fetch basic information

  $pubTypesTable = $pntable['pagesetter_pubtypes'];
  $pubTypesColumn = &$pntable['pagesetter_pubtypes_column'];

  $sql = "SELECT $pubTypesColumn[id],
                 $pubTypesColumn[title],
                 $pubTypesColumn[filename],
                 $pubTypesColumn[formname],
                 $pubTypesColumn[description],
                 $pubTypesColumn[authorID],
                 $pubTypesColumn[created],
                 $pubTypesColumn[listCount],
                 $pubTypesColumn[sortField1],
                 $pubTypesColumn[sortDesc1],
                 $pubTypesColumn[sortField2],
                 $pubTypesColumn[sortDesc2],
                 $pubTypesColumn[sortField3],
                 $pubTypesColumn[sortDesc3],
                 $pubTypesColumn[defaultFilter],
                 $pubTypesColumn[enableHooks],
                 $pubTypesColumn[workflow],
                 $pubTypesColumn[enableRevisions],
                 $pubTypesColumn[enableEditOwn],
                 $pubTypesColumn[enableTopicAccess],
                 $pubTypesColumn[defaultFolder],
                 $pubTypesColumn[defaultSubFolder],
                 $pubTypesColumn[defaultFolderTopic]
          FROM   $pubTypesTable
          WHERE  $pubTypesColumn[id] = " . $tid;

  $result = $dbconn->execute($sql);

  if ($dbconn->errorNo() != 0)
    return pagesetterErrorAPI(__FILE__, __LINE__, '"getPubTypeInfo" failed: ' 
                                                  . $dbconn->errorMsg() . " while executing: $sql");

  if ($result->EOF)
    return pagesetterErrorAPI(__FILE__, __LINE__, "Unknown publication type ID '$tid'");

  $pubTypeInfo = array('id'              => $result->fields[0],
                       'title'           => $result->fields[1],
                       'filename'        => $result->fields[2],
                       'formname'        => $result->fields[3],
                       'description'     => $result->fields[4],
                       'authorID'        => $result->fields[5],
                       'author'          => pnUserGetVar('uname', $result->fields[5]),
                       'created'         => $result->fields[6],
                       'listCount'       => intval($result->fields[7]),
                       'sortField1'      => $result->fields[8],
                       'sortDesc1'       => $result->fields[9],
                       'sortField2'      => $result->fields[10],
                       'sortDesc2'       => $result->fields[11],
                       'sortField3'      => $result->fields[12],
                       'sortDesc3'       => $result->fields[13],
                       'defaultFilter'   => $result->fields[14],
                       'enableHooks'     => $result->fields[15],
                       'workflow'        => $result->fields[16],
                       'enableRevisions' => $result->fields[17],
                       'enableEditOwn'   => $result->fields[18],
                       'enableTopicAccess'  => $result->fields[19],
                       'defaultFolder'      => $result->fields[20],
                       'defaultSubFolder'   => $result->fields[21],
                       'defaultFolderTopic' => $result->fields[22],
                       'titleFieldID'       => null,
                       'pageableFieldID'    => null);

  $result->Close();


    // Fetch fields information

  $pubFieldsTable = $pntable['pagesetter_pubfields'];
  $pubFieldsColumn = &$pntable['pagesetter_pubfields_column'];

  $sql = "SELECT $pubFieldsColumn[id],
                 $pubFieldsColumn[name],
                 $pubFieldsColumn[title],
                 $pubFieldsColumn[description],
                 $pubFieldsColumn[type],
                 $pubFieldsColumn[typeData],
                 $pubFieldsColumn[isTitle],
                 $pubFieldsColumn[isPageable],
                 $pubFieldsColumn[isSearchable],
                 $pubFieldsColumn[isMandatory],
                 $pubFieldsColumn[lineno]
          FROM   $pubFieldsTable
          WHERE  $pubFieldsColumn[tid] = " . $tid . "
          ORDER BY $pubFieldsColumn[lineno]";

  $result = $dbconn->execute($sql);

  if ($dbconn->errorNo() != 0)
    return pagesetterErrorAPI(__FILE__, __LINE__, '"getPublTypeInfo" failed: '
                                                  . $dbconn->errorMsg() . " while executing: $sql");

  $pubFields = array();
  $pubFieldIndex = array();
  $pubFieldIdIndex = array();
  $pubColumnIndex = array();

  for (; !$result->EOF; $result->MoveNext())
  {
    $record = array('id'           => $result->fields[0],
                    'name'         => $result->fields[1],
                    'title'        => $result->fields[2],
                    'description'  => $result->fields[3],
                    'type'         => $result->fields[4],
                    'typeData'     => $result->fields[5],
                    'isTitle'      => intval($result->fields[6]),
                    'isPageable'   => intval($result->fields[7]),
                    'isSearchable' => intval($result->fields[8]),
                    'isMandatory'  => intval($result->fields[9]),
                    'lineno'       => intval($result->fields[10]));

    if ($record['isTitle'])
      $pubTypeInfo['titleFieldID'] = $record['id'];

    if ($record['isPageable'])
      $pubTypeInfo['pageableFieldID'] = $record['id'];

    $pubFieldIndex[$record['name']] = count($pubFields);
    $pubFieldIdIndex[$record['id']] = count($pubFields);
    $pubColumnIndex[ pagesetterGetPubColumnName($record['id']) ] = count($pubFields);
    $pubFields[] = $record;
  }

  $result->Close();
  
  $typeInfo = array('publication'  => $pubTypeInfo,
                    'fields'       => $pubFields,
                    'fieldIndex'   => $pubFieldIndex,
                    'fieldIdIndex' => $pubFieldIdIndex,
                    'columnIndex'  => $pubColumnIndex);

  return $cache[$tid] = $typeInfo;
}


function pagesetter_adminapi_getSearchableColumns($args)
{
  if (!isset($args['tid']))
    return pagesetterErrorAPI(__FILE__, __LINE__, "Missing argument 'tid' in 'pagesetter_adminapi_getSearchableColumns'");

  $tid = $args['tid'];

  $pubInfo = pnModAPIFunc( 'pagesetter',
                           'admin',
                           'getPubTypeInfo',
                           array('tid' => $tid) );
 
  $searchableColumns = array();

  foreach ($pubInfo['fields'] as $field)
  {
    if ($field['isSearchable'] == 1)
      $searchableColumns[] = pagesetterGetPubColumnName($field['id']);
  }

  return $searchableColumns;
}


function pagesetter_adminapi_getListIDByFieldName($args)
{
  if (!isset($args['tid']))
    return pagesetterErrorAPI(__FILE__, __LINE__, "Missing argument 'tid' in 'pagesetter_adminapi_getListIDByFieldName'");
  if (!isset($args['field']))
    return pagesetterErrorAPI(__FILE__, __LINE__, "Missing argument 'field' in 'pagesetter_adminapi_getListIDByFieldName'");

  $tid   = $args['tid'];
  $field = $args['field'];

  $pubInfo = pnModAPIFunc( 'pagesetter',
                           'admin',
                           'getPubTypeInfo',
                           array('tid' => $tid) );

  $fieldSpec = $pubInfo['fields'][$pubInfo['fieldIndex'][$field]];
  $fieldType = $fieldSpec['type'];

  if ($fieldType < pagesetterFieldTypeListoffset)
    return pagesetterErrorAPI(__FILE__, __LINE__, "The specified field '$field' is not a list field.");

  $listID = $fieldType - pagesetterFieldTypeListoffset;

  return $listID;
}


function pagesetterCheckPageableFields($fields)
{
  $pageableCount = 0;
  
  foreach ($fields as $field)
    if ($field['isPageable'])
      ++$pageableCount;

  if ($pageableCount > 1)
    return false;

  return true;
}


function pagesetter_adminapi_createPublicationType($args)
{
    // Check access
  if (!pnSecAuthAction(0, 'pagesetter::', '::', ACCESS_ADMIN))
    return pagesetterErrorAPI(__FILE__, __LINE__, _PGNOAUTH);

  if (!isset($args['publication']))
    return pagesetterErrorAPI(__FILE__, __LINE__, "Missing argument 'publication' in 'pagesetter_adminapi_createPublicationType'");
  if (!isset($args['fields']))
    return pagesetterErrorAPI(__FILE__, __LINE__, "Missing argument 'fields' in 'pagesetter_adminapi_createPublicationType'");
  if (!isset($args['authorID']))
    return pagesetterErrorAPI(__FILE__, __LINE__, "Missing argument 'authorID' in 'pagesetter_adminapi_createPublicationType'");

  $pubTypeData    = $args['publication'];
  $pubFieldsData  = $args['fields'];
  $authorID       = $args['authorID'];

  if (!pagesetterCheckPageableFields($pubFieldsData))
    return pagesetterErrorAPI(null,null, _PGONLYONEPAGEABLE);

  list($dbconn) = pnDBGetConn();
  $pntable = pnDBGetTables();

    // Create publication type

  $pubTypesTable = $pntable['pagesetter_pubtypes'];
  $pubTypesColumn = &$pntable['pagesetter_pubtypes_column'];

  if (empty($pubTypeData['filename']))
    $pubTypeData['filename'] = $pubTypeData['title'];

  if (empty($pubTypeData['formname']))
    $pubTypeData['formname'] = $pubTypeData['filename'];

  if (!isset($pubTypeData['sortField1']))
    $pubTypeData['sortField1'] = null;
  if (!isset($pubTypeData['sortDesc1']))
    $pubTypeData['sortDesc1'] = 0;
  if (!isset($pubTypeData['sortField2']))
    $pubTypeData['sortField2'] = null;
  if (!isset($pubTypeData['sortDesc2']))
    $pubTypeData['sortDesc2'] = 0;
  if (!isset($pubTypeData['sortField3']))
    $pubTypeData['sortField3'] = null;
  if (!isset($pubTypeData['sortDesc3']))
    $pubTypeData['sortDesc3'] = 0;

  if (!isset($pubTypeData['defaultFilter']))
    $pubTypeData['defaultFilter'] = '';
  if (!isset($pubTypeData['enableHooks']))
    $pubTypeData['enableHooks'] = true;
  if (!isset($pubTypeData['enableRevisions']))
    $pubTypeData['enableRevisions'] = true;
  if (!isset($pubTypeData['enableEditOwn']))
    $pubTypeData['enableEditOwn'] = false;
  if (!isset($pubTypeData['enableTopicAccess']))
    $pubTypeData['enableTopicAccess'] = false;
  if (!isset($pubTypeData['defaultSubFolder']))
    $pubTypeData['defaultSubFolder'] = '';
  if (!isset($pubTypeData['defaultFolderTopic']))
    $pubTypeData['defaultFolderTopic'] = -1;

  $pubTypeData['filename'] = pagesetterSanitizeIdentifier($pubTypeData['filename']);
  $pubTypeData['formname'] = pagesetterSanitizeIdentifier($pubTypeData['formname']);

  $sql = "INSERT INTO $pubTypesTable (
           $pubTypesColumn[title],
           $pubTypesColumn[filename],
           $pubTypesColumn[formname],
           $pubTypesColumn[description],
           $pubTypesColumn[authorID],
           $pubTypesColumn[created],
           $pubTypesColumn[listCount],
           $pubTypesColumn[sortField1],
           $pubTypesColumn[sortDesc1],
           $pubTypesColumn[sortField2],
           $pubTypesColumn[sortDesc2],
           $pubTypesColumn[sortField3],
           $pubTypesColumn[sortDesc3],
           $pubTypesColumn[defaultFilter],
           $pubTypesColumn[enableHooks],
           $pubTypesColumn[workflow],
           $pubTypesColumn[enableRevisions],
           $pubTypesColumn[enableEditOwn],
           $pubTypesColumn[enableTopicAccess],
           $pubTypesColumn[defaultFolder],
           $pubTypesColumn[defaultSubFolder],
           $pubTypesColumn[defaultFolderTopic])
          VALUES (
            '" . pnVarPrepForStore($pubTypeData['title']) . "',
            '" . pnVarPrepForStore($pubTypeData['filename']) . "',
            '" . pnVarPrepForStore($pubTypeData['formname']) . "',
            '" . pnVarPrepForStore($pubTypeData['description']) . "',
            '" . pnVarPrepForStore($authorID) . "',
            NOW(),
            " . (int)$pubTypeData['listCount'] . ",
            '" . pnVarPrepForStore($pubTypeData['sortField1']) . "',
            " . (int)$pubTypeData['sortDesc1'] . ",
            '" . pnVarPrepForStore($pubTypeData['sortField2']) . "',
            " . (int)$pubTypeData['sortDesc2'] . ",
            '" . pnVarPrepForStore($pubTypeData['sortField3']) . "',
            " . (int)$pubTypeData['sortDesc3'] . ",
            '" . pnVarPrepForStore($pubTypeData['defaultFilter']) . "',
            " . (int)$pubTypeData['enableHooks'] . ",
            '" . pnVarPrepForStore($pubTypeData['workflow']) . "',
            " . (int)$pubTypeData['enableRevisions'] . ",
            " . (int)$pubTypeData['enableEditOwn'] . ",
            " . (int)$pubTypeData['enableTopicAccess'] . ",
            " . (int)$pubTypeData['defaultFolder'] . ",
            '" . pnVarPrepForStore($pubTypeData['defaultSubFolder']) . "',
            " . (int)$pubTypeData['defaultFolderTopic'] . ")";

  $result = $dbconn->execute($sql);

  if ($dbconn->errorNo() != 0)
    return pagesetterErrorAPI(__FILE__, __LINE__, '"createPublicationType" failed: ' 
                                                  . $dbconn->errorMsg() . " while executing: $sql");

  $tid = $dbconn->Insert_ID();


    // Create publication relation

  $dataTable = pagesetterGetPubTableName($tid);
  $dataColumn = $pntable['pagesetter_pubdata_column'];

  $sql = "CREATE TABLE $dataTable (
            $dataColumn[id] INT NOT NULL AUTO_INCREMENT,
            $dataColumn[pid] INT NOT NULL,
            $dataColumn[approvalState] VARCHAR(255),
            $dataColumn[online] TINYINT,
            $dataColumn[inDepot] TINYINT DEFAULT 0,
            $dataColumn[revision] INT DEFAULT 1,
            $dataColumn[topic] INT,
            $dataColumn[showInMenu] TINYINT,
            $dataColumn[showInList] TINYINT,
            $dataColumn[author] VARCHAR(255),
            $dataColumn[creatorID] INT NOT NULL,
            $dataColumn[created] DATETIME,
            $dataColumn[lastUpdated] DATETIME,
            $dataColumn[publishDate] DATETIME,
            $dataColumn[expireDate] DATETIME,
            $dataColumn[language] VARCHAR(10),
            PRIMARY KEY (pg_id))";
  $dbconn->execute($sql);

  if ($dbconn->errorNo() != 0)
    return pagesetterErrorAPI(__FILE__, __LINE__, '"createPublicationType" failed: ' 
                                                  . $dbconn->errorMsg() . " while executing: $sql");


  $sql = "ALTER TABLE $dataTable ADD INDEX ($dataColumn[pid], $dataColumn[online])";

  $dbconn->execute($sql);

  if ($dbconn->errorNo() != 0)
    return pagesetterErrorAPI(__FILE__, __LINE__, '"createPublicationType" failed: ' 
                                                  . $dbconn->errorMsg() . " while executing: $sql");
    // Create fields

  $pubFieldsTable = $pntable['pagesetter_pubfields'];
  $pubFieldsColumn = &$pntable['pagesetter_pubfields_column'];
  
  foreach ($pubFieldsData as $record)
  {
    if (pagesetterCreateFieldType($dbconn, $pubFieldsTable, $pubFieldsColumn, $tid, $record) == false)
      return false;
  }

  return $tid;
}


function pagesetter_adminapi_updatePublicationType($args)
{
  if (!isset($args['publication']))
    return pagesetterErrorAPI(__FILE__, __LINE__, "Missing argument 'publication' in 'pagesetter_adminapi_updatePublicationType'");
  if (!isset($args['fields']))
    return pagesetterErrorAPI(__FILE__, __LINE__, "Missing argument 'fields' in 'pagesetter_adminapi_updatePublicationType'");
  if (!isset($args['tid']))
    return pagesetterErrorAPI(__FILE__, __LINE__, "Missing argument 'tid' in 'pagesetter_adminapi_updatePublicationType'");

  $pubTypeData    = $args['publication'];
  $pubFieldsData  = $args['fields'];
  $tid            = $args['tid'];

    // Check access
  if (!pnSecAuthAction(0, 'pagesetter::', "$tid::", ACCESS_ADMIN))
    return pagesetterErrorAPI(__FILE__, __LINE__, _PGNOAUTH);

  if (!pagesetterCheckPageableFields($pubFieldsData))
    return pagesetterErrorAPI(null,null, _PGONLYONEPAGEABLE);

  list($dbconn) = pnDBGetConn();
  $pntable = pnDBGetTables();

    // Update basic information

  $pubTypesTable = $pntable['pagesetter_pubtypes'];
  $pubTypesColumn = &$pntable['pagesetter_pubtypes_column'];

  if (empty($pubTypeData['filename']))
    $pubTypeData['filename'] = $pubTypeData['title'];

  if (empty($pubTypeData['formname']))
    $pubTypeData['formname'] = $pubTypeData['filename'];

  $sql = "UPDATE $pubTypesTable SET
           $pubTypesColumn[title] = '" . pnVarPrepForStore($pubTypeData['title']) . "',
           $pubTypesColumn[filename] = '" . pnVarPrepForStore($pubTypeData['filename']) . "',
           $pubTypesColumn[formname] = '" . pnVarPrepForStore($pubTypeData['formname']) . "',
           $pubTypesColumn[description] = '" . pnVarPrepForStore($pubTypeData['description']) . "',
           $pubTypesColumn[listCount] = " . (int)$pubTypeData['listCount'] . ",
           $pubTypesColumn[sortField1] = '" . pnVarPrepForStore($pubTypeData['sortField1']) . "',
           $pubTypesColumn[sortDesc1] = " . (int)$pubTypeData['sortDesc1'] . ",
           $pubTypesColumn[sortField2] = '" . pnVarPrepForStore($pubTypeData['sortField2']) . "',
           $pubTypesColumn[sortDesc2] = " . (int)$pubTypeData['sortDesc2'] . ",
           $pubTypesColumn[sortField3] = '" . pnVarPrepForStore($pubTypeData['sortField3']) . "',
           $pubTypesColumn[sortDesc3] = " . (int)$pubTypeData['sortDesc3'] . ",
           $pubTypesColumn[defaultFilter] = '" . pnVarPrepForStore($pubTypeData['defaultFilter']) . "',
           $pubTypesColumn[enableHooks] = " . (int)$pubTypeData['enableHooks'] . ",
           $pubTypesColumn[workflow] = '" . pnVarPrepForStore($pubTypeData['workflow']) . "',
           $pubTypesColumn[enableRevisions] = " . (int)$pubTypeData['enableRevisions'] . ",
           $pubTypesColumn[enableEditOwn] = " . (int)$pubTypeData['enableEditOwn'] . ",
           $pubTypesColumn[enableTopicAccess] = " . (int)$pubTypeData['enableTopicAccess'] . ",
           $pubTypesColumn[defaultFolder] = " . (int)$pubTypeData['defaultFolder'] . ",
           $pubTypesColumn[defaultSubFolder] = '" . pnVarPrepForStore($pubTypeData['defaultSubFolder']) . "',
           $pubTypesColumn[defaultFolderTopic] = " . (int)$pubTypeData['defaultFolderTopic'] . "
         WHERE $pubTypesColumn[id] = '" . pnVarPrepForStore($tid) . "'";

  //echo "<pre>$sql</pre>"; exit(0);
  $result = $dbconn->execute($sql);

  if ($dbconn->errorNo() != 0)
    return pagesetterErrorAPI(__FILE__, __LINE__, '"updatePublicationType" failed: ' 
                                                  . $dbconn->errorMsg() . " while executing: $sql");

    // Update fields

  $pubFieldsTable = $pntable['pagesetter_pubfields'];
  $pubFieldsColumn = &$pntable['pagesetter_pubfields_column'];

  foreach ($pubFieldsData as $record)
  {
      // (missing ID means it haven't been created yet)
    if (isset($record['id']))
    {
      if (pagesetterUpdateFieldType($dbconn, $pubFieldsTable, $pubFieldsColumn, $tid, $record) == false)
        return false;
    }
    else
    {
      if (pagesetterCreateFieldType($dbconn, $pubFieldsTable, $pubFieldsColumn, $tid, $record) == false)
        return false;
    }

  } 
    // Removed deleted fields from database schema

  $deletedFields = (isset($args['deletedFields']) ? $args['deletedFields'] : array());
  foreach ($deletedFields as $field)
  {
    $ok = pnModAPIFunc( 'pagesetter',
                        'admin',
                        'deletePublicationFieldType',
                        $field );
    if (!$ok)
      return false;
  } 
  $smarty = new pnRender('pagesetter');
  $smarty->clear_all_cache();

  return true;
}


function pagesetter_adminapi_deletePublicationFieldType($args)
{
  if (!isset($args['ftid']))
    return pagesetterErrorAPI(__FILE__, __LINE__, "Missing argument 'ftid' in 'pagesetter_adminapi_deletePublicationFieldType'");
  if (!isset($args['tid']))
    return pagesetterErrorAPI(__FILE__, __LINE__, "Missing argument 'tid' in 'pagesetter_adminapi_deletePublicationFieldType'");

  $ftid = $args['ftid'];
  $tid  = $args['tid'];

    // Check access
  if (!pnSecAuthAction(0, 'pagesetter::', "$tid::", ACCESS_ADMIN))
    return pagesetterErrorAPI(__FILE__, __LINE__, _PGNOAUTH);

  list($dbconn) = pnDBGetConn();
  $pntable = pnDBGetTables();

  $pubFieldsTable = $pntable['pagesetter_pubfields'];
  $pubFieldsColumn = &$pntable['pagesetter_pubfields_column'];
  
  if (!pagesetterDeleteFieldType($dbconn,$pubFieldsTable,$pubFieldsColumn,$tid,$ftid))
  	return false;

  $sql = pagesetterGetPubSchemaDeleteSQL($tid, $ftid);

  $result = $dbconn->execute($sql);

  if ($dbconn->errorNo() != 0)
    return pagesetterErrorAPI(__FILE__, __LINE__, '"deletePublicationFieldType" failed: ' 
                                                  . $dbconn->errorMsg() . " while executing: $sql");

  return true;
}


function pagesetter_adminapi_deletePublicationType($args)
{
    // Check access (do not depend on pub type ID - only real admin may delete a pub type)
  if (!pnSecAuthAction(0, 'pagesetter::', '::', ACCESS_ADMIN))
    return pagesetterErrorAPI(__FILE__, __LINE__, _PGNOAUTH);

  if (!isset($args['tid']))
    return pagesetterErrorAPI(__FILE__, __LINE__, "Missing argument 'tid' in 'pagesetter_adminapi_deletePublicationType'");

  $tid = $args['tid'];

  list($dbconn) = pnDBGetConn();
  $pntable = pnDBGetTables();

    // Do the database delete 
    
    // First delete publication fields (if these fails then at least the type still exists)
    // This is not done directly in the database because the OnFieldDeletedHandler 
    // has to be called for plugins

  $pubFieldsTable = $pntable['pagesetter_pubfields'];
  $pubFieldsColumn = &$pntable['pagesetter_pubfields_column'];

  $sql = "SELECT $pubFieldsColumn[id] FROM $pubFieldsTable
          WHERE $pubFieldsColumn[tid] = " . (int)$tid;

  $result = $dbconn->execute($sql);

  if ($dbconn->errorNo() != 0)
    return pagesetterErrorAPI(__FILE__, __LINE__, '"deletePublicationType" failed: ' 
                                                  . $dbconn->errorMsg() . " while executing: $sql");

  for (; !$result->EOF; $result->MoveNext())
  {
    $ftid = $result->fields[0];
    
    if (!pagesetterDeleteFieldType($dbconn,$pubFieldsTable,$pubFieldsColumn,$tid,$ftid)) 
    	return false;
  }
  
    // Delete publication type from database

  $pubTypesTable = $pntable['pagesetter_pubtypes'];
  $pubTypesColumn = &$pntable['pagesetter_pubtypes_column'];

  $sql = "DELETE FROM $pubTypesTable
          WHERE $pubTypesColumn[id] = " . (int)$tid;

  $dbconn->execute($sql);

  if ($dbconn->errorNo() != 0)
    return pagesetterErrorAPI(__FILE__, __LINE__, '"deletePublicationType" failed: ' 
                                                  . $dbconn->errorMsg() . " while executing: $sql");

    // Delete publication header info from database

  $pubHeaderTable = $pntable['pagesetter_pubheader'];
  $pubHeaderColumn = &$pntable['pagesetter_pubheader_column'];

  $sql = "DELETE FROM $pubHeaderTable
          WHERE $pubHeaderColumn[tid] = " . (int)$tid;

  $dbconn->execute($sql);

  if ($dbconn->errorNo() != 0)
    return pagesetterErrorAPI(__FILE__, __LINE__, '"deletePublicationType" failed: ' 
                                                  . $dbconn->errorMsg() . " while executing: $sql");

    // Delete revision info from database

  $revisionsTable = $pntable['pagesetter_revisions'];
  $revisionsColumn = &$pntable['pagesetter_revisions_column'];

  $sql = "DELETE FROM $revisionsTable
          WHERE $revisionsColumn[tid] = " . (int)$tid;

  $dbconn->execute($sql);

  if ($dbconn->errorNo() != 0)
    return pagesetterErrorAPI(__FILE__, __LINE__, '"deletePublicationType" failed: ' 
                                                  . $dbconn->errorMsg() . " while executing: $sql");

    // Delete workflow configurations from database

  $wfcfgTable = $pntable['pagesetter_wfcfg'];
  $wfcfgColumn = &$pntable['pagesetter_wfcfg_column'];

  $sql = "DELETE FROM $wfcfgTable
          WHERE $wfcfgColumn[tid] = " . (int)$tid;

  $dbconn->execute($sql);

  if ($dbconn->errorNo() != 0)
    return pagesetterErrorAPI(__FILE__, __LINE__, '"deletePublicationType" failed: ' 
                                                  . $dbconn->errorMsg() . " while executing: $sql");

    // Delete publication specific relations

  $dataTable = pagesetterGetPubTableName($tid);
  $sql = "DROP TABLE $dataTable";
  $dbconn->execute($sql);

  if ($dbconn->errorNo() != 0)
    return pagesetterErrorAPI(__FILE__, __LINE__, '"deletePublicationType" failed: ' 
                                                  . $dbconn->errorMsg() . " while executing: $sql");

  return true;
}


function pagesetterCreateFieldType(&$dbconn, $pubFieldsTable, $pubFieldsColumn, $tid, $fieldDef)
{
  $sql = "INSERT INTO $pubFieldsTable (
           $pubFieldsColumn[tid],
           $pubFieldsColumn[name],
           $pubFieldsColumn[title],
           $pubFieldsColumn[description],
           $pubFieldsColumn[type],
           $pubFieldsColumn[typeData],
           $pubFieldsColumn[isTitle],
           $pubFieldsColumn[isPageable],
           $pubFieldsColumn[isSearchable],
           $pubFieldsColumn[isMandatory],
           $pubFieldsColumn[lineno])
          VALUES (
            '" . pnVarPrepForStore($tid) . "',
            '" . pnVarPrepForStore($fieldDef['name']) . "',
            '" . pnVarPrepForStore($fieldDef['title']) . "',
            '" . pnVarPrepForStore($fieldDef['description']) . "',
            '" . pnVarPrepForStore($fieldDef['type']) . "',
            '" . pnVarPrepForStore($fieldDef['typeData']) . "',
            " . (int)$fieldDef['isTitle'] . ",
            " . (int)$fieldDef['isPageable'] . ",
            " . (int)$fieldDef['isSearchable'] . ",
            " . (int)$fieldDef['isMandatory'] . ",
            " . (int)$fieldDef['lineno'] . "
            )";

  $dbconn->execute($sql);

  if ($dbconn->errorNo() != 0)
    return pagesetterErrorAPI(__FILE__, __LINE__, '"pagesetterCreateFieldType" failed: ' 
                                                  . $dbconn->errorMsg() . " while executing: $sql");

  $ftid = $dbconn->Insert_ID();

  $sql = pagesetterGetPubSchemaCreateSQL($tid, $ftid, $fieldDef['type']);

  $dbconn->execute($sql);

  if ($dbconn->errorNo() != 0)
    return pagesetterErrorAPI(__FILE__, __LINE__, '"pagesetterCreateFieldType" failed: ' 
                                                  . $dbconn->errorMsg() . " while executing: $sql");
                                                  
  guppy_executeFieldTypeEvent ("$tid:$ftid",$fieldDef['type'],'OnFieldAdded',$fieldDef);
  
  // echo "<pre> CreateFieldType: ";print_r($fieldDef);echo "</pre>";
  
    // Maybe the extra data has changed. Store it.                                                
  $sql = "UPDATE $pubFieldsTable SET
           $pubFieldsColumn[typeData] = '" . pnVarPrepForStore($fieldDef['typeData']) . "'
         WHERE 
           $pubFieldsColumn[id] = " . (int)$ftid;

  // print_r($fieldDef);
  // echo "<pre>$sql\n</pre>";
  $result = $dbconn->execute($sql);

  if ($dbconn->errorNo() != 0)
    return pagesetterErrorAPI(__FILE__, __LINE__, '"pagesetterCreateFieldType" failed: ' 
                                                  . $dbconn->errorMsg() . " while executing: $sql");
  return true;
}


function pagesetterUpdateFieldType(&$dbconn, $pubFieldsTable, $pubFieldsColumn, $tid, $fieldDef)
{
    // Get the old field type
  $sql = "SELECT $pubFieldsColumn[type] " .
  		 "FROM $pubFieldsTable " .
  		 "WHERE $pubFieldsColumn[id] = " . (int)$fieldDef['id'];
  		 
  $result = $dbconn->execute($sql);

  if ($dbconn->errorNo() != 0)
    return pagesetterErrorAPI(__FILE__, __LINE__, '"pagesetterUpdateFieldType" failed: ' 
                                                  . $dbconn->errorMsg() . " while executing: $sql");
  $oldType = $result->fields[0];
     
    // call the appropiate handlers
  
  $fid = "$tid:$fieldDef[id]"; 
  
  if ($fieldDef['type'] !== $oldType) {
  	guppy_executeFieldTypeEvent ($fid,$oldType,'OnFieldDeleted',$fieldDef);
  	guppy_executeFieldTypeEvent ($fid,$fieldDef['type'],'OnFieldAdded',$fieldDef);
  } else {
  	guppy_executeFieldTypeEvent ($fid,$fieldDef['type'],'OnFieldUpdated',$fieldDef);
  }
  
  $sql = "UPDATE $pubFieldsTable SET
           $pubFieldsColumn[name] = '" . pnVarPrepForStore($fieldDef['name']) . "',
           $pubFieldsColumn[title] = '" . pnVarPrepForStore($fieldDef['title']) . "',
           $pubFieldsColumn[description] = '" . pnVarPrepForStore($fieldDef['description']) . "',
           $pubFieldsColumn[type] = '" . pnVarPrepForStore($fieldDef['type']) . "',
           $pubFieldsColumn[typeData] = '" . pnVarPrepForStore($fieldDef['typeData']) . "',
           $pubFieldsColumn[isTitle] = " . (int)$fieldDef['isTitle'] . ",
           $pubFieldsColumn[isPageable] = " . (int)$fieldDef['isPageable'] . ",
           $pubFieldsColumn[isSearchable] = " . (int)$fieldDef['isSearchable'] . ",
           $pubFieldsColumn[isMandatory] = " . (int)$fieldDef['isMandatory'] . ",
           $pubFieldsColumn[lineno] = " . (int)$fieldDef['lineno'] . "
         WHERE 
           $pubFieldsColumn[id] = " . (int)$fieldDef['id'];

  //print_r($fieldDef);
  //echo "<pre>$sql\n</pre>";
  $result = $dbconn->execute($sql);

  if ($dbconn->errorNo() != 0)
    return pagesetterErrorAPI(__FILE__, __LINE__, '"pagesetterUpdateFieldType" failed: ' 
                                                  . $dbconn->errorMsg() . " while executing: $sql");

  $sql = pagesetterGetPubSchemaUpdateSQL($tid, $fieldDef['id'], $fieldDef['type']);

    // Change database schema for new field types
  $result = $dbconn->execute($sql);

  if ($dbconn->errorNo() != 0)
    return pagesetterErrorAPI(__FILE__, __LINE__, '"pagesetterUpdateFieldType" failed: ' 
                                                  . $dbconn->errorMsg() . " while executing: $sql");

  return true;
}

function pagesetterDeleteFieldType(&$dbconn, $pubFieldsTable, $pubFieldsColumn, $tid, $ftid)
{

  // Fetch fields information

  $sql = "SELECT $pubFieldsColumn[id],
                 $pubFieldsColumn[name],
                 $pubFieldsColumn[title],
                 $pubFieldsColumn[description],
                 $pubFieldsColumn[type],
                 $pubFieldsColumn[typeData],
                 $pubFieldsColumn[isTitle],
                 $pubFieldsColumn[isPageable],
                 $pubFieldsColumn[isSearchable],
                 $pubFieldsColumn[isMandatory],
                 $pubFieldsColumn[lineno]
          FROM   $pubFieldsTable
          WHERE  $pubFieldsColumn[tid] = " . $tid . " AND $pubFieldsColumn[id] = " . (int)$ftid;

  $result = $dbconn->execute($sql);

  if ($dbconn->errorNo() != 0)
    return pagesetterErrorAPI(__FILE__, __LINE__, '"getPublTypeInfo" failed: '
                                                  . $dbconn->errorMsg() . " while executing: $sql");

  $return = $dbconn->execute($sql);

  if ($dbconn->errorNo() != 0)
    return pagesetterErrorAPI(__FILE__, __LINE__, '"pagesetterDeleteFieldType" failed: ' 
                                                  . $dbconn->errorMsg() . " while executing: $sql");
  
  if (!$return->EOF)
  {
    $field = array('id'           => $result->fields[0],
                    'name'         => $result->fields[1],
                    'title'        => $result->fields[2],
                    'description'  => $result->fields[3],
                    'type'         => $result->fields[4],
                    'typeData'     => $result->fields[5],
                    'isTitle'      => intval($result->fields[6]),
                    'isPageable'   => intval($result->fields[7]),
                    'isSearchable' => intval($result->fields[8]),
                    'isMandatory'  => intval($result->fields[9]),
                    'lineno'       => intval($result->fields[10]));

    $fieldType = $field['type'];
  } else {
  	return false;
  }

    // Delete the field
  $sql = "DELETE FROM $pubFieldsTable
          WHERE  $pubFieldsColumn[id] = " . (int)$ftid;

  $dbconn->execute($sql);

  if ($dbconn->errorNo() != 0)
    return pagesetterErrorAPI(__FILE__, __LINE__, '"pagesetterDeleteFieldType" failed: ' 
                                                  . $dbconn->errorMsg() . " while executing: $sql");

  guppy_executeFieldTypeEvent ("$tid:$ftid",$fieldType,'OnFieldDeleted',$field);
  
  return true;  
}

// =======================================================================
// Publication dynamic table handling
// =======================================================================

function pagesetterGetPubColumnType($type)
{
  $typeInfo = pagesetterFieldTypesGet($type);

  return $typeInfo['sqlType'];
}


function pagesetterGetPubSchemaCreateSQL($tid, $ftid, $type)
{
    // Get publication/field type relation names
  $pubDataTableName = pagesetterGetPubTableName($tid);
  $pubDataColumnName = pagesetterGetPubColumnName($ftid);

  $schemaSQL =   "ALTER TABLE " . pnVarPrepForStore($pubDataTableName)
               . " ADD COLUMN " . pnVarPrepForStore($pubDataColumnName)
               . ' ' . pagesetterGetPubColumnType($type);

  return $schemaSQL;
}


function pagesetterGetPubSchemaUpdateSQL($tid, $ftid, $type)
{
    // Get publication/field type relation names
  $pubDataTableName = pagesetterGetPubTableName($tid);
  $pubDataColumnName = pagesetterGetPubColumnName($ftid);

  $schemaSQL =   'ALTER TABLE ' . pnVarPrepForStore($pubDataTableName)
               . ' CHANGE COLUMN ' . pnVarPrepForStore($pubDataColumnName)
               . ' ' . pnVarPrepForStore($pubDataColumnName) . ' ' . pagesetterGetPubColumnType($type);

  return $schemaSQL;
}


function pagesetterGetPubSchemaDeleteSQL($tid, $ftid)
{
    // Get publication/field type relation names
  $pubDataTableName = pagesetterGetPubTableName($tid);
  $pubDataColumnName = pagesetterGetPubColumnName($ftid);

  $schemaSQL =   "ALTER TABLE " . pnVarPrepForStore($pubDataTableName)
               . " DROP COLUMN " . pnVarPrepForStore($pubDataColumnName);

  return $schemaSQL;
}


// =======================================================================
// List / Category handling
// =======================================================================

function pagesetter_adminapi_getLists($args)
{
    // Check access
  if (!pnSecAuthAction(0, 'pagesetter::', '::', ACCESS_READ))
    return pagesetterErrorAPI(__FILE__, __LINE__, _PGNOAUTH);

  list($dbconn) = pnDBGetConn();
  $pntable = pnDBGetTables();

  $listsTable = $pntable['pagesetter_lists'];
  $listsColumn = &$pntable['pagesetter_lists_column'];

  $sql = "SELECT $listsColumn[id],
                 $listsColumn[title],
                 $listsColumn[authorID],
                 $listsColumn[created]
          FROM   $listsTable
          ORDER By $listsColumn[title]";

  $result = $dbconn->execute($sql);

  if ($dbconn->errorNo() != 0)
    return pagesetterErrorAPI(__FILE__, __LINE__, '"getLists" failed: '
                                                  . $dbconn->errorMsg() . " while executing: $sql");

  $lists = array();

  for (; !$result->EOF; $result->MoveNext())
  {
    $lists[] = array('id'          => $result->fields[0],
                     'title'       => $result->fields[1],
                     'authorID'    => $result->fields[2],
                     'author'      => pnUserGetVar('uname', $result->fields[2]),
                     'created'     => $result->fields[3]);
  }

  $result->Close();

  return $lists;
}


function pagesetter_adminapi_getList($args)
{
  if (!isset($args['lid']) || $args['lid'] == '')
    return pagesetterErrorAPI(__FILE__, __LINE__, "Missing argument 'lid' in 'pagesetter_adminapi_getList'");

  $lid           = $args['lid'];
  $forSelectList = array_key_exists('forSelectList',$args) ? $args['forSelectList'] : null;
  $topListValueID = array_key_exists('topListValueID',$args) ? $args['topListValueID'] : null;

    // Check access
  if (!pnSecAuthAction(0, 'pagesetter::', '::', ACCESS_READ))
    return pagesetterErrorAPI(__FILE__, __LINE__, _PGNOAUTH);

  list($dbconn) = pnDBGetConn();
  $pntable = pnDBGetTables();

  $listsTable = $pntable['pagesetter_lists'];
  $listsColumn = &$pntable['pagesetter_lists_column'];

  $sql = "SELECT $listsColumn[id],
                 $listsColumn[title],
                 $listsColumn[description],
                 $listsColumn[authorID],
                 $listsColumn[created]
          FROM   $listsTable
          WHERE  $listsColumn[id] = " . (int)$lid;

  $result = $dbconn->execute($sql);

  if ($dbconn->errorNo() != 0)
    return pagesetterErrorAPI(__FILE__, __LINE__, '"getList" failed: '
                                                  . $dbconn->errorMsg() . " while executing: $sql");
  if ($result->EOF)
    return pagesetterErrorAPI(__FILE__, __LINE__, "Unknown category ID '$lid'");

  $list = array('id'          => $result->fields[0],
                'title'       => $result->fields[1],
                'description' => $result->fields[2],
                'authorID'    => $result->fields[3],
                'author'      => pnUserGetVar('uname', $result->fields[3]),
                'created'     => $result->fields[4]);

  $result->Close();

    // Get items

  $listItemsTable = $pntable['pagesetter_listitems'];
  $listItemsColumn = &$pntable['pagesetter_listitems_column'];

  if (!empty($topListValueID)  &&  $topListValueID != 'top')
  {
    $topJoin = "LEFT JOIN $listItemsTable as topItemTable ON topItemTable.pg_id = '" . pnVarPrepForStore($topListValueID) . "'";

    $topWhere = "AND $listItemsColumn[lval] >= topItemTable.pg_lval AND $listItemsColumn[rval] <= topItemTable.pg_rval";
  }
  else
  {
    $topJoin = '';
    $topWhere = '';
  }

  $sql = "SELECT $listItemsColumn[id],
                 $listItemsColumn[parentID],
                 $listItemsColumn[title],
                 $listItemsColumn[fullTitle],
                 $listItemsColumn[value],
                 $listItemsColumn[description],
                 $listItemsColumn[lineno],
                 $listItemsColumn[indent]
          FROM   $listItemsTable
          $topJoin
          WHERE  $listItemsColumn[lid] = " . (int)$lid . "
          $topWhere
          ORDER BY $listItemsColumn[lineno]";

  //echo "<pre>$sql</pre>\n";
  
  $result = $dbconn->execute($sql);

  if ($dbconn->errorNo() != 0)
    return pagesetterErrorAPI(__FILE__, __LINE__, '"getList" failed: '
                                                  . $dbconn->errorMsg() . " while executing: $sql");
  $items = array();

  if ($forSelectList)
    $items[] = array('title'       => '',
                     'value'       => 0);

  for (; !$result->EOF; $result->MoveNext())
  {
    if ($forSelectList)
    {
      $t = ereg_replace('[^:]*:', '+ ', $result->fields[3]);
      $items[] = array('title'       => $t,
                       'value'       => intval($result->fields[0]));
    }
    else
      $items[] = array('id'          => intval($result->fields[0]),
                       'parentID'    => intval($result->fields[1]),
                       'title'       => $result->fields[2],
                       'fullTitle'   => $result->fields[3],
                       'value'       => $result->fields[4],
                       'description' => $result->fields[5],
                       'lineno'      => intval($result->fields[6]),
                       'indent'      => intval($result->fields[7]));
  }

  $result->Close();
  
  return array('list' => $list, 'items' => $items);
}


/* test
$items = array
(
  array('s' => 'a', 'indent' => 0),
    array('s' => 'b', 'indent' => 1),
    array('s' => 'c', 'indent' => 1),
  array('s' => 'd', 'indent' => 0),
    array('s' => 'e', 'indent' => 1),
      array('s' => 'f', 'indent' => 2),
  array('s' => 'g', 'indent' => 0),
);

$tree = pagesetterFlat2nestedTree($items);
print_r($tree);
*/

  // Convert a flat table of indented tree data to a recursive data structure
function pagesetter_adminapi_flat2nestedTree($args)
{
  if (!isset($args['items']))
    return pagesetterErrorAPI(__FILE__, __LINE__, "Missing argument 'items' in 'pagesetter_adminapi_flat2nestedTree'");

  $items = $args['items'];

  $offset = 0;
  return pagesetterFlat2nestedTree_rec($items, count($items), $offset);
}


function pagesetterFlat2nestedTree_rec($items, $size, &$offset)
{
  $nodes = array();

  $indent = $items[$offset]['indent'];
  //echo "$offset: $indent / " . $items[$offset]['s'] . "<br>\n";

  while ($offset<$size && $items[$offset]['indent'] >= $indent)
  {
    if ($items[$offset]['indent'] == $indent)
    {
      $nodes[] = array('item' => $items[$offset], 'nodes' => array() );
      ++$offset;
    }
    else
      $nodes[count($nodes)-1]['nodes'] = pagesetterFlat2nestedTree_rec($items, $size, $offset);
  }

  return $nodes;
}


function pagesetter_adminapi_createList($args)
{
  if (!isset($args['list']) || $args['list'] == '')
    return pagesetterErrorAPI(__FILE__, __LINE__, "Missing argument 'list' in 'pagesetter_adminapi_createList'");
  if (!isset($args['items']) || $args['items'] == '')
    return pagesetterErrorAPI(__FILE__, __LINE__, "Missing argument 'items' in 'pagesetter_adminapi_createList'");
  if (!isset($args['authorID']) || $args['authorID'] == '')
    return pagesetterErrorAPI(__FILE__, __LINE__, "Missing argument 'authorID' in 'pagesetter_adminapi_createList'");

  $list     = $args['list'];
  $items    = $args['items'];     // Recursive representation!
  $authorID = $args['authorID'];

    // Check access
  if (!pnSecAuthAction(0, 'pagesetter::', "::", ACCESS_ADMIN))
    return pagesetterErrorAPI(__FILE__, __LINE__, _PGNOAUTH);

  list($dbconn) = pnDBGetConn();
  $pntable = pnDBGetTables();

  $listsTable = $pntable['pagesetter_lists'];
  $listsColumn = &$pntable['pagesetter_lists_column'];

  $sql = "INSERT INTO $listsTable (
            $listsColumn[authorID],
            $listsColumn[created],
            $listsColumn[title],
            $listsColumn[description])
          VALUES (
            " . (int)$authorID . ",
            NOW(),
            '" . pnVarPrepForStore($list[title]) . "',
            '" . pnVarPrepForStore($list[description]) . "')";

  $result = $dbconn->execute($sql);

  if ($dbconn->errorNo() != 0)
    return pagesetterErrorAPI(__FILE__, __LINE__, '"createList" failed: ' 
                                                  . $dbconn->errorMsg() . " while executing: $sql");

  $lid = $dbconn->Insert_ID();

    // Add left/right values from depth-first traversal for easy sub-list detection
  pagesetterCalculateLRValues($items);

  if (!pagesetterUpdateListItems($dbconn, $pntable, $items, $lid))
    return false;

  return $lid;
}


function pagesetter_adminapi_updateList($args)
{
  if (!isset($args['list']) || $args['list'] == '')
    return pagesetterErrorAPI(__FILE__, __LINE__, "Missing argument 'list' in 'pagesetter_adminapi_updateList'");
  if (!isset($args['items']) || $args['items'] == '')
    return pagesetterErrorAPI(__FILE__, __LINE__, "Missing argument 'items' in 'pagesetter_adminapi_updateList'");
  if (!isset($args['lid']) || $args['lid'] == '')
    return pagesetterErrorAPI(__FILE__, __LINE__, "Missing argument 'lid' in 'pagesetter_adminapi_updateList'");

  $list         = $args['list'];
  $items        = $args['items'];
  $deletedItems = $args['deletedItems'];
  $lid          = $args['lid'];

    // Check access
  if (!pnSecAuthAction(0, 'pagesetter::', "::", ACCESS_ADMIN))
    return pagesetterErrorAPI(__FILE__, __LINE__, _PGNOAUTH);

  list($dbconn) = pnDBGetConn();
  $pntable = pnDBGetTables();

  $listsTable = $pntable['pagesetter_lists'];
  $listsColumn = &$pntable['pagesetter_lists_column'];

  $sql = "UPDATE $listsTable SET
            $listsColumn[title] = '" . pnVarPrepForStore($list[title]) . "',
            $listsColumn[description] = '" . pnVarPrepForStore($list[description]) . "'
          WHERE $listsColumn[id] = " . (int)$lid;

  $result = $dbconn->execute($sql);

  if ($dbconn->errorNo() != 0)
    return pagesetterErrorAPI(__FILE__, __LINE__, '"updateList" failed: ' 
                                                  . $dbconn->errorMsg() . " while executing: $sql");   

    // Add left/right values from depth-first traversal for easy sub-list detection
  pagesetterCalculateLRValues($items);

    // Update changes in database
  pagesetterUpdateListItems($dbconn, $pntable, $items, $lid);

    // Remove deleted items from database

  $listItemsTable = $pntable['pagesetter_listitems'];
  $listItemsColumn = &$pntable['pagesetter_listitems_column'];

  if (count($deletedItems) > 0)
  {
    $deletedItems = array_map("pnVarPrepForStore", $deletedItems);
    $inList = implode("','", $deletedItems);
    $sql = "DELETE FROM $listItemsTable
            WHERE $listItemsColumn[id] in ('$inList')
              AND $listItemsColumn[lid] = " . (int)$lid;
  
    $result = $dbconn->execute($sql);

    if ($dbconn->errorNo() != 0)
      return pagesetterErrorAPI(__FILE__, __LINE__, '"updateList" failed: ' 
                                                    . $dbconn->errorMsg() . " while executing: $sql");   
  }

    // A list may be included in a dropdown somewhere, so make sure that is updated
  $smarty = new pnRender('pagesetter');
  $smarty->clear_all_cache();

  return true;
}


function pagesetter_adminapi_deleteList($args)
{
  if (!isset($args['lid']) || $args['lid'] == '')
    return pagesetterErrorAPI(__FILE__, __LINE__, "Missing argument 'lid' in 'pagesetter_adminapi_deleteList'");

  $lid = $args['lid'];

    // Check access
  if (!pnSecAuthAction(0, 'pagesetter::', "::", ACCESS_ADMIN))
    return pagesetterErrorAPI(__FILE__, __LINE__, _PGNOAUTH);

  list($dbconn) = pnDBGetConn();
  $pntable = pnDBGetTables();

  $listsTable = $pntable['pagesetter_lists'];
  $listsColumn = &$pntable['pagesetter_lists_column'];

  $sql = "DELETE FROM $listsTable
          WHERE  $listsColumn[id] = " . (int)$lid;

  $result = $dbconn->execute($sql);

  if ($dbconn->errorNo() != 0)
    return pagesetterErrorAPI(__FILE__, __LINE__, '"deleteList" failed: ' 
                                                  . $dbconn->errorMsg() . " while executing: $sql");
  $listItemsTable = $pntable['pagesetter_listitems'];
  $listItemsColumn = &$pntable['pagesetter_listitems_column'];

  $sql = "DELETE FROM $listItemsTable
          WHERE $listItemsColumn[lid] = " . (int)$lid;

  $result = $dbconn->execute($sql);

  if ($dbconn->errorNo() != 0)
    return pagesetterErrorAPI(__FILE__, __LINE__, '"deleteList" failed: ' 
                                                  . $dbconn->errorMsg() . " while executing: $sql");

  return true;
}


function pagesetterCalculateLRValues(&$items)
{
  pagesetterCalculateLRValues_rec($items, 0);
}


function pagesetterCalculateLRValues_rec(&$items, $val)
{
  for ($i=0, $size=count($items); $i<$size; ++$i)
  {
    $item = &$items[$i];

    $item['item']['lval'] = $val++;
    $val = pagesetterCalculateLRValues_rec($item['nodes'], $val);
    $item['item']['rval'] = $val++;
  }

  return $val;
}


function pagesetterUpdateListItems(&$dbconn, $pntable, $items, $lid)
{
  $listItemsTable = $pntable['pagesetter_listitems'];
  $listItemsColumn = &$pntable['pagesetter_listitems_column'];

  return pagesetterUpdateListItems_rec($dbconn, $listItemsTable, $listItemsColumn, $items, $lid, -1);
}


function pagesetterUpdateListItems_rec(&$dbconn, $listItemsTable, $listItemsColumn, $items, $lid, $parentID)
{
  for ($i=0, $size=count($items); $i<$size; ++$i)
  {
    $itemInfo = $items[$i];
    $item     = $itemInfo['item'];

      // Create SQL - if item exists (has ID) do an update otherwise create it
    if (isset($item['id']))
      $sql = "UPDATE $listItemsTable SET
                $listItemsColumn[parentID] = " . (int)$parentID . ",
                $listItemsColumn[title] = '" . pnVarPrepForStore($item['title']) . "',
                $listItemsColumn[fullTitle] = '" . pnVarPrepForStore($item['fullTitle']) . "',
                $listItemsColumn[value] = '" . pnVarPrepForStore($item['value']) . "',
                $listItemsColumn[description] = '" . pnVarPrepForStore($item['description']) . "',
                $listItemsColumn[lineno] = " . (int)$item['lineno'] . ",
                $listItemsColumn[indent] = " . (int)$item['indent'] . ",
                $listItemsColumn[lval] = " . (int)$item['lval'] . ",
                $listItemsColumn[rval] = " . (int)$item['rval'] . "
              WHERE
                $listItemsColumn[id] = " . (int)$item['id'];
    else              
      $sql = "INSERT INTO $listItemsTable (
                $listItemsColumn[lid],
                $listItemsColumn[parentID],
                $listItemsColumn[title],
                $listItemsColumn[fullTitle],
                $listItemsColumn[value],
                $listItemsColumn[description],
                $listItemsColumn[lineno],
                $listItemsColumn[indent],
                $listItemsColumn[lval],
                $listItemsColumn[rval])
              VALUES (
                " . (int)$lid . ",
                " . (int)$parentID . ",
                '" . pnVarPrepForStore($item['title']) . "',
                '" . pnVarPrepForStore($item['fullTitle']) . "',
                '" . pnVarPrepForStore($item['value']) . "',
                '" . pnVarPrepForStore($item['description']) . "',
                " . (int)$item['lineno'] . ",
                " . (int)$item['indent'] . ",
                " . (int)$item['lval'] . ",
                " . (int)$item['rval'] . ")";

    $result = $dbconn->execute($sql);

    if ($dbconn->errorNo() != 0)
      return pagesetterErrorAPI(__FILE__, __LINE__, '"updateListItems_rec" failed: ' 
                                                    . $dbconn->errorMsg() . " while executing: $sql");

    if (isset($item['id']))
      $thisID = $item['id'];
    else
      $thisID = $dbconn->insert_ID();

    if (!pagesetterUpdateListItems_rec($dbconn, $listItemsTable, $listItemsColumn, $itemInfo['nodes'], $lid, $thisID))
      return false;
  }

  return true;
}


/**
 * pagesetter_adminapi_createTemplate()
 * 
 * Returns a generic template for a given publication
 * 
 * @author Jörg Napp
 * @param $args['tid'] the publication type id
 * @param $args['format'] the format of the template
 * @return the template
 **/
function pagesetter_adminapi_createTemplate($args)
{ 
    // Check access
    if (!pnSecAuthAction(0, 'pagesetter::', '::', ACCESS_ADMIN)) {
        return pagesetterErrorAPI(__FILE__, __LINE__, _PGNOAUTH);
    }

    // check parameters
    if (!isset($args['tid'])) {
        return pagesetterErrorAPI(__FILE__, __LINE__, "Missing argument 'tid' in 'pagesetter_adminapi_createTemplate'");
    }

    if (!isset($args['format'])) {
        return pagesetterErrorAPI(__FILE__, __LINE__, "Missing argument 'format' in 'pagesetter_adminapi_createTemplate'");
    }

    $format = $args['format'];
    $tid = $args['tid'];

    // get the field information
    $pubinfo = pnModAPIFunc('pagesetter',
                            'admin',
                            'getPubTypeInfo',
                            compact('tid'));

    // create and return the template from the base templates                            
    $smarty =& new pnRender('pagesetter');
    $smarty->caching = false;
    $smarty->assign('publication', $pubinfo['publication']);
    $smarty->assign('fields', $pubinfo['fields']);
    return $smarty->fetch("__template-$format.html");
} 


/**
 * pagesetter_adminapi_createTemplate()
 * 
 * Creates a generic template for a given publication and filename
 **/
function pagesetter_adminapi_createTemplateFile($args)
{ 
  if (!isset($args['filename']))
    return pagesetterErrorAPI(__FILE__, __LINE__, "Missing argument 'filename' in 'pagesetter_adminapi_createTemplateFile'");

  $templateFilename = $args['filename'];
  $format           = $args['format'];

  $template = pnModAPIFunc('pagesetter', 'admin', 'createTemplate', $args);
  if ($template === false)
    return false;
  
  $fname = "modules/pagesetter/pntemplates/$templateFilename-$format.html";

  $handle = fopen($fname, 'w');
  if (!$handle)
    return pagesetterErrorAPI(__FILE__, __LINE__, "Cannot write the template file '$fname'");

  fwrite($handle, $template);
  fclose($handle);                        
  chmod($fname, 0766);

  return true;
}

?>
