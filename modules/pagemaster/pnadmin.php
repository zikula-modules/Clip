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
require_once("modules/pagesetter/common-edit.php");

// =======================================================================
// Admin main function
// =======================================================================

function pagesetter_admin_main()
{
  return pagesetter_admin_pubtypes();
}


function pagesetter_admin_config()
{
    // Check access
  if (!pnSecAuthAction(0, 'pagesetter::', '::', ACCESS_ADMIN))
    return pagesetterErrorPage(__FILE__, __LINE__, _PGNOAUTH);

  if (!pnModAPILoad('pagesetter', 'admin'))
    return pagesetterErrorPage(__FILE__, __LINE__, 'Failed to load Pagesetter admin API');

  require_once "modules/pagesetter/forms/configHandler.php";
  $handler = new ConfigHandler();

  if (!guppy_decode($handler))
  {
    $pubTypesData = pnModAPIFunc('pagesetter',
                                 'admin',
                                 'getPublicationTypes');

    $pubTypes = array();
    foreach ($pubTypesData as $pubType)
      $pubTypes[] = array( 'title' => $pubType['title'],
                           'value' => $pubType['id'] );

    $pubTypeOptions = array( 'pubTypes' => $pubTypes );

    $uploadDir = pnModGetVar('pagesetter','uploadDir');
    if ($uploadDir == ''  ||  !is_string($uploadDir))
      $uploadDir = 'pnTemp';

    $configData = array( 'pubType'             => pnModGetVar('pagesetter','frontpagePubType'),
                         'PagesetterBaseDir'   => dirname(__FILE__),
                         'uploadDir'           => $uploadDir,
                         'uploadDirDocs'       => pnModGetVar('pagesetter','uploadDirDocs'),
                         'autofillPublishDate' => pnModGetVar('pagesetter','autofillPublishDate'),
                         'htmlAreaStyled'      => guppy_getSetting('htmlAreaStyled'),
                         'htmlAreaWordKill'    => guppy_getSetting('htmlAreaWordKill'),
                         'htmlAreaUndo'        => guppy_getSetting('htmlAreaUndo'),
                         'htmlAreaEnabled'     => guppy_getSetting('htmlAreaEnabled') );

    $data = array( 'config' => array( 'rows' => array( $configData ) ) );

    guppy_open( array( 'specFile'    => 'modules/pagesetter/forms/configSpec.xml',
                       'layoutFile'  => 'modules/pagesetter/forms/configLayout.xml',
                       'toolbarFile' => 'modules/pagesetter/forms/adminToolbar.xml',
                       'data'        => $data,
                       'options'     => $pubTypeOptions,
                       'actionURL'   => pnModUrl('pagesetter','admin','config') ) );

  }

  return guppy_output();
}


// =======================================================================
// Publication types list
// =======================================================================
function pagesetter_admin_pubtypes()
{
    // Check access
  if (!pnSecAuthAction(0, 'pagesetter::', '::', ACCESS_ADMIN))
    return pagesetterErrorPage(__FILE__, __LINE__, _PGNOAUTH);

  if (!pnModAPILoad('pagesetter', 'admin'))
    return pagesetterErrorPage(__FILE__, __LINE__, 'Failed to load Pagesetter admin API');

  require_once "modules/pagesetter/forms/pubTypesHandler.php";
  $handler = new PublicationTypesHandler();

  if (!guppy_decode($handler))
  {
    require_once 'modules/pagesetter/guppy/guppy_parser.php';

    $pubTypesData = pnModAPIFunc('pagesetter',
                                 'admin',
                                 'getPublicationTypes');
    $pubTypesData = array( 'rows' => $pubTypesData );

    $data = array( 'publicationTypes' => $pubTypesData );

    if (($layout=guppy_loadfile('pubtypes', 'modules/pagesetter/forms/pubTypesLayout.xml')) === false)
      return guppy_output();
    $layout = guppy_parseXMLLayout($layout);

    $textElement = &guppy_getLayoutElement('introtext');

    if (count($pubTypesData) > 0)
      $textElement['visible'] = false;

    guppy_open( array( 'specFile'    => 'modules/pagesetter/forms/pubTypesSpec.xml',
                       'rawLayout'   => $layout,
                       'toolbarFile' => 'modules/pagesetter/forms/adminToolbar.xml',
                       'data'        => $data,
                       'actionURL'   => pnModUrl('pagesetter','admin','pubtypes') ) );

  }

  return guppy_output();
}


// =======================================================================
// Publication type edit
// =======================================================================

function pagesetter_admin_pubtypeedit()
{
  $action = pnVarCleanFromInput('action');
  $tid    = pnVarCleanFromInput('tid');

  if (!pnModAPILoad('pagesetter', 'admin'))
    return pagesetterErrorPage(__FILE__, __LINE__, 'Failed to load Pagesetter admin API');

  require_once "modules/pagesetter/forms/pubTypeEditHandler.php";
  $handler = new PublicationTypeEditHandler();

  if (!guppy_decode($handler))
  {
      // Check access at this point where the ID is available
    if (!pnSecAuthAction(0, 'pagesetter::', "$tid::", ACCESS_ADMIN))
      return pagesetterErrorPage(__FILE__, __LINE__, _PGNOAUTH);

    if (!pnModAPILoad('pagesetter', 'workflow'))
      return pagesetterErrorPage(__FILE__, __LINE__, 'Failed to load Pagesetter admin API');

    if ($action == 'new')
    {
      $pubTypeData =    array( 'listCount' => 10 );
      $pubFieldsData =  array();
    }
    else
    {
        // Get publication type information

      $pubTypeInfo =  pnModAPIFunc( 'pagesetter',
                                    'admin',
                                    'getPubTypeInfo',
                                    array('tid' => $tid) );

      if ($pubTypeInfo === false)
        return pagesetterErrorApiGet();

      //echo "<pre>"; print_r($pubTypeInfo); echo "</pre>"; exit(0);

      $pubTypeData =    $pubTypeInfo['publication'];
      $pubFieldsData =  $pubTypeInfo['fields'];

        // Hack: join 'type' and 'typeData'
      for ($i=0,$s=count($pubFieldsData); $i<$s; ++$i)
      {
        $field = $pubFieldsData[$i];
        $typeInfo = $field['type'] . '|' . $field['typeData'];
        $pubFieldsData[$i]['type'] = $typeInfo;
      }

        // Extract user field list, add core fields (created, last updated, id), and make an option list from it
        // The list is used to fill "order by" selections

      $pntable = pnDBGetTables();
      $pubTable = pagesetterGetPubTableName($tid);
      $pubColumn = $pntable['pagesetter_pubdata_column'];

      $pubFieldOptions = array( array('title' => '_PGSORTCREATED',      'value' => $pubColumn['created']),
                                array('title' => '_PGSORTLASTUPDATED',  'value' => $pubColumn['lastUpdated']),
                                array('title' => '_PGSORTID',           'value' => $pubColumn['id']));

      foreach ($pubFieldsData as $field)
        $pubFieldOptions[] = array( 'title' => $field['title'],
                                    'value' => pagesetterGetPubColumnName($field['id']) );
    }

    $workflowObjects = pnModAPIFunc('pagesetter',  'workflow', 'getWorkflows');
    $workflows = array();

    foreach ($workflowObjects as $workflowID => $workflow)
      $workflows[] = array( 'title' => $workflow->getTitle(),
                            'value' => $workflowID );

    $data = array( 'publicationType'    => array( 'rows' => array($pubTypeData) ),
                   'publicationFields'  => array( 'rows' => $pubFieldsData ) );

    guppy_open( array( 'specFile'    => 'modules/pagesetter/forms/pubTypeEditSpec.xml',
                       'layoutFile'  => 'modules/pagesetter/forms/pubTypeEditLayout.xml',
                       'toolbarFile' => 'modules/pagesetter/forms/adminToolbar.xml',
                       'data'        => $data,
                       'options'     => array('pubFields'  => $pubFieldOptions,
                                              'fieldTypes' => pagesetterFieldTypesGetOptionList(),
                                              'workflows'  => $workflows),
                       'extra'       => array('deletedFields' => array(), 'action' => $action),
                       'actionURL'   => pnModUrl('pagesetter','admin','pubtypeedit') ) );

  }

  return guppy_output();
}


function pagesetter_admin_pubtypeadd1()
{
  if (!pnModAPILoad('pagesetter', 'admin'))
    return pagesetterErrorPage(__FILE__, __LINE__, 'Failed to load Pagesetter admin API');

  require_once "modules/pagesetter/forms/pubTypeAdd1Handler.php";
  $handler = new PublicationTypeAdd1Handler();

  if (!guppy_decode($handler))
  {
    if (!pnSecAuthAction(0, 'pagesetter::', "::", ACCESS_ADMIN))
      return pagesetterErrorPage(__FILE__, __LINE__, _PGNOAUTH);

    $pubTypeData =    array( 'listCount' => 10 );

    $data = array( 'publicationType' => array( 'rows' => array($pubTypeData) ) );

    guppy_open( array( 'specFile'    => 'modules/pagesetter/forms/pubTypeEditSpec.xml',
                       'layoutFile'  => 'modules/pagesetter/forms/pubTypeAdd1Layout.xml',
                       'toolbarFile' => 'modules/pagesetter/forms/adminToolbar.xml',
                       'data'        => $data,
                       'options'     => array(),
                       'actionURL'   => pnModUrl('pagesetter','admin','pubtypeadd1') ) );

  }

  return guppy_output();
}


function pagesetter_admin_pubtypeadd1b()
{
  $tid = pnVarCleanFromInput('tid');

  if (!pnModAPILoad('pagesetter', 'admin'))
    return pagesetterErrorPage(__FILE__, __LINE__, 'Failed to load Pagesetter admin API');

  require_once "modules/pagesetter/forms/pubTypeAdd1bHandler.php";
  $handler = new PublicationTypeAdd1bHandler();

  if (!guppy_decode($handler))
  {
    if (!pnSecAuthAction(0, 'pagesetter::', "::", ACCESS_ADMIN))
      return pagesetterErrorPage(__FILE__, __LINE__, _PGNOAUTH);

      // Get publication type information
    $pubTypeInfo =  pnModAPIFunc( 'pagesetter',
                                  'admin',
                                  'getPubTypeInfo',
                                  array('tid' => $tid) );

    if ($pubTypeInfo === false)
      return pagesetterErrorApiGet();

    //echo "<pre>"; print_r($pubTypeInfo); echo "</pre>"; exit(0);

    $pubTypeData   = $pubTypeInfo['publication'];
    $pubFieldsData =  array( array('name' => 'title',
                                   'title' => 'Title',
                                   'description' => 'Title of this publication',
                                   'type' => pagesetterFieldTypeString,
                                   'isTitle' => true,
                                   'isPageable' => false,
                                   'isSearchable' => true,
                                   'isMandatory' => true,
                                   'lineno' => 0) );

    $data = array( 'publicationType'    => array( 'rows' => array($pubTypeData) ),
                   'publicationFields'  => array( 'rows' => $pubFieldsData ) );

    guppy_open( array( 'specFile'    => 'modules/pagesetter/forms/pubTypeEditSpec.xml',
                       'layoutFile'  => 'modules/pagesetter/forms/pubTypeAdd1bLayout.xml',
                       'toolbarFile' => 'modules/pagesetter/forms/adminToolbar.xml',
                       'data'        => $data,
                       'options'     => array('fieldTypes' => pagesetterFieldTypesGetOptionList()),
                       'actionURL'   => pnModUrl('pagesetter','admin','pubtypeadd1b') ) );

  }

  return guppy_output();
}


function pagesetter_admin_pubtypeadd2()
{
  $tid = pnVarCleanFromInput('tid');

  if (!pnModAPILoad('pagesetter', 'admin'))
    return pagesetterErrorPage(__FILE__, __LINE__, 'Failed to load Pagesetter admin API');

  require_once "modules/pagesetter/forms/pubTypeAdd2Handler.php";
  $handler = new PublicationTypeAdd2Handler();

  if (!guppy_decode($handler))
  {
      // Check access at this point where the ID is available
    if (!pnSecAuthAction(0, 'pagesetter::', "$tid::", ACCESS_ADMIN))
      return pagesetterErrorPage(__FILE__, __LINE__, _PGNOAUTH);

      // Get publication type information
    $pubTypeInfo =  pnModAPIFunc( 'pagesetter',
                                  'admin',
                                  'getPubTypeInfo',
                                  array('tid' => $tid) );

    if ($pubTypeInfo === false)
      return pagesetterErrorApiGet();

    //echo "<pre>"; print_r($pubTypeInfo); echo "</pre>"; exit(0);

    $pubTypeData   = $pubTypeInfo['publication'];
    $pubFieldsData = $pubTypeInfo['fields'];

      // Extract user field list, add core fields (created, last updated, id), and make an option list from it
      // The list is used to fill "order by" selections

    $pntable = pnDBGetTables();
    $pubTable = pagesetterGetPubTableName($tid);
    $pubColumn = $pntable['pagesetter_pubdata_column'];

    $pubFieldOptions = array( array('title' => '_PGSORTCREATED',      'value' => $pubColumn['created']),
                              array('title' => '_PGSORTLASTUPDATED',  'value' => $pubColumn['lastUpdated']),
                              array('title' => '_PGSORTID',           'value' => $pubColumn['id']));

    foreach ($pubFieldsData as $field)
      $pubFieldOptions[] = array( 'title' => $field['title'],
                                  'value' => pagesetterGetPubColumnName($field['id']) );

      // Setup initial default data

    $pubTypeName = $pubTypeData['filename'];

    $pubTypeData['listGenerate'] = true;
    $pubTypeData['listTemplate'] = $pubTypeName . '-list.html';
    $pubTypeData['fullGenerate'] = true;
    $pubTypeData['fullTemplate'] = $pubTypeName . '-full.html';
    $pubTypeData['printGenerate'] = true;
    $pubTypeData['printTemplate'] = $pubTypeName . '-print.html';
    $pubTypeData['rssGenerate'] = true;
    $pubTypeData['rssTemplate'] = $pubTypeName . '-rss.html';
    $pubTypeData['blockGenerate'] = true;
    $pubTypeData['blockTemplate'] = $pubTypeName . '-block-list.html';
    $pubTypeData['sortField1'] = $pubColumn['created'];
    $pubTypeData['sortDesc1'] = true;

      // Hack: join 'type' and 'typeData'
    for ($i=0,$s=count($pubFieldsData); $i<$s; ++$i)
    {
      $field = $pubFieldsData[$i];
      $typeInfo = $field['type'] . '|' . $field['typeData'];
      $pubFieldsData[$i]['type'] = $typeInfo;
    }

    $data = array( 'publicationType'    => array( 'rows' => array($pubTypeData) ),
                   'publicationFields'  => array( 'rows' => $pubFieldsData ) );

    guppy_open( array( 'specFile'    => 'modules/pagesetter/forms/pubTypeAdd2Spec.xml',
                       'layoutFile'  => 'modules/pagesetter/forms/pubTypeAdd2Layout.xml',
                       'toolbarFile' => 'modules/pagesetter/forms/adminToolbar.xml',
                       'data'        => $data,
                       'options'     => array('pubFields'  => $pubFieldOptions,
                                              'fieldTypes' => pagesetterFieldTypesGetOptionList()),
                       'actionURL'   => pnModUrl('pagesetter','admin','pubtypeadd2') ) );

  }

  return guppy_output();
}


// =======================================================================
// Publication types list
// =======================================================================

function pagesetter_admin_lists()
{
    // Check access
  if (!pnSecAuthAction(0, 'pagesetter::', '::', ACCESS_ADMIN))
    return pagesetterErrorPage(__FILE__, __LINE__, _PGNOAUTH);

  if (!pnModAPILoad('pagesetter', 'admin'))
    return pagesetterErrorPage(__FILE__, __LINE__, 'Failed to load Pagesetter admin API');

  require_once "modules/pagesetter/forms/listsHandler.php";
  $handler = new ListsHandler();

  if (!guppy_decode($handler))
  {
    $listsData = pnModAPIFunc('pagesetter',
                              'admin',
                              'getLists');
    $listsData = array( 'rows' => $listsData );

    $data = array( 'lists' => $listsData );

    guppy_open( array( 'specFile'    => 'modules/pagesetter/forms/listsSpec.xml',
                       'layoutFile'  => 'modules/pagesetter/forms/listsLayout.xml',
                       'toolbarFile' => 'modules/pagesetter/forms/adminToolbar.xml',
                       'data'        => $data,
                       'actionURL'   => pnModUrl('pagesetter','admin','lists') ) );

  }

  return guppy_output();
}


// =======================================================================
// List edit
// =======================================================================

class CardTableHelper
{
  var $formName;
  var $entityName;
  var $cardName;
  var $tableName;

  function checkAccess() {}

  function getDefaultData() {}

  function getExistingData() {}

  function getExtraData()
  {
    return array();
  }

  function getActionURL() {}
}


function pagesetterCardTableHandler($helper)
{
  $action = pnVarCleanFromInput('action');
  $formName = $helper->formName;
  $entityName = $helper->entityName;

  require_once "modules/pagesetter/forms/{$formName}Handler.php";
  $handlerClassName = "{$formName}Handler";
  $handler = new $handlerClassName();

  if (!guppy_decode($handler))
  {
      // Check access at this point where the ID is available (check is done in handler when form is open)
    if (!$helper->checkAccess())
      return pagesetterErrorPage(__FILE__, __LINE__, _PGNOAUTH);

    if ($action == 'new')
      $data = $helper->getDefaultData();
    else
      $data = $helper->getExistingData();

    if ($data === false)
      return pagesetterErrorApiGet();

    //echo "<pre>"; print_r($data); echo "</pre>"; exit(0);

    $cardData  = $data[$helper->cardName];
    $tableData = $data[$helper->tableName];

    $data = array( $helper->cardName  => array( 'rows' => array($cardData) ),
                   $helper->tableName => array( 'rows' => $tableData       ) );

    $extra = $helper->getExtraData();
    $extra = $extra + array('deletedItems' => array(), 'action' => $action);

    guppy_open( array( 'specFile'    => "modules/pagesetter/forms/{$formName}Spec.xml",
                       'layoutFile'  => "modules/pagesetter/forms/{$formName}Layout.xml",
                       'toolbarFile' => "modules/pagesetter/forms/adminToolbar.xml",
                       'data'        => $data,
                       'extra'       => $extra,
                       'actionURL'   => $helper->getActionURL() ) );

  }

  return guppy_output();
}


function pagesetter_admin_listedit()
{
  class ListCardTableHelper extends CardTableHelper
  {
    var $lid;

    function ListCardTableHelper($lid)
    {
      $this->formName   = 'listEdit';
      $this->entityName = 'list';
      $this->cardName   = 'list';
      $this->tableName  = 'listItems';
      $this->lid        = $lid;
    }


    function checkAccess()
    {
      return pnSecAuthAction(0, 'pagesetter::', $this->lid."::", ACCESS_ADMIN);
    }


    function getDefaultData()
    {
      if (!pnModAPILoad('pagesetter', 'admin'))
        return pagesetterErrorPage(__FILE__, __LINE__, 'Failed to load Pagesetter admin API');

      return array( 'list' => array(), 'listItems' => array() );
    }


    function getExistingData()
    {
      if (!pnModAPILoad('pagesetter', 'admin'))
        return pagesetterErrorPage(__FILE__, __LINE__, 'Failed to load Pagesetter admin API');

      $list =  pnModAPIFunc( 'pagesetter',
                             'admin',
                             'getList',
                              array('lid' => $this->lid) );

      if ($list === false)
        return false;

      return array( 'list' => $list['list'], 'listItems' => $list['items'] );
    }

    function getExtraData()
    {
      return array('lid' => $this->lid);
    }


    function getActionURL()
    {
      return pnModUrl('pagesetter','admin','listedit');
    }
  }

  $lid = pnVarCleanFromInput('lid');

  $helper = new ListCardTableHelper($lid);

  return pagesetterCardTableHandler($helper);
}


// =======================================================================
// Workflow configuration
// =======================================================================

function pagesetter_admin_wfcfglist()
{
  require_once "modules/pagesetter/forms/wfcfgListHandler.php";
  $handler = new wfcfgListHandler();

  if (!guppy_decode($handler))
  {
    if (!pnSecAuthAction(0, 'pagesetter:workflow:', "::", ACCESS_ADMIN))
      return pagesetterErrorPage(__FILE__, __LINE__, _PGNOAUTH);

      // Get publication types

    if (!pnModAPILoad('pagesetter', 'admin'))
      return pagesetterErrorPage(__FILE__, __LINE__, 'Failed to load Pagesetter admin API');

    $pubTypes = pnModAPIFunc('pagesetter', 'admin', 'getPublicationTypes');

    if ($pubTypes === false)
      return pagesetterErrorAPIGet();

    $extra = array();

    $data = array( 'wfcfgList' => array( 'rows' => $pubTypes ) );

    guppy_open( array( 'specFile'    => 'modules/pagesetter/forms/wfcfgListSpec.xml',
                       'layoutFile'  => 'modules/pagesetter/forms/wfcfgListLayout.xml',
                       'toolbarFile' => 'modules/pagesetter/forms/adminToolbar.xml',
                       'options'     => array(),
                       'data'        => $data,
                       'extra'       => $extra,
                       'actionURL'   => pnModUrl('pagesetter','admin','wfcfglist') ) );
  }

  return guppy_output();
}


function pagesetter_admin_wfcfg()
{
  require_once "modules/pagesetter/forms/wfcfgHandler.php";
  $handler = new wfcfgHandler();

  if (!guppy_decode($handler))
  {
    $tid = pnVarCleanFromInput('tid');

    if (!pnSecAuthAction(0, 'pagesetter:workflow:', "$tid::", ACCESS_ADMIN))
      return pagesetterErrorPage(__FILE__, __LINE__, _PGNOAUTH);

    if (!pnModAPILoad('pagesetter', 'workflow'))
      return pagesetterErrorPage(__FILE__, __LINE__, 'Failed to load Pagesetter workflow API');

    if (!pnModAPILoad('pagesetter', 'admin'))
      return pagesetterErrorPage(__FILE__, __LINE__, 'Failed to load Pagesetter admin API');


      // Get dynamic parts of spec and layout

    $pubInfo =  pnModAPIFunc( 'pagesetter',
                              'admin',
                              'getPubTypeInfo',
                              array('tid' => $tid) );

    if ($pubInfo === false)
      return pagesetterErrorAPIGet();

    $workflowName = $pubInfo['publication']['workflow'];
    $workflow = pnModAPIFunc('pagesetter', 'workflow', 'load', array('workflow' => $workflowName) );
    if ($workflow === false)
      return pagesetterErrorAPIGet();

    $guppySetup = $workflow->getConfigurationsForGuppy();


      // Load hardcoded parts of spec and layout

    if (($spec=guppy_loadfile('pagedit', 'modules/pagesetter/forms/wfcfgSpec.xml')) === false)
      return guppy_output();

    if (($layout=guppy_loadfile('pagedit', 'modules/pagesetter/forms/wfcfgLayout.xml')) === false)
      return guppy_output();

    require_once 'modules/pagesetter/guppy/guppy_parser.php';

    guppy_parseXMLSpec($spec);
    global $guppyParsedSpec;
    $spec = &$guppyParsedSpec;
    $layout = guppy_parseXMLLayout($layout);
    //print_r($spec); exit(0);

      // Insert dynamic spec and layout

    $fieldSpec = &$spec['components']['wfcfg']['fields'];
    $fieldSpec = array_merge($fieldSpec, $guppySetup['fieldSpec']);

    $fieldLayout = &$layout['layout'][0][0]['layout'];
    $fieldLayout = array_merge($fieldLayout, $guppySetup['fieldLayout']);
    //print_r($fieldSpec); print_r($layout); exit(0);


      // Fetch data and show form

    $settings = pnModAPIFunc('pagesetter', 'workflow', 'getSettings',
                             array('workflow' => $workflowName,
                                   'tid'      => $tid) );

    $settings['pubType']  = $pubInfo['publication']['title'];
    $settings['workflow'] = $workflow->getTitle();

    $extra = array('tid' => $tid, 'workflow' => $workflowName);

    $data = array( 'wfcfg' => array( 'rows' => array($settings) ) );

    guppy_open( array( 'rawSpec'     => $spec,
                       'rawLayout'   => $layout,
                       'toolbarFile' => 'modules/pagesetter/forms/adminToolbar.xml',
                       'options'     => array(),
                       'data'        => $data,
                       'extra'       => $extra,
                       'actionURL'   => pnModUrl('pagesetter','admin','wfcfg') ) );
  }

  return guppy_output();
}


// =======================================================================
// Auto-create templates
// =======================================================================

/**
 * pagesetter_admin_createtemplates()
 *
 * Select which templates are to be created automatically
 *
 * @author Jörg Napp
 * @param $args
 * @return The ceck page
 **/
function pagesetter_admin_createtemplates($args)
{
    // Check access
    if (!pnSecAuthAction(0, 'pagesetter::', "::", ACCESS_ADMIN)) {
        return pagesetterErrorPage(__FILE__, __LINE__, _PGNOAUTH);
    }

    if (!pnModAPILoad('pagesetter', 'admin')) {
        return pagesetterErrorPage(__FILE__, __LINE__, 'Failed to load Pagesetter user API');
    }

    // get a list of all publication types
    $pubtypes = pnModAPIFunc('pagesetter', 'admin', 'getPublicationTypes');

    $templates = array();
    $smarty = &new pnRender('pagesetter');

    // loop through the defined publication types and find out
    // which templates do exist
    foreach ($pubtypes as $pubtype) {
        $formats = pagesetterSmartyGetTemplates($pubtype['id']);
        $template = array();
        $template['name'] = $pubtype['title'];
        $template['id'] = $pubtype['id'];
        foreach ($formats as $format) {
            $template[$format['name']] = $smarty->template_exists($format['file']);
        }
        $templates[] = $template;
    }

    // Use last example of formats to generate genric format list
    $genericFormats = array();
    foreach ($formats as $format)
      $genericFormats[] = $format['name'];

    // print out the results
    $smarty->caching = false;
    $smarty->assign('formats', $genericFormats);
    $smarty->assign('templates', $templates);

    // Make sure guppy stylesheet is included
    require_once 'modules/pagesetter/guppy/guppy_postnuke.php';
    guppy_postnuke_addEditorheaders();

    return $smarty->fetch('pagesetter_admin_createtemplates.htm');
}

/**
 * pagesetter_admin_do_createtemplates()
 *
 * Create the templates selected
 *
 * @author Jörg Napp
 * @param $args
 * @return
 **/
function pagesetter_admin_do_createtemplates($args)
{
    // Check access
    if (!pnSecAuthAction(0, 'pagesetter::', "::", ACCESS_ADMIN)) {
        return pagesetterErrorPage(__FILE__, __LINE__, _PGNOAUTH);
    }

    if (!pnModAPILoad('pagesetter', 'admin')) {
        return pagesetterErrorPage(__FILE__, __LINE__, 'Failed to load Pagesetter user API');
    }

    $templates=pnVarCleanFromInput('createme');


    // loop through all selected templates to create.
    foreach ($templates as $template){
        // Split the return value into information we could use
        list($tid, $format) = explode('.', $template);

        // get the generic template
        $template = pnModAPIFunc('pagesetter',
                                 'admin',
                                 'createTemplate',
                                 compact('tid', 'format'));


        // right now, the only possibility is to write the
        // template out to a file. This might change in future
        // versions (of pnRender), so that it might be written to
        // the database...

        // to write the file, we need to know the name of the
        // pubtype.

        $pubinfo = pnModAPIFunc('pagesetter',
                                'admin',
                                'getPubTypeInfo',
                                compact('tid'));

        $templateFilename = $pubinfo['publication']['filename'];


        // we write the file.
        $fname = "modules/pagesetter/pntemplates/$templateFilename-$format.html";
        $handle = fopen($fname, 'w');
        if (!$handle) {
            return pagesetterErrorPage(__FILE__, __LINE__, 'Cannot write the template file');
        }
        fwrite($handle, $template);
        fclose($handle);
        chmod($fname, 0766);
    }
      // This function generated no output, and so now it is complete we redirect
      // the user to an appropriate page for them to carry on their work
    pnRedirect(pnModURL('pagesetter', 'admin', 'createtemplates'));

    return true;
}

// =======================================================================
// Importing
// =======================================================================

function pagesetter_admin_import()
{
    // Check access
  if (!pnSecAuthAction(0, 'pagesetter::', '::', ACCESS_ADMIN))
    return pagesetterErrorPage(__FILE__, __LINE__, _PGNOAUTH);

  $smarty = new pnRender('pagesetter');

  return $smarty->fetch('pagesetter_admin_import.html');
}


function pagesetter_admin_importnews()
{
    // Check access
  if (!pnSecAuthAction(0, 'pagesetter::', '::', ACCESS_ADMIN))
    return pagesetterErrorPage(__FILE__, __LINE__, _PGNOAUTH);

  if (!pnModAPILoad('pagesetter', 'integ'))
    return pagesetterErrorPage(__FILE__, __LINE__, 'Failed to load Pagesetter admin API');

  $image = pnVarCleanFromInput('image');

  $ok =  pnModAPIFunc( 'pagesetter',
                       'integ',
                       'importNews',
                       array( 'addImage' => $image ) );

  if ($ok === false)
    return pagesetterErrorApiGet();

    // This function generated no output, and so now it is complete we redirect
    // the user to an appropriate page for them to carry on their work
  pnRedirect(pnModURL('pagesetter', 'admin', 'pubtypes'));

  return true;
}


function pagesetter_admin_importce()
{
    // Check access
  if (!pnSecAuthAction(0, 'pagesetter::', '::', ACCESS_ADMIN))
    return pagesetterErrorPage(__FILE__, __LINE__, _PGNOAUTH);

  if (!pnModAPILoad('pagesetter', 'integ'))
    return pagesetterErrorPage(__FILE__, __LINE__, 'Failed to load Pagesetter admin API');

  $ok =  pnModAPIFunc( 'pagesetter',
                       'integ',
                       'importContentExpress' );

  if ($ok === false)
    return pagesetterErrorApiGet();

    // This function generated no output, and so now it is complete we redirect
    // the user to an appropriate page for them to carry on their work
  pnRedirect(pnModURL('pagesetter', 'admin', 'pubtypes'));

  return true;
}


function pagesetter_admin_importarticle()
{
    // Check access
  if (!pnSecAuthAction(0, 'pagesetter::', '::', ACCESS_ADMIN))
    return pagesetterErrorPage(__FILE__, __LINE__, _PGNOAUTH);

  if (!pnModAPILoad('pagesetter', 'integ'))
    return pagesetterErrorPage(__FILE__, __LINE__, 'Failed to load Pagesetter admin API');

  $ok =  pnModAPIFunc( 'pagesetter', 'integ', 'createArticle' );

  if ($ok === false)
    return pagesetterErrorApiGet();

    // This function generated no output, and so now it is complete we redirect
    // the user to an appropriate page for them to carry on their work
  pnRedirect(pnModURL('pagesetter', 'admin', 'pubtypes'));

  return true;
}


function pagesetter_admin_importfileupload()
{
    // Check access
  if (!pnSecAuthAction(0, 'pagesetter::', '::', ACCESS_ADMIN))
    return pagesetterErrorPage(__FILE__, __LINE__, _PGNOAUTH);

  if (!pnModAPILoad('pagesetter', 'integ'))
    return pagesetterErrorPage(__FILE__, __LINE__, 'Failed to load Pagesetter admin API');

  $ok =  pnModAPIFunc( 'pagesetter',
                       'integ',
                       'importFileUpload' );

  if ($ok === false)
    return pagesetterErrorApiGet();

  pnRedirect(pnModURL('pagesetter', 'admin', 'pubtypes'));

  return true;
}


function pagesetter_admin_importimage()
{
    // Check access
  if (!pnSecAuthAction(0, 'pagesetter::', '::', ACCESS_ADMIN))
    return pagesetterErrorPage(__FILE__, __LINE__, _PGNOAUTH);

  if (!pnModAPILoad('pagesetter', 'integ'))
    return pagesetterErrorPage(__FILE__, __LINE__, 'Failed to load Pagesetter admin API');

  $ok =  pnModAPIFunc( 'pagesetter',
                       'integ',
                       'importImage' );

  if ($ok === false)
    return pagesetterErrorApiGet();

  pnRedirect(pnModURL('pagesetter', 'admin', 'pubtypes'));

  return true;
}


function pagesetter_admin_importnote()
{
    // Check access
  if (!pnSecAuthAction(0, 'pagesetter::', '::', ACCESS_ADMIN))
    return pagesetterErrorPage(__FILE__, __LINE__, _PGNOAUTH);

  if (!pnModAPILoad('pagesetter', 'integ'))
    return pagesetterErrorPage(__FILE__, __LINE__, 'Failed to load Pagesetter admin API');

  $ok =  pnModAPIFunc( 'pagesetter',
                       'integ',
                       'importNote' );

  if ($ok === false)
    return pagesetterErrorApiGet();

  pnRedirect(pnModURL('pagesetter', 'admin', 'pubtypes'));

  return true;
}


function pagesetter_admin_importpc()
{
    // Check access
  if (!pnSecAuthAction(0, 'pagesetter::', '::', ACCESS_ADMIN))
    return pagesetterErrorPage(__FILE__, __LINE__, _PGNOAUTH);

  if (!pnModAPILoad('pagesetter', 'integ'))
    return pagesetterErrorPage(__FILE__, __LINE__, 'Failed to load Pagesetter admin API');

  $ok =  pnModAPIFunc( 'pagesetter',
                       'integ',
                       'importPostCalendar' );

  if ($ok === false)
    return pagesetterErrorApiGet();

    // This function generated no output, and so now it is complete we redirect
    // the user to an appropriate page for them to carry on their work
  pnRedirect(pnModURL('pagesetter', 'admin', 'pubtypes'));

  return true;
}


function pagesetter_admin_importXMLSchema()
{
    // Check access
  if (!pnSecAuthAction(0, 'pagesetter::', '::', ACCESS_ADMIN))
    return pagesetterErrorPage(__FILE__, __LINE__, _PGNOAUTH);

  if (!pnModAPILoad('pagesetter', 'integ'))
    return pagesetterErrorPage(__FILE__, __LINE__, 'Failed to load Pagesetter admin API');

    // Check upload error
  if ($_FILES['xmlfile']['error'] != 0)
    return pagesetterErrorPage(__FILE__, __LINE__, _PGERRORUPLOAD . $_FILES['xmlfile']['name'] .
                               ' (' . $_FILES['xmlfile']['error'] . ')');

    // Get and check temporary upload directory
  $uploadDir = pnModGetVar('pagesetter', 'uploadDir');
  if (empty($uploadDir))
    return pagesetterErrorPage(__FILE__, __LINE__, _PGERRORUPLOADDIREMPTY);

    // Create and check temporary file
  if (($tmpfilename = tempnam($uploadDir, 'XML')) === false)
    return pagesetterErrorPage(__FILE__, __LINE__, _PGERRORUPLOADTMP . $_FILES['xmlfile']['name']);

    // Move uploaded file to a file accessible from our PHP script (the upload may very well be protected by SAFE_MODE)
  if (!move_uploaded_file($_FILES['xmlfile']['tmp_name'], $tmpfilename))
    return pagesetterErrorPage(__FILE__, __LINE__, _PGERRORUPLOADMOVE . $_FILES['xmlfile']['name'] . "/" . $tmpfilename);

  $result = pnModAPIFunc( 'pagesetter',
                          'integ',
                          'importXMLSchema',
                          array('filename' => $tmpfilename) );

  unlink($tmpfilename);

  if ($result === false)
    return pagesetterErrorApiGet();

    // This function generated no output, and so now it is complete we redirect
    // the user to an appropriate page for them to carry on their work
  pnRedirect(pnModURL('pagesetter', 'admin', 'pubtypes'));

  return true;
}


// =======================================================================
// Exporting
// =======================================================================

function pagesetter_admin_export()
{
    // Check access
  if (!pnSecAuthAction(0, 'pagesetter::', '::', ACCESS_ADMIN))
    return pagesetterErrorPage(__FILE__, __LINE__, _PGNOAUTH);

  if (!pnModAPILoad('pagesetter', 'admin'))
    return pagesetterErrorPage(__FILE__, __LINE__, 'Failed to load Pagesetter admin API');

  require_once "modules/pagesetter/forms/exportHandler.php";
  $handler = new ExportHandler();

  if (!guppy_decode($handler))
  {
    $pubTypes = pnModAPIFunc('pagesetter', 'admin', 'getPublicationTypes',
                             array('getForGuppyDropdown' => true));

    $pubTypeOptions = array( 'pubTypes' => $pubTypes );

    $exportData = array( 'tid'               => -1,
                         'includeCategories' => false,
                         'exportSchemaOnly'  => false);

    $data = array( 'export' => array( 'rows' => array( $exportData ) ) );

    guppy_open( array( 'specFile'    => 'modules/pagesetter/forms/exportSpec.xml',
                       'layoutFile'  => 'modules/pagesetter/forms/exportLayout.xml',
                       'toolbarFile' => 'modules/pagesetter/forms/adminToolbar.xml',
                       'data'        => $data,
                       'options'     => $pubTypeOptions,
                       'actionURL'   => pnModUrl('pagesetter','admin','export') ) );

  }

  return guppy_output();
}


function pagesetter_admin_exportXML()
{
  $tid = pnVarCleanFromInput('tid');

    // Check access
  if (!pnSecAuthAction(0, 'pagesetter::', '$tid::', ACCESS_ADMIN))
    return pagesetterErrorPage(__FILE__, __LINE__, _PGNOAUTH);

  if (!pnModAPILoad('pagesetter', 'integ'))
    return pagesetterErrorPage(__FILE__, __LINE__, 'Failed to load Pagesetter integ API');

  $exportURL = htmlspecialchars( pnModUrl('pagesetter', 'admin', 'exportXML2', array('tid' => $tid)) );
  $backURL = htmlspecialchars( pnModUrl('pagesetter', 'admin') );

    // Generate an I-frame with the actual export
  return
      _PGDOWNLOADINGSCHEMA
    . ": <a href=\"$exportURL\">" . _PGDOWNLOAD . "</a>.<br/>\n"
    . "<p><a href=\"$backURL\">" . _PGBACKTOADMIN . "</a>.</p>\n"
    . "<iframe src=\"$exportURL\" width=\"0\" height=\"0\" frameborder=\"0\"/>";
}


  // Export actual XML (supposed to be inside a hidden frame)
function pagesetter_admin_exportXML2()
{
  $tid = pnVarCleanFromInput('tid');

  // Access check in API

  if (!pnModAPILoad('pagesetter', 'integ'))
    return pagesetterErrorPage(__FILE__, __LINE__, 'Failed to load Pagesetter integ API');

    // Dump the XML
  $ok =  pnModAPIFunc( 'pagesetter',
                       'integ',
                       'export',
                       array('tid' => $tid) );

  if (!$ok)
    return pagesetterErrorAPIGet();

  return true;
}


// =======================================================================
// Database definition
// =======================================================================

function pagesetter_admin_databaseview()
{
    // Check access
  if (!pnSecAuthAction(0, 'pagesetter::', '::', ACCESS_ADMIN))
    return pagesetterErrorPage(__FILE__, __LINE__, _PGNOAUTH);

  if (!pnModAPILoad('pagesetter', 'admin'))
    return pagesetterErrorPage(__FILE__, __LINE__, 'Failed to load Pagesetter admin API');


  $pubTypes = pnModAPIFunc('pagesetter', 'admin', 'getPublicationTypes');
  if ($pubTypes === false)
    return pagesetterErrorAPIGet();

  $fullPubTypes = array();

  foreach ($pubTypes as $pubType)
  {
    $tid = $pubType['id'];

    $pubInfo = pnModAPIFunc( 'pagesetter', 'admin', 'getPubTypeInfo',
                             array('tid' => $tid) );
    if ($pubInfo === false)
      return pagesetterErrorAPIGet();

    for ($i=0, $size=count($pubInfo['fields']); $i<$size; ++$i)
    {
      $field = &$pubInfo['fields'][$i];
      $field['column'] = pagesetterGetPubColumnName($field['id']);
    }

    $fullPubTypes[] = $pubInfo;
  }

  $smarty = new pnRender('pagesetter');

  $smarty->caching = 0;
  $smarty->assign('publications', $fullPubTypes);

  return $smarty->fetch('pagesetter_admin_databaseview.html');
}


// =======================================================================
// Database definition
// =======================================================================

function pagesetter_admin_setupfolder()
{
    // Check access
  if (!pnSecAuthAction(0, 'pagesetter::', '::', ACCESS_ADMIN))
    return pagesetterErrorPage(__FILE__, __LINE__, _PGNOAUTH);

  if (!pnModAPILoad('pagesetter', 'admin'))
    return pagesetterErrorPage(__FILE__, __LINE__, 'Failed to load Pagesetter admin API');

  if (!pnModAPILoad('folder', 'user'))
    return pagesetterErrorPage(__FILE__, __LINE__, 'Failed to load Folder user API');


  $pubTypes = pnModAPIFunc('pagesetter', 'admin', 'getPublicationTypes');
  if ($pubTypes === false)
    return pagesetterErrorAPIGet();

  $fullPubTypes = array();

  foreach ($pubTypes as $pubType)
  {
    $tid = $pubType['id'];

    $pubInfo = pnModAPIFunc( 'pagesetter', 'admin', 'getPubTypeInfo',
                             array('tid' => $tid) );
    if ($pubInfo === false)
      return pagesetterErrorAPIGet();

    $folderId = $pubInfo['publication']['defaultFolder'];
    $folder = array('disabled' => ' disabled="1"');

    if ($folderId >= 0)
    {
      $folderInfo = pnModAPIFunc('folder', 'user', 'getItemInfo',
                                 array('itemId' => $folderId));

      if ($folderInfo === false)
        $folder['title'] = pnModAPIFunc('folder', 'user', 'errorAPIGet');
      else if ($folderInfo === true)
        $folder['title'] = _PGUNKNOWNFOLDER;
      else
      {
        $folder = $folderInfo;
        $folder['disabled'] = '';
      }
    }
    else
      $folder['title'] = _PGUNKNOWNFOLDER;

    $pubInfo['folder'] = $folder;

    $fullPubTypes[] = $pubInfo;
  }

  $render = new pnRender('pagesetter');

  $render->caching = 0;
  $render->assign('publications', $fullPubTypes);

  return $render->fetch('pagesetter_admin_setupfolder.html');
}


function pagesetter_admin_transferfolders()
{
    // Check access
  if (!pnSecAuthAction(0, 'pagesetter::', '::', ACCESS_ADMIN))
    return pagesetterErrorPage(__FILE__, __LINE__, _PGNOAUTH);

  if (!pnModAPILoad('pagesetter', 'admin'))
    return pagesetterErrorPage(__FILE__, __LINE__, 'Failed to load Pagesetter admin API');
  if (!pnModAPILoad('pagesetter', 'integ'))
    return pagesetterErrorPage(__FILE__, __LINE__, 'Failed to load Pagesetter integ API');


  $pubTypes = pnModAPIFunc('pagesetter', 'admin', 'getPublicationTypes');
  if ($pubTypes === false)
    return pagesetterErrorAPIGet();

  foreach ($pubTypes as $pubType)
  {
    $tid = $pubType['id'];
    $doTransfer = (int)pnVarCleanFromInput("transfer-$tid");

    if ($doTransfer)
    {
      $ok = pnModAPIFunc( 'pagesetter', 'integ', 'transferPubTypeToFolders',
                          array('tid' => $tid) );
      if ($ok === false)
        return pagesetterErrorAPIGet();
    }
  }

  return _PGFOLDERSTRANSFERED;
}


function pagesetter_admin_typeselect()
{
  $pluginType     = pnVarCleanFromInput('plugintype');
  $hiddenInputId  = pnVarCleanFromInput('inputid');
  $typeData       = pnVarCleanFromInput('typedata');
  $okButton       = pnVarCleanFromInput('okbutton');
  $cancelButton   = pnVarCleanFromInput('cancelbutton');

  if ($okButton != '')
  {
    return pagesetterEditTypeselectUpdate(true);
  }
  else if ($cancelButton != '')
  {
    return pagesetterEditTypeselectUpdate(false);
  }

  $includeName = "modules/pagesetter/guppy/plugins/typeextra.$pluginType.php";
  include $includeName;

  // FIXME
  $args = array('typeData' => $typeData);
  $functionName = 'typeextra_'.$pluginType.'_render';
  $output = $functionName($args);

  $render = new pnRender('pagesetter');
  $render->caching = 0;
  $render->assign('pluginType', $pluginType);
  $render->assign('hiddenInputId', $hiddenInputId);
  $render->assign('output', $output);

  echo $render->fetch('pagesetter_edit_typeselect.html');

  return true;
}


function pagesetterEditTypeselectUpdate($ok)
{
  echo "<html><script>window.opener.focus(); window.close();</script></html>";

  return true;
}


?>
