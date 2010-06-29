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
 * PageMaster installation
 */
function PageMaster_init()
{
    $dom = ZLanguage::getModuleDomain('PageMaster');

    // create table
    if (!DBUtil::createTable('pagemaster_pubfields')) {
        return false;
    }

    if (!DBUtil::createTable('pagemaster_pubtypes')) {
        return false;
    }

    // build the default category tree
    $regpath = '/__SYSTEM__/Modules';

    Loader::loadClass('CategoryUtil');
    Loader::loadClassFromModule('Categories', 'Category');
    Loader::loadClassFromModule('Categories', 'CategoryRegistry');

    $lang = ZLanguage::getLanguageCode();

    $rootcat = CategoryUtil::getCategoryByPath($regpath.'/pagemaster');
    if (!$rootcat) {
        $rootcat = CategoryUtil::getCategoryByPath($regpath);

        $cat = new PNCategory();
        $cat->setDataField('parent_id', $rootcat['id']);
        $cat->setDataField('name', 'PageMaster');
        $cat->setDataField('display_name', array($lang => __('PageMaster', $dom)));
        $cat->setDataField('display_desc', array($lang => __('PageMaster root category', $dom)));
        if (!$cat->validate('admin')) {
            return LogUtil::registerError(__f('Error! Could not create the [%s] category.', 'PageMaster', $dom));
        }
        $cat->insert();
        $cat->update();
    }

    $rootcat = CategoryUtil::getCategoryByPath($regpath.'/pagemaster/lists');
    if (!$rootcat) {
        $rootcat = CategoryUtil::getCategoryByPath($regpath.'/pagemaster');

        $cat = new PNCategory();
        $cat->setDataField('parent_id', $rootcat['id']);
        $cat->setDataField('name', 'lists');
        //! this is the 'lists' root category name
        $cat->setDataField('display_name', array($lang => __('lists', $dom)));
        $cat->setDataField('display_desc', array($lang => __('PageMaster lists for its publications', $dom)));
        if (!$cat->validate('admin')) {
            return LogUtil::registerError(__f('Error! Could not create the [%s] category.', 'lists', $dom));
        }
        $cat->insert();
        $cat->update();
    }

    // create the PM category registry
    $rootcat = CategoryUtil::getCategoryByPath($regpath.'/pagemaster/lists');
    if ($rootcat) {
        // create an entry in the categories registry to the Lists property
        $registry = new PNCategoryRegistry();
        $registry->setDataField('modname', 'PageMaster');
        $registry->setDataField('table', 'pagemaster_pubtypes');
        $registry->setDataField('property', 'Lists');
        $registry->setDataField('category_id', $rootcat['id']);
        $registry->insert();
    } else {
        LogUtil::registerError(__f('Error! Could not create the [%s] Category Registry for PageMaster.', 'Lists', $dom));
    }

    // modvars
    // upload dir creation if the temp dir is not outside the root (relative path)
    $tempdir = CacheUtil::getLocalDir();
    $pmdir   = $tempdir.'/PageMaster';
    if (StringUtil::left($tempdir, 1) <> '/') {
        if (CacheUtil::createLocalDir('PageMaster')) {
            LogUtil::registerStatus(__f('PageMaster created the upload directory successfully at [%s]. Be sure that this directory is accessible via web and writable by the webserver.', $pmdir, $dom));
        }
    } else {
        LogUtil::registerStatus(__f('PageMaster could not create the upload directory [%s]. Please create an upload directory, accessible via web and writable by the webserver.', $pmdir, $dom));
    }
    $modvars = array(
        'uploadpath' => $pmdir,
        'devmode'    => true
    );
    pnModSetVars('PageMaster', $modvars);

    return true;
}

/**
 * PageMaster upgrade
 */
function PageMaster_upgrade($oldversion)
{
    //update pn_pagemaster_pubfields set pm_fieldplugin = SUBSTRING( SUBSTRING( pm_fieldplugin,10 ),1,INSTR(SUBSTRING( pm_fieldplugin,10 ),'.')-1) //FIXME

    $dom = ZLanguage::getModuleDomain('PageMaster');

    switch ($oldversion)
    {
        case '0.1' :
            // add the urltitle field to the pubtypes table
            if (!DBUtil::changeTable('pagemaster_pubtypes')) {
                return '0.1';
            }
            // update the urltitle field of each existing pubtype
            $types = DBUtil::selectObjectArray('pagemaster_pubtypes');
            if ($types !== false) {
                foreach (array_keys($types) as $k) {
                    $types[$k]['urltitle'] = DataUtil::formatPermalink($types[$k]['title']);
                }
                if (!DBUtil::updateObjectArray($types, 'pagemaster_pubtypes', 'tid')) {
                    LogUtil::registerError(__('Error! Update attempt failed.', $dom));
                    return '0.1';
                }
            }

        case '0.2':
            // fix the upload path to a root-relative one
            $uploadpath = pnModGetVar('PageMaster', 'uploadpath');
            $siteroot   = substr(pnServerGetVar('DOCUMENT_ROOT'), 0, -1).pnGetBaseURI().'/';
            $newpath    = str_replace($siteroot, '', $uploadpath);
            if (StringUtil::right($newpath, 1) == '/') {
                $newpath = StringUtil::left($newpath, strlen($newpath) - 1);
            }
            pnModSetVar('PageMaster', 'uploadpath', $newpath);

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
                     return '0.2';
                }
                $sql = "UPDATE {$tables['pagemaster_pubdata'.$tid]} SET pm_author = pm_cr_uid WHERE pm_author = '0'";
                if (!DBUtil::executeSQL($sql)) {
                    LogUtil::registerError(__('Error! Update attempt failed.', $dom));
                    return '0.2';
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
                    LogUtil::registerError(__('Error! Update attempt failed.', $dom));
                    return '0.2';
                }
            }
            unset($fieldsinpubtype);
            unset($fieldsdata);
    
        case '0.2.1':
            $tables = pnDBGetTables();
            $sql = "UPDATE {$tables['pagemaster_pubfields']} set pm_fieldplugin = SUBSTRING( SUBSTRING( pm_fieldplugin,10 ),1,INSTR(SUBSTRING( pm_fieldplugin,10 ),'.')-1)";
            if (!DBUtil::executeSQL($sql)) {
                LogUtil::registerError(__('Error! Update attempt failed.', $dom));
                return '0.2.1';
            }

        case '0.3.0':
        case '0.3.1':
        case '0.3.2':
        case '0.3.3':
            // new modvar: development mode
            pnModSetVars('PageMaster', 'devmode', true);

            // update the table definitions of some fields
            $tochange = array(
                'pmformcheckboxinput' => 'L',
                'pmformintinput' => 'I4',
                'pmformlistinput' => 'I4',
                'pmformpubinput' => 'I4',
                'pmformurlinput' => 'C(512)'
            );
            foreach ($tochange as $plugin => $dbtype) {
                $record = array('fieldtype' => $dbtype);
                DBUtil::updateObject($record, 'pagemaster_pubfields', "pm_fieldplugin = '$plugin'");
            }

            // reload the table definitions
            pnModDBInfoLoad('PageMaster', '', true);

            // update the tables
            $tables   = pnDBGetTables();
            $pubtypes = DBUtil::selectFieldArray('pagemaster_pubtypes', 'tid');

            // process the tables to update
            $toupdate = array('pubtypes', 'pubfields', 'relations');
            $pubdatas = array();
            foreach ($pubtypes as $tid) {
                if (isset($tables["pagemaster_pubdata$tid"])) {
                    $pubdatas[] = "pubdata$tid";
                }
            }

            $toupdate = array_merge($toupdate, $pubdatas);
            foreach ($toupdate as $table) {
                if (!DBUtil::changeTable("pagemaster_$table")) {
                    return '0.3.3';
                }
            }

            // update the publications l2 language codes to l3
            foreach ($pubdatas as $pubdata) {
                $langs = DBUtil::selectFieldArray("pagemaster_$pubdata", 'core_language', "pm_language <> ''", '', true);
                foreach ($langs as $l3) {
                   if ($l2 = ZLanguage::translateLegacyCode($l3)) {
                       $record = array('core_language' => $l2);
                       DBUtil::updateObject($record, "pagemaster_$pubdata", "pm_language = '$l3'");
                   }
                }
            }

        case '0.3.4':
            // create the PM category registry
            Loader::loadClass('CategoryUtil');
            Loader::loadClassFromModule('Categories', 'Category');
            Loader::loadClassFromModule('Categories', 'CategoryRegistry');

            $rootcat = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules/pagemaster/lists');
            if (!$rootcat) {
                $rootcat = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules/Global');
                if (!$rootcat) {
                    LogUtil::registerError(__f('Error! Category path not found [%s].', '/Modules/pagemaster/lists', $dom));
                    LogUtil::registerError(__f('Error! Category path not found [%s].', '/Modules/Global', $dom));
                    return '0.3.4';
                }
            }
            // create an entry in the categories registry to the Lists property
            $registry = new PNCategoryRegistry();
            $registry->setDataField('modname', 'PageMaster');
            $registry->setDataField('table', 'pagemaster_pubtypes');
            $registry->setDataField('property', 'Lists');
            $registry->setDataField('category_id', $rootcat['id']);
            $registry->insert();

        case '0.4.0':
        case '0.4.1':
            // further upgrade handling
    }

    return true;
}

/**
 * PageMaster deinstallation
 */
function PageMaster_delete()
{
    $pubtypes = DBUtil::selectObjectArray('pagemaster_pubtypes');

    foreach ($pubtypes as $pubtype) {
        DBUtil::dropTable('pagemaster_pubdata'.$pubtype['tid']);
    }

    // FIXME Hooks, Workflows registries deleted?

    if (!DBUtil::dropTable('pagemaster_pubfields')) {
        return false;
    }

    if (!DBUtil::dropTable('pagemaster_pubtypes')) {
        return false;
    }

    Loader::loadClass('CategoryUtil');
    CategoryUtil::deleteCategoriesByPath('/__SYSTEM__/Modules/pagemaster', 'path');
    pnModDelVar('PageMaster');

    return true;
}
