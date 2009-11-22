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
//update pn_pagemaster_pubfields set pm_fieldplugin = SUBSTRING( SUBSTRING( pm_fieldplugin,10 ),1,INSTR(SUBSTRING( pm_fieldplugin,10 ),'.')-1)
function pagemaster_init()
{
    $dom = ZLanguage::getModuleDomain('pagemaster');
    // create table
    if (!DBUtil::createTable('pagemaster_pubfields')) {
        return false;
    }

    if (!DBUtil::createTable('pagemaster_pubtypes')) {
        return false;
    }

    Loader::loadClass('CategoryUtil');
    Loader::loadClassFromModule('Categories', 'Category');

    $rootcat = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules');
    $lang = pnUserGetLang();

    $cat = new PNCategory();
    $cat->setDataField('parent_id', $rootcat['id']);
    $cat->setDataField('name', 'pagemaster');
    $cat->setDataField('display_name', array ($lang => 'pagemaster'));
    $cat->setDataField('display_desc', array ($lang => 'module category for pagemaster'));
    $cat->insert();
    $cat->update();

    $rootcat = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules/pagemaster');
    $cat = new PNCategory();
    $cat->setDataField('parent_id', $rootcat['id']);
    $cat->setDataField('name', 'lists');
    $cat->setDataField('display_name', array ($lang => 'lists'));
    $cat->setDataField('display_desc', array ($lang => 'contains lists for pagemaster publications'));
    $cat->insert();
    $cat->update();
    return (true);
}

function pagemaster_upgrade($from_version)
{
    $dom = ZLanguage::getModuleDomain('pagemaster');
    echo $from_version;
  switch ($oldversion)
    {
    case '0.1' :
        // add the urltitle field to the pubtypes table
        if (!DBUtil::changeTable('pagemaster_pubtypes')) {
            return LogUtil::registerError(_CHANGETABLEFAILED);
        }
        // create the index
        if (!DBUtil::createIndex('urltitle', 'pagemaster_pubtypes', array('urltitle'))) {
            return LogUtil::registerError(__('Error! Index creation failed.', $dom));
        }
        // update the urltitle field of each existing pubtype
        $types = DBUtil::selectObjectArray('pagemaster_pubtypes');
        if ($types !== false) {
            foreach (array_keys($types) as $k) {
                $types[$k]['urltitle'] = DataUtil::formatPermalink($types[$k]['title']);
            }
            if (!DBUtil::updateObjectArray($types, 'pagemaster_pubtypes', 'tid')) {
                return LogUtil::registerError(__('Error! Update attempt failed.', $dom));
            }
        }
        return pagemaster_upgrade('0.2');

    case '0.2':
        // fix the upload path to a root-relative one
        $uploadpath = pnModGetVar('pagemaster', 'uploadpath');
        $siteroot   = substr(pnServerGetVar('DOCUMENT_ROOT'), 0, -1).pnGetBaseURI().'/';
        $newpath    = str_replace($siteroot, '', $uploadpath);
        if (StringUtil::right($newpath, 1) == '/') {
            $newpath = StringUtil::left($newpath, strlen($newpath) - 1);
        }
        pnModSetVar('pagemaster', 'uploadpath', $newpath);

        // fix the pm_author field to pn_cr_uid
        $pubtypes = DBUtil::selectFieldArray('pagemaster_pubtypes', 'tid');
        if (empty($pubtypes)) {
            // nothing to update
            break;
        }
        $tables = pnDBGetTables();
        // update each pubdata table
        // and update the new field value with the good old pm_cr_uid
        foreach ($pubtypes as $tid) {
            if (!DBUtil::changeTable('pagemaster_pubdata'.$tid)) {
                 return LogUtil::registerError(_CHANGETABLEFAILED);
            }
            $sql = "UPDATE {$tables['pagemaster_pubdata'.$tid]} SET pm_author = pm_cr_uid WHERE pm_author = '0'";
            if (!DBUtil::executeSQL($sql)) {
                return LogUtil::registerError(__('Error! Table update failed.', $dom));
            }
        }

        // fix the upload path dependency on the plugins data
        // 1. get all the fields based on image and upload plugins
        $pluginfield = $tables['pagemaster_pubfields_column']['fieldplugin'];
        $where       = "$pluginfield = 'function.pmformimageinput.php' OR $pluginfield = 'function.pmformuploadinput.php'";
        $columnArray = array('tid', 'name');
        $fields      = DBUtil::selectObjectArray('pagemaster_pubfields', $where, '', -1, -1, '', null, null, $columnArray);
        if (empty($fields)) {
            // nothing to update
            break;
        }
        // 2. build an array of the fields per tid
        $fieldsinpubtype = array();
        foreach ($fields as $field) {
            $fieldsinpubtype[$field['tid']][] = $field;
        }
        unset($fields);
        // 3. get, extract and update the publications data
        // make global the upload path
        global $pmuploadpath;
        $pmuploadpath = $uploadpath.'/';
        // define the function that will update the paths
        function pmupdate_path(&$field) {
            // discard empty fields
            if (empty($field)) {
                return;
            }
            // remove the upload path of the field
            global $pmuploadpath;
            $field = str_replace($pmuploadpath, '', $field);
        }
        // define the function that will update each field data
        function pmupdate_fielddata(&$fielddata) {
            if (empty($fielddata) || !is_array($data = @unserialize($fielddata))) {
                // it's the publication id
                // or an empty image/upload
                return;
            }
            // update the publication image/upload field path
            array_walk($data, 'pmupdate_path');
            // save the updated data
            $fielddata = serialize($data);
        }

        $pubtypes = array_keys($fieldsinpubtype);
        foreach ($pubtypes as $tid) {
            // build the column array of the fields to update
            // and to not load all the publications data, just the id and fields data
            $columnArray = array('id');
            foreach ($fieldsinpubtype[$tid] as $field) {
                $columnArray[] = $field['name'];
            }
            $fieldsdata = DBUtil::selectObjectArray('pagemaster_pubdata'.$tid, '', '', -1, -1, 'id', null, null, $columnArray);
            if (empty($fieldsdata)) {
                // nothing to update in this pub
                continue;
            }
            // update each publication
            $pubids = array_keys($fieldsdata);
            foreach ($pubids as $id) {
                // unserialize and update the data of the plugin
                array_walk($fieldsdata[$id], 'pmupdate_fielddata');
            }
            // update the publications data
            if (!DBUtil::updateObjectArray($fieldsdata, 'pagemaster_pubdata'.$tid)) {
                return LogUtil::registerError(__('Error! Table update failed.', $dom));
            }
        }

    case '0.2.1':
        $tables = pnDBGetTables();
        $sql = "UPDATE {$tables['pagemaster_pubfields']} set pm_fieldplugin = SUBSTRING( SUBSTRING( pm_fieldplugin,10 ),1,INSTR(SUBSTRING( pm_fieldplugin,10 ),'.')-1)";
        if (!DBUtil::executeSQL($sql)) {
            return LogUtil::registerError(__('Error! Table update failed.', $dom));
        }
    }

    return true;
}

function pagemaster_delete()
{
    $dom = ZLanguage::getModuleDomain('pagemaster');
    $pubtypes = DBUtil::selectObjectArray('pagemaster_pubtypes');
    foreach ($pubtypes as $pubtype) {
        DBUtil::dropTable('pagemaster_pubdata'.$pubtype['tid']);
    }

    if (!DBUtil::dropTable('pagemaster_pubfields')) {
        return false;
    }

    if (!DBUtil::dropTable('pagemaster_pubtypes')) {
        return false;
    }

    Loader::loadClass('CategoryUtil');
    CategoryUtil::deleteCategoriesByPath('/__SYSTEM__/Modules/pagemaster', 'path');
    pnModDelVar('pagemaster');

    return true;
}
