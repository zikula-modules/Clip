<?php
/**
 * PageMaster
 *
 * @copyright (c) 2008, PageMaster Team
 * @link        http://code.zikula.org/projects/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package     Zikula_3rd_party_Modules
 * @subpackage  pagemaster
 */

/**
 * Convert Lists to Categories
 *
 * @author       kundi
 * @return       boolean
 */
function pagemaster_importapi_importps1()
{
    //convert list's
    pnModLoad('pagesetter');

    function guppy_translate($str) {
        if (strlen($str) > 0 && $str[0] != '_')
            return $str;
        if (constant($str) <> false)
            return constant($str);
        else
            return $str;
    }

    Loader::loadClass('CategoryUtil');
    Loader::loadClassFromModule('Categories', 'Category');
    //Loader::loadClassFromModule('Categories', 'CategoryRegistry');

    $rootcat = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules/pagemaster/lists');
    if ($rootcat == '' or !$rootcat) {
        return LogUtil :: registerError('Category /__SYSTEM__/Modules/pagemaster/lists not found');
    }
	
    $temp_arr = unserialize(pnModGetVar('pagesetter','temp_arr'));
    $lang  = pnUserGetLang();
    $lists = DBUtil::selectObjectArray('pagesetter_lists');
    foreach ($lists as $list) {
    	$cat = new PNCategory();
        $cat->setDataField('parent_id', $rootcat['id']);
        $cat->setDataField('name', $list['title']);
        $cat->setDataField('is_leaf', 0);
        $cat->setDataField('display_name', array ($lang => guppy_translate($list['title'])));
        $cat->setDataField('display_desc', array ($lang => guppy_translate($list['description'])));
        $cat->insert();
        $cat->update();
        $dr = $cat->getDataField('id');
        $items = DBUtil::selectObjectArray('pagesetter_listitems', 'pg_lid = '.$list['id'], 'pg_id');
        foreach ($items as $item) {
            //TODO Lists are "flat" after import, means only one hirachical step
        	$cat = new PNCategory();
            $cat->setDataField('name', $item['title']);
            if ($item['parentID'] == -1){
            	$cat->setDataField('parent_id', $dr);
            	if ($item['lval']-$item['rval'] < -1)
            		$cat->setDataField('is_leaf', 0);
            	else
            		$cat->setDataField('is_leaf', 1);
            }else{
            	$cat->setDataField('parent_id', $temp_arr[$item['parentID']]);
            	if ($item['lval']-$item['rval'] < -1)
            		$cat->setDataField('is_leaf', 0);
            	else
            		$cat->setDataField('is_leaf', 1);
            }  
            
            $cat->setDataField('sort_value', $item['lineno']);
            $cat->setDataField('display_name', array ($lang => guppy_translate($item['title'])));
            $cat->setDataField('display_desc', array ($lang => guppy_translate($item['fullTitle'])));
            $cat->insert();
            $cat->update();
            $temp_arr[$item['id']] =  $cat->getDataField('id');
        }
    }
    //save link between list id's and category id's
    pnModSetVar('pagesetter','temp_arr',serialize($temp_arr));
    return LogUtil::registerStatus(_PAGEMASTER_IMPORTFROMPAGESETTER_INSERTSUCCEDED);
}

/**
 * Import DB Structure
 *
 * @author       kundi
 * @return       boolean
 */
function pagemaster_importapi_importps2()
{
    //convert list's
    pnModLoad('pagesetter');
    pnModDBInfoLoad('Categories');
    include_once('includes/pnForm.php');

    //import the DB Structure
    $pubtypes = DBUtil::selectObjectArray('pagesetter_pubtypes');
    foreach ($pubtypes as $pubtype) {
        $datatype['tid']             = $pubtype['id'];
        $datatype['title']           = $pubtype['title'];
        $datatype['filename']        = $pubtype['filename'];
        $datatype['formname']        = $pubtype['formname'];
        $datatype['description']     = $pubtype['description'];
        $datatype['itemsperpage']    = $pubtype['listCount'];
        $datatype['sortfield1']      = '';
        $datatype['sortdesc1']       = '';
        $datatype['sortfield2']      = '';
        $datatype['sortdesc2']       = '';
        $datatype['sortfield3']      = '';
        $datatype['sortdesc3']       = '';
        $datatype['defaultfilter']   = $pubtype['defaultFilter'];
        $datatype['workflow']        = $pubtype['workflow'] . '.xml';
        $datatype['enablerevisions'] = $pubtype['enableRevisions'];
        $datatype['enableeditown']   = $pubtype['enableEditOwn'];
        DBUtil::insertObject($datatype, 'pagemaster_pubtypes');
        //$pubfields = DBUtil::selectObjectArray('pagesetter_pubfields', 'pg_tid = '.$pubtype['id']);

        $sql = 'SELECT pn_pagesetter_pubfields.pg_id AS "id",
                       pn_pagesetter_pubfields.pg_tid AS "tid",
                       pn_pagesetter_pubfields.pg_name AS "name", 
                       pn_pagesetter_pubfields.pg_title AS "title",
                       pn_pagesetter_pubfields.pg_description AS "description",
                       pn_pagesetter_pubfields.pg_type AS "type", 
                       pn_pagesetter_pubfields.pg_typedata AS "typeData",
                       pn_pagesetter_pubfields.pg_istitle AS "isTitle",
                       pn_pagesetter_pubfields.pg_ispageable AS "isPageable",
                       pn_pagesetter_pubfields.pg_issearchable AS "isSearchable",
                       pn_pagesetter_pubfields.pg_ismandatory AS "isMandatory",
                       pn_pagesetter_pubfields.pg_lineno AS "lineno"
                FROM pn_pagesetter_pubfields where pg_tid = '.$pubtype['id'];
        $result = DBUtil::executeSQL($sql);
        if (!$result) {
            LogUtil::registerError('Error' . $sql);
        }

        for (; !$result->EOF; $result->MoveNext()) {
            $pubfield = $result->GetRowAssoc(2);

            //foreach ($pubfields as $pubfield) {
            //Import Pub Field
            $datafield['id'] = $pubfield['id'];
            $datafield['tid'] = $pubfield['tid'];
            $datafield['name'] = $pubfield['name'];
            $datafield['title'] = $pubfield['title'];
            $datafield['description'] = $pubfield['description'];
            $datafield['fieldmaxlength'] = '';
            $datafield['typedata'] = '';

            if ($pubfield['type'] == 'datetime') {
                $datafield['fieldplugin'] = 'function.pmformdateinput.php'; //TODO

            } elseif ($pubfield['type'] == 'url') {
                $datafield['fieldplugin'] = 'function.pmformurlinput.php';

            } elseif ($pubfield['type'] == 'email') {
                $datafield['fieldplugin'] = 'function.pmformemailinput.php';

            } elseif ($pubfield['type'] == 'multilist') {
                $datafield['fieldplugin'] = 'function.pmformmultilistinput.php';
                $list = DBUtil :: selectObjectArray("pagesetter_lists", 'pg_id = ' . $pubfield['typeData']);
                $cat = DBUtil :: selectObjectArray("categories_category", "cat_path = '/__SYSTEM__/Modules/pagemaster/lists/" . mysql_escape_string($list[0]['title']) . "' and cat_name = '" . mysql_escape_string($list[0]['title']) . "'");
                $datafield['typedata'] = $cat[0]['id'];

            } elseif ($pubfield['type'] == 'publication') {
                $datafield['fieldplugin'] = 'function.pmformpubinput.php';
                $datafield['typedata'] = $pubfield['typeData'];

            } elseif ($pubfield['type'] == '0') {
                $datafield['fieldplugin'] = 'function.pmformstringinput.php';

            } elseif ($pubfield['type'] == '1') {
                $datafield['fieldplugin'] = 'function.pmformtextinput.php';
                $datafield['typedata'] = '0';

            } elseif ($pubfield['type'] == '2') {
                $datafield['fieldplugin'] = 'function.pmformtextinput.php';
                $datafield['typedata'] = '1';

            } elseif ($pubfield['type'] == '3') {
                $datafield['fieldplugin'] = 'function.pmformcheckboxinput.php';

            } elseif ($pubfield['type'] == '4') {
                $datafield['fieldplugin'] = 'function.pmformintinput.php';

            } elseif ($pubfield['type'] == '5') {
                $datafield['fieldplugin'] = 'function.pmformfloatinput.php';

            } elseif ($pubfield['type'] == '6') {
                $datafield['fieldplugin'] = 'function.pmformdateinput.php';

            } elseif ($pubfield['type'] == '7') { //time TODO
                $datafield['fieldplugin'] = 'function.pmformdateinput.php';

            } elseif ($pubfield['type'] == '8') {
                $datafield['fieldplugin'] = 'function.pmformurlinput.php';

            } elseif ($pubfield['type'] == '9') {
                $datafield['fieldplugin'] = 'function.pmformimageinput.php';
                $datafield['typedata'] = '100:100';

            } elseif ($pubfield['type'] == '10') {
                $datafield['fieldplugin'] = 'function.pmformuploadinput.php';

            } elseif (is_numeric($pubfield['type']) and $pubfield['type'] > 100) {
                //has to be a list
                $datafield['fieldplugin'] = 'function.pmformlistinput.php';
                $pubfield['type'] = $pubfield['type'] -100;
                $list = DBUtil::selectObjectArray('pagesetter_lists', 'pg_id = '.$pubfield['type']);
                $cat  = DBUtil::selectObjectArray('categories_category', 'cat_path = \'/__SYSTEM__/Modules/pagemaster/lists/'.mysql_escape_string($list[0]['title']).'\' and cat_name = \''.mysql_escape_string($list[0]['title']) .'\'');
                $datafield['typedata'] = $cat[0]['id'];

            } else {
                echo 'unsupportet field type' . $pubfield['type'];
            }

            $plugin = pagemasterGetPlugin($datafield['fieldplugin']);
            $datafield['fieldtype']    = $plugin->columnDef;
            $datafield['istitle']      = $pubfield['isTitle'];
            $datafield['ispageable']   = $pubfield['isPageable'];
            $datafield['issearchable'] = $pubfield['isSearchable'];
            $datafield['ismandatory']  = $pubfield['isMandatory'];
            $datafield['lineno']       = $pubfield['lineno'];
            DBUtil::insertObject($datafield, 'pagemaster_pubfields', 'dummy');
        }
    }
    return LogUtil::registerStatus(_PAGEMASTER_IMPORTFROMPAGESETTER_INSERTSUCCEDED);
}

/**
 * Create DB Tables
 *
 * @author       kundi
 * @return       boolean
 */
function pagemaster_importapi_importps3()
{
    //create tables
    $pubtypes = DBUtil::selectObjectArray('pagemaster_pubtypes');

    foreach ($pubtypes as $pubtype) {
        $ret = pnModAPIFunc('pagemaster', 'admin', 'updatetabledef',
                            array('tid' => $pubtype['tid']));
        if (!$ret)
            LogUtil::registerError(_PAGEMASTER_CANNOTCREATEDBFORTID.' '.$pubtype['tid']);
    }
    return LogUtil :: registerStatus(_PAGEMASTER_IMPORTFROMPAGESETTER_INSERTSUCCEDED);
}

/**
 * Load Data
 *
 * @author       kundi
 * @return       boolean
 */
function pagemaster_importapi_importps4()
{
    pnModLoad('pagesetter');
    pnModDBInfoLoad('Workflow');

    Loader::loadClass('CategoryUtil');
    Loader::loadClassFromModule('Categories', 'Category');
    
    $temp_arr = unserialize(pnModGetVar('pagesetter','temp_arr'));
   

    $pntable = &pnDBGetTables();
    $DirPM = pnModGetVar('pagemaster', 'uploadpath');
    $DirPS = pnModGetVar('pagesetter', 'uploadDirDocs');

    // insert revisions
    $sql = "INSERT INTO $pntable[pagemaster_revisions] (
                SELECT pg_tid, pg_id, pg_pid, pg_prevversion, 'A', pg_timestamp , pg_user, null, null  FROM $pntable[pagesetter_revisions] )";
    //$result = DBUtil::executeSQL($sql);

    $pubtypes = DBUtil::selectObjectArray('pagesetter_pubtypes', 'pg_id=11'); //, 'pg_id=11'
    foreach ($pubtypes as $pubtype) {
        $pubfields = DBUtil::selectObjectArray('pagemaster_pubfields', 'pm_tid = '.$pubtype['id']);

        foreach ($pubfields as $pubfield) {

            if ($pubfield['fieldplugin'] == 'function.pmformimageinput.php') {
                $imagefields[$pubfield['id']] = 1;

            } elseif ($pubfield['fieldplugin'] == 'function.pmformlistinput.php') {
                $listfields[$pubfield['id']] = 1;

            } elseif ($pubfield['fieldplugin'] == 'function.pmformmultilistinput.php') {
                $multifields[$pubfield['id']] = 1;

            }
        }
        $tablename   = $pntable['pagesetter_pubdata'].$pubtype['id'];
        $tablenamePM = $pntable['pagemaster_pubdata'.$pubtype['id']];

        $sql    = 'SELECT pg_hitcount , dyn.* from pn_pagesetter_pubheader pp, ' . $tablename . ' dyn where pp.pg_pid = dyn.pg_pid and pp.pg_tid = ' . $pubtype['id'];
        $result = DBUtil::executeSQL($sql);
        if (!$result) {
            LogUtil::registerError(_PAGEMASTER_CANNOTIMPORTDATAFORTID.' '.$pubtype['id']);
        }

        for (; !$result->EOF; $result->MoveNext()) {
            $sql = 'INSERT INTO '. $tablenamePM .' (pm_id, pm_pid, pm_online, pm_indepot, pm_revision, pm_showinmenu, pm_showinlist, pm_publishdate, pm_expiredate, pm_language, pm_hitcount ';
            $item = $result->GetRowAssoc(2);

            $data = array ();
            $data[1]  = $item['pg_id'];
            $data[2]  = $item['pg_pid'];
            $data[3]  = $item['pg_online'];
            $data[4]  = $item['pg_indepot'];
            $data[5]  = $item['pg_revision'];
            $data[6]  = $item['pg_showInMenu'];
            $data[7]  = $item['pg_showInList'];
            $data[8]  = $item['pg_publishDate'];
            $data[9]  = $item['pg_expireDate'];
            $data[10] = str_replace('x_all', '', $item['pg_language']);
            $data[11] = $item['pg_hitcount'];

            $i = 0;
            foreach ($item as $key => $field) {
                $i++;
                if ($i > 17) {
                    $fieldname = str_replace('pg_field', 'pm_', $key);
                    $DBid = str_replace('pg_field', '', $key);
                    if (isset ($imagefields[$DBid])) {
                        list ($mime_type, $id, $file_name, $orig_name) = explode('|', $field);
                        $tmb_file_name = str_replace('.dat', '-tmb.dat', $file_name);
                        $arrTypeData = array (
                            'orig_name' => $orig_name,
                            'tmb_name' => $tmb_file_name,
                            'file_name' => $file_name
                        );
                        $field = serialize($arrTypeData);
                        copy($DirPS . '/' . $file_name, $DirPM . '/' . $file_name);
                        copy($DirPS . '/' . $tmb_file_name, $DirPM . '/' . $tmb_file_name);

                    } elseif (isset ($listfields[$DBid])) {
                        if ($field <> ''){
                            if ($field <> 0){
                                /*$listitem = DBUtil::selectObjectByID('pagesetter_listitems', $field);
                                if (!$listitem)
                                    LogUtil::registerError('Listitem: '.$field.' not found in pagesetter field: '.$key);
                                
                                $list = DBUtil::selectObjectByID('pagesetter_lists', $listitem['lid']);
                                if (!$list)
                                    LogUtil :: registerError('List: '.$listitem[lid].' not found in pagesetter field: '.$key);
                                
                                $catitem = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules/pagemaster/lists/' . str_replace('/', '&#47;', mysql_escape_string($list['title'])) . '/' . str_replace('/', '&#47;', mysql_escape_string($listitem['title'])));
								*/
                                $catitem['id'] = $temp_arr[$field];
                                $field = $catitem['id'];
                            } else {
                                $field = '';
                            }
                        }

                    } elseif (isset ($multifields[$DBid])) {
                        $listArr = explode(':', $field);
                        $field = ':';
                        foreach ($listArr as $listId) {
                            if ($listId <> '') {
                                //$listitem = DBUtil::selectObjectByID('pagesetter_listitems', $listId);
                                //$list     = DBUtil::selectObjectByID('pagesetter_lists', $listitem['lid']);
								//$catitem  = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules/pagemaster/lists/' . str_replace('/', '&#47;', mysql_escape_string($list['title'])) . '/' . str_replace('/', '&#47;', mysql_escape_string($listitem['title'])));
                            	$catitem['id'] = $temp_arr[$listId];
                                if ($catitem['id'] <> '')
                                    $field .= $catitem['id'] . ':';
                            }
                        }
                    }

                    if ($field == ':')
                        $field = '';
                    $data[] = $field;
                    $sql .= ', ' . $fieldname;
                }
            }

            $data[] = $item['pg_created'];
            $data[] = $item['pg_creator'];
            $data[] = $item['pg_lastUpdatedDate'];
            $data[] = $item['pg_creator'];

            $sql .= ', pm_cr_date, pm_cr_uid, pm_lu_date, pm_lu_uid ';
            $sql .= ') VALUES ( ' . $data[1];
            unset ($data[1]);

            foreach ($data as $dkey => $data2) {
                if ($data2 == '' and $dkey <> 10) {
                    $sql .= ' , null';
                } else {
                    $sql .= ' ,\''. mysql_escape_string($data2) . '\'';
                }
            }
            $sql .= ' )';
            $resultinsert = DBUtil::executeSQL($sql);

            $wfData['metaid']       = 0;
            $wfData['module']       = 'pagemaster';
            $wfData['schemaname']   = $pubtype['workflow'];
            $wfData['state']        = $item['pg_approvalState'];
            $wfData['type']         = 1;
            $wfData['obj_table']    = 'pagemaster_pubdata' . $pubtype['id'];
            $wfData['obj_idcolumn'] = 'id';
            $wfData['obj_id']       = $item['pg_id'];
            $wfData['busy']         = 0;
            $ret = DBUtil :: insertObject($wfData, 'workflows', 'id');
            $sdf = $sdf +1;
            /*if ($sdf > 10) {
                exit;
            }*/
        }

        $sql = 'UPDATE '.$tablenamePM.' SET pm_publishdate = null WHERE pm_publishdate = \'0000-00-00\';';
        $upd_result = DBUtil::executeSQL($sql);

        $sql = 'UPDATE '.$tablenamePM.' SET pm_expiredate = null WHERE pm_expiredate = \'0000-00-00\';';
        $upd_result = DBUtil::executeSQL($sql);
    }

    return LogUtil::registerStatus(_PAGEMASTER_IMPORTFROMPAGESETTER_INSERTSUCCEDED);
}
