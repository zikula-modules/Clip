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

/**
 * Convert Lists to Categories
 *
 * @author       kundi
 * @return       boolean
 */
function PageMaster_importapi_importps1()
{
    $dom = ZLanguage::getModuleDomain('PageMaster');

    // convert list's
    pnModLoad('pagesetter');

    function guppy_translate($str)
    {
        if (strlen($str) > 0 && $str[0] != '_') {
            return $str;
        }
        if (constant($str) <> false) {
            return constant($str);
        }
        return $str;
    }

    Loader::loadClass('CategoryUtil');
    Loader::loadClassFromModule('Categories', 'Category');
    //Loader::loadClassFromModule('Categories', 'CategoryRegistry');

    $rootcat = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules/pagemaster/lists');
    if (empty($rootcat)) {
        return LogUtil::registerError('Category /__SYSTEM__/Modules/pagemaster/lists not found');
    }

    //$temp_arr = unserialize(pnModGetVar('pagesetter','temp_arr'));
    $temp_arr = array();

    $lang  = ZLanguage::getLanguageCode();
    $lists = DBUtil::selectObjectArray('pagesetter_lists');

    foreach ($lists as $list)
    {
        $cat = new PNCategory();
        $cat->setDataField('parent_id', $rootcat['id']);
        $cat->setDataField('name', $list['title']);
        $cat->setDataField('is_leaf', 0);
        $cat->setDataField('display_name', array($lang => guppy_translate($list['title'])));
        $cat->setDataField('display_desc', array($lang => guppy_translate($list['description'])));
        $cat->insert();
        $cat->update();
        $dr = $cat->getDataField('id');

        $items = DBUtil::selectObjectArray('pagesetter_listitems', "pg_lid = '$list[id]'", 'pg_id');
        foreach ($items as $item)
        {
            // FIXME [Lists are "flat" after import, means only one hirachical step]
            $cat = new PNCategory();
            $cat->setDataField('name', $item['title']);
            if ($item['parentID'] == -1){
                $cat->setDataField('parent_id', $dr);
                if ($item['lval']-$item['rval'] < -1) {
                    $cat->setDataField('is_leaf', 0);
                } else {
                    $cat->setDataField('is_leaf', 1);
                }
            } else {
                $cat->setDataField('parent_id', $temp_arr[$item['parentID']]);
                if ($item['lval']-$item['rval'] < -1) {
                    $cat->setDataField('is_leaf', 0);
                } else {
                    $cat->setDataField('is_leaf', 1);
                }
            }

            $cat->setDataField('sort_value', $item['lineno']);
            $cat->setDataField('display_name', array($lang => guppy_translate($item['title'])));
            $cat->setDataField('display_desc', array($lang => guppy_translate($item['fullTitle'])));
            $cat->insert();
            $cat->update();
            $temp_arr[$item['id']] =  $cat->getDataField('id');
        }
    }

    // save link between list id's and category id's
    pnModSetVar('pagesetter', 'temp_arr', serialize($temp_arr));

    return LogUtil::registerStatus(__('Lists import succeded!', $dom));
}

/**
 * Import DB Structure
 *
 * @author       kundi
 * @return       boolean
 */
function PageMaster_importapi_importps2()
{
    $dom = ZLanguage::getModuleDomain('PageMaster');

    pnModLoad('pagesetter');
    pnModDBInfoLoad('Categories');

    // import the DB Structure
    $pubtypes = DBUtil::selectObjectArray('pagesetter_pubtypes');

    foreach ($pubtypes as $pubtype)
    {
        $datatype['tid']             = $pubtype['id'];
        $datatype['title']           = $pubtype['title'];
        $datatype['filename']        = $pubtype['filename'];
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
        //$pubfields = DBUtil::selectObjectArray('pagesetter_pubfields', 'pg_tid = '.$pubtype['id'], '', -1, -1, 'name');

        $pstable = DBUtil::getLimitedTablename('pagesetter_pubfields');
        $sql = "SELECT pf.pg_id AS id,
                       pf.pg_tid AS tid,
                       pf.pg_name AS name,
                       pf.pg_title AS title,
                       pf.pg_description AS description,
                       pf.pg_type AS type,
                       pf.pg_typedata AS typeData,
                       pf.pg_istitle AS isTitle,
                       pf.pg_ispageable AS isPageable,
                       pf.pg_issearchable AS isSearchable,
                       pf.pg_ismandatory AS isMandatory,
                       pf.pg_lineno AS lineno
                  FROM $pstable pf
                 WHERE pg_tid = '$pubtype[id]'";

        $result = DBUtil::executeSQL($sql);
        if (!$result) {
            LogUtil::registerError('Error in SQL: '.$sql);
        }

        for (; !$result->EOF; $result->MoveNext()) {
            $pubfield = $result->GetRowAssoc(2);

            // foreach ($pubfields as $pubfield) {
            // import Pub Field
            $datafield['id']             = $pubfield['id'];
            $datafield['tid']            = $pubfield['tid'];
            $datafield['name']           = $pubfield['name'];
            $datafield['title']          = $pubfield['title'];
            $datafield['description']    = $pubfield['description'];
            $datafield['fieldmaxlength'] = '';
            $datafield['typedata']       = '';

            switch ($pubfield['type'])
            {
                case 'datetime':
                    $datafield['fieldplugin'] = 'pmformdateinput'; // FIXME
                    break;
    
                case 'url':
                    $datafield['fieldplugin'] = 'pmformurlinput';
                    break;
    
                case 'email':
                    $datafield['fieldplugin'] = 'pmformemailinput';
                    break;
    
                case 'multilist':
                    $datafield['fieldplugin'] = 'pmformmultilistinput';
    
                    $list = DBUtil :: selectObjectArray("pagesetter_lists", "pg_id = '$pubfield[typeData]'");
    
                    $where = "cat_path = '/__SYSTEM__/Modules/pagemaster/lists/".DataUtil::formatForStore($list[0]['title'])."' AND cat_name = '".DataUtil::formatForStore($list[0]['title'])."'";
                    $cat   = DBUtil :: selectObjectArray("categories_category", $where);
    
                    $datafield['typedata'] = $cat[0]['id'];
                    break;
    
                case 'publication':
                    $datafield['fieldplugin'] = 'pmformpubinput';
                    $datafield['typedata']    = $pubfield['typeData'];
                    break;
    
                case '0':
                    $datafield['fieldplugin'] = 'pmformstringinput';
                    break;
    
                case '1':
                    $datafield['fieldplugin'] = 'pmformtextinput';
                    $datafield['typedata']    = '0';
                    break;
    
                case '2':
                    $datafield['fieldplugin'] = 'pmformtextinput';
                    $datafield['typedata']    = '1';
                    break;
    
                case '3':
                    $datafield['fieldplugin'] = 'pmformcheckboxinput';
                    break;
    
                case '4':
                    $datafield['fieldplugin'] = 'pmformintinput';
                    break;
    
                case '5':
                    $datafield['fieldplugin'] = 'pmformfloatinput';
                    break;
    
                case '6':
                    $datafield['fieldplugin'] = 'pmformdateinput';
                    break;
    
                case '7': //time TODO
                    $datafield['fieldplugin'] = 'pmformdateinput';
                    break;
    
                case '8':
                    $datafield['fieldplugin'] = 'pmformurlinput';
                    break;
    
                case '9':
                    $datafield['fieldplugin'] = 'pmformimageinput';
                    $datafield['typedata']    = '100:100';
                    break;
    
                case '10':
                    $datafield['fieldplugin'] = 'pmformuploadinput';
                    break;
    
                default:
                    if ($pubfield['type']  == 'plz') {
                        $datafield['fieldplugin'] = 'pmformplzinput';
        
                    } elseif ($pubfield['type']  == 'latlng') {
                        $datafield['fieldplugin'] = 'pmformlatlnginput';
        
                    } elseif (is_numeric($pubfield['type']) && $pubfield['type'] > 100) {
                        // has to be a list
                        $datafield['fieldplugin'] = 'pmformlistinput';
                        $pubfield['type']         = $pubfield['type']-100;
        
                        $list  = DBUtil::selectObjectArray('pagesetter_lists', "pg_id = '$pubfield[type]'");
        
                        $where = "cat_path = '/__SYSTEM__/Modules/pagemaster/lists/".DataUtil::formatForStore($list[0]['title'])."' AND cat_name = '".DataUtil::formatForStore($list[0]['title'])."'";
                        $cat   = DBUtil::selectObjectArray('categories_category', $where);
        
                        $datafield['typedata'] = $cat[0]['id'];
        
                    } else {
                        LogUtil::registerError(__f('Error! Unsupported field type [%s].', $pubfield['type'], $dom));
                    }
            }

            $plugin                    = PMgetPlugin($datafield['fieldplugin']);

            $datafield['fieldtype']    = $plugin->columnDef;
            $datafield['istitle']      = $pubfield['isTitle'];
            $datafield['ispageable']   = $pubfield['isPageable'];
            $datafield['issearchable'] = $pubfield['isSearchable'];
            $datafield['ismandatory']  = $pubfield['isMandatory'];
            $datafield['lineno']       = $pubfield['lineno'];

            DBUtil::insertObject($datafield, 'pagemaster_pubfields', 'dummy');
        }
    }

    return LogUtil::registerStatus(__('Fields import succeded!', $dom));
}

/**
 * Create DB Tables
 *
 * @author       kundi
 * @return       boolean
 */
function PageMaster_importapi_importps3()
{
    $dom = ZLanguage::getModuleDomain('PageMaster');

    // create tables
    $pubtypes = DBUtil::selectObjectArray('pagemaster_pubtypes');

    foreach ($pubtypes as $pubtype) {
        $ret = pnModAPIFunc('PageMaster', 'admin', 'updatetabledef', array('tid' => $pubtype['tid']));
        if (!$ret) {
            LogUtil::registerError(__('Cannot create the database for tid [%1$s (%2$s)].', array($pubtype['title'], $pubtype['tid']), $dom));
        }
    }

    return LogUtil::registerStatus(__('Database update succeded!', $dom));
}

/**
 * Load Data
 *
 * @author       kundi
 * @return       boolean
 */
function PageMaster_importapi_importps4()
{
    $dom = ZLanguage::getModuleDomain('PageMaster');

    pnModLoad('pagesetter');
    pnModDBInfoLoad('Workflow');

    Loader::loadClass('CategoryUtil');
    Loader::loadClassFromModule('Categories', 'Category');

    $temp_arr = unserialize(pnModGetVar('pagesetter', 'temp_arr'));

    $tables = &pnDBGetTables();
    $pubheader_table = $tables['pagesetter_pubheader'];

    $DirPM = pnModGetVar('PageMaster', 'uploadpath');
    $DirPS = pnModGetVar('pagesetter', 'uploadDirDocs');

    $pubtypes = DBUtil::selectObjectArray('pagesetter_pubtypes'); //, 'pg_id=11'

    foreach ($pubtypes as $pubtype)
    {
        $pubfields = DBUtil::selectObjectArray('pagemaster_pubfields', "pm_tid = '$pubtype[id]'");

        foreach ($pubfields as $pubfield)
        {
            if ($pubfield['fieldplugin'] == 'pmformimageinput') {
                $imagefields[$pubfield['id']] = 1;

            } elseif ($pubfield['fieldplugin'] == 'pmformlistinput') {
                $listfields[$pubfield['id']] = 1;

            } elseif ($pubfield['fieldplugin'] == 'pmformmultilistinput') {
                $multifields[$pubfield['id']] = 1;
            }
        }
        $tablename   = $tables['pagesetter_pubdata'].$pubtype['id'];
        $tablenamePM = $tables['pagemaster_pubdata'.$pubtype['id']];

        $sql    = 'SELECT pp.pg_hitcount , dyn.*
                   FROM '.$pubheader_table.' pp,
                        '.$tablename.' dyn
                   WHERE  pp.pg_pid = dyn.pg_pid AND pp.pg_tid = \''.$pubtype['id'].'\'';

        $result = DBUtil::executeSQL($sql);
        if (!$result) {
            LogUtil::registerError(__f('Error! Cannot import the data for pubtype [%1$s (%2$s)].', array($pubtype['title'], $pubtype['id']), $dom));
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
            foreach ($item as $key => $field)
            {
                $i++;
                if ($i > 17) {
                    $fieldname = str_replace('pg_field', 'pm_', $key);
                    $DBid      = str_replace('pg_field', '', $key);

                    if (isset($imagefields[$DBid])) {
                        list($mime_type, $id, $file_name, $orig_name) = explode('|', $field);

                        $tmb_file_name = str_replace('.dat', '-tmb.dat', $file_name);
                        $arrTypeData = array (
                            'orig_name' => $orig_name,
                            'tmb_name' => $tmb_file_name,
                            'file_name' => $file_name
                        );
                        $field = serialize($arrTypeData);

                        copy("$DirPS/$file_name", "$DirPM/$file_name");
                        copy("$DirPS/$tmb_file_name", "$DirPM/$tmb_file_name");

                    } elseif (isset($listfields[$DBid])) {
                        if (!empty($field)) {
                            if ($field <> 0) {
                                $field = $catitem['id'] = $temp_arr[$field];
                            } else {
                                $field = '';
                            }
                        }

                    } elseif (isset($multifields[$DBid])) {
                        $listArr = explode(':', $field);
                        $field   = ':';
                        foreach ($listArr as $listId) {
                            if (!empty($listId)) {
                                $catitem['id'] = $temp_arr[$listId];
                                if (!empty($catitem['id'])) {
                                    $field .= $catitem['id'] . ':';
                                }
                            }
                        }
                    }

                    if ($field == ':') {
                        $field = '';
                    }
                    $data[] = $field;

                    $sql .= ', '.$fieldname;
                }
            }

            $data[] = $item['pg_created'];
            $data[] = $item['pg_creator'];
            $data[] = $item['pg_creator'];
            $data[] = $item['pg_lastUpdatedDate'];
            $data[] = $item['pg_creator'];

            $sql .= ', pm_cr_date, pm_cr_uid, pm_author, pm_lu_date, pm_lu_uid ';
            $sql .= ') VALUES ( '.$data[1];
            unset($data[1]);

            foreach ($data as $dkey => $data2)
            {
                if ($data2 == '' && $dkey <> 10) {
                    $sql .= ' , null';
                } else {
                    $sql .= ' ,\''.DataUtil::formatForStore($data2).'\'';
                }
            }
            $sql .= ' )';
            DBUtil::executeSQL($sql);

            $wfData['metaid']       = 0;
            $wfData['module']       = 'PageMaster';
            $wfData['schemaname']   = $pubtype['workflow'];
            $wfData['state']        = $item['pg_approvalState'];
            $wfData['type']         = 1;
            $wfData['obj_table']    = 'pagemaster_pubdata' . $pubtype['id'];
            $wfData['obj_idcolumn'] = 'id';
            $wfData['obj_id']       = $item['pg_id'];
            $wfData['busy']         = 0;
            DBUtil::insertObject($wfData, 'workflows', 'id');
            //$sdf = $sdf +1;
            //if ($sdf > 10) {
            //    exit;
            //}
        }

        $sql = "UPDATE $tablenamePM SET pm_publishdate = null WHERE pm_publishdate = '0000-00-00';";
        DBUtil::executeSQL($sql);

        $sql = "UPDATE $tablenamePM SET pm_expiredate = null WHERE pm_expiredate = '0000-00-00';";
        DBUtil::executeSQL($sql);
    }

    return LogUtil::registerStatus(__('Data import succeded!', $dom));
}
