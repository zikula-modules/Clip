<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Installer
 */

/**
 * Clip Installer.
 */
class Clip_Installer extends Zikula_AbstractInstaller
{
    /**
     * Clip installation
     */
    public function install()
    {
        // create tables
        $tables = array(
            'Clip_Model_Pubfield',
            'Clip_Model_Pubtype',
            'Clip_Model_Pubrelation'
        );

        foreach ($tables as $table) {
            if (!Doctrine_Core::getTable($table)->createTable()) {
                // delete previously created tables
                foreach ($tables as $lasttable) {
                    if ($lasttable == $table) {
                        break;
                    }
                    Doctrine_Core::getTable($lasttable)->dropTable();
                }
                return false;
            }
        }

        // build the default category tree
        self::createCategoryTree();

        // try to create the upload directory
        $tmpdir = self::createTempDir();

        // register the hooks
        HookUtil::registerHookSubscriberBundles($this->version);

        // register persistent event listeners (handlers)
        EventUtil::registerPersistentModuleHandler('Clip', 'zikula.filterutil.get_plugin_classes', array('Clip_EventHandler_Listeners', 'getFilterClasses'));
        EventUtil::registerPersistentModuleHandler('Clip', 'module.content.gettypes', array('Clip_EventHandler_Listeners', 'getTypes'));

        // modvars
        $modvars = array(
            'uploadpath' => $tmpdir,
            'maxperpage' => 100,
            'devmode'    => true
        );
        $this->setVars($modvars);

        return true;
    }

    /**
     * Clip upgrade
     */
    public function upgrade($oldversion)
    {
        switch ($oldversion)
        {
            case '0.4.0':
            case '0.4.1':
            case '0.4.2':
            case '0.4.3':
                self::renameClipTables();
            case '0.4.4':
                // set the new limit for items per page
                $this->setVar('maxperpage', 100);

                if (!self::renameRoutine()) {
                    return '0.4.4';
                }
            case '0.4.5':
                if (!self::migratePubField()) {
                    return '0.4.5';
                }
            case '0.4.6':
                // register persistent event listeners (handlers)
                EventUtil::registerPersistentModuleHandler('Clip', 'zikula.filterutil.get_plugin_classes', array('Clip_EventHandler_Listeners', 'getFilterClasses'));
            case '0.4.7':
                self::tempUpdate047();
            case '0.4.8':
                // register the hooks
                HookUtil::registerHookSubscriberBundles($this->version);
            case '0.4.9':
                if (!Doctrine_Core::getTable('Clip_Model_Pubtype')->changeTable(true)) {
                    return '0.4.9';
                }
                if (!Doctrine_Core::getTable('Clip_Model_Pubrelation')->changeTable(true)) {
                    return '0.4.9';
                }
            case '0.4.10':
                if (!Doctrine_Core::getTable('Clip_Model_Pubfield')->changeTable(true)) {
                    return '0.4.10';
                }
            case '0.4.11':
                // further upgrade handling
                // * rename the columns to drop the pm_ prefix
                // * contenttype stuff
                //   Content_Installer::updateContentType('Clip');
                //   EventUtil::registerPersistentModuleHandler('Clip', 'module.content.gettypes', array('Clip_EventHandler_Listeners', 'getTypes'));
        }

        return true;
    }

    /**
     * Clip deinstallation
     */
    public function uninstall()
    {
        Clip_Generator::loadDataClasses();

        // drop pubtype tables
        $pubtypes = array_keys(Clip_Util::getPubType(-1)->toArray());

        foreach ($pubtypes as $tid) {
            $table = "Clip_Model_Pubdata$tid";
            if (!Doctrine_Core::getTable($table)->dropTable()) {
                return false;
            }
        }

        // FIXME Hooks, Workflows registries deleted?

        // drop base tables
        $tables = array(
            'Clip_Model_Pubfield',
            'Clip_Model_Pubtype',
            'Clip_Model_Pubrelation'
        );

        foreach ($tables as $table) {
            if (!Doctrine_Core::getTable($table)->dropTable()) {
                return false;
            }
        }

        // FIXME delete m2m relation tables

        // delete the category registry and modvars
        CategoryUtil::deleteCategoriesByPath('/__SYSTEM__/Modules/clip', 'path');
        $this->delVars();

        return true;
    }



    /**
     * Default category tree creation.
     */
    private function createCategoryTree()
    {
        $regpath = '/__SYSTEM__/Modules';

        $lang = ZLanguage::getLanguageCode();

        $rootcat = CategoryUtil::getCategoryByPath($regpath.'/clip');
        if (!$rootcat) {
            $rootcat = CategoryUtil::getCategoryByPath($regpath);

            $cat = new Categories_DBObject_Category();
            $cat->setDataField('parent_id', $rootcat['id']);
            $cat->setDataField('name', 'Clip');
            $cat->setDataField('display_name', array($lang => $this->__('Clip')));
            $cat->setDataField('display_desc', array($lang => $this->__('Clip root category')));
            $cat->setDataField('value', '');
            if (!$cat->validate()) {
                return LogUtil::registerError($this->__f('Error! Could not create the [%s] category.', 'Clip'));
            }
            $cat->insert();
            $cat->update();
        }

        $rootcat = CategoryUtil::getCategoryByPath($regpath.'/clip/lists');
        if (!$rootcat) {
            $rootcat = CategoryUtil::getCategoryByPath($regpath.'/clip');

            $cat = new Categories_DBObject_Category();
            $cat->setDataField('parent_id', $rootcat['id']);
            $cat->setDataField('name', 'lists');
            //! this is the 'lists' root category name
            $cat->setDataField('display_name', array($lang => $this->__('lists')));
            $cat->setDataField('display_desc', array($lang => $this->__('Clip lists for its publications')));
            $cat->setDataField('value', '');
            if (!$cat->validate()) {
                return LogUtil::registerError($this->__f('Error! Could not create the [%s] category.', 'lists'));
            }
            $cat->insert();
            $cat->update();
        }

        // create the PM category registry
        $rootcat = CategoryUtil::getCategoryByPath($regpath.'/clip/lists');
        if ($rootcat) {
            // create an entry in the categories registry to the Lists property
            $registry = new Categories_DBObject_Registry();
            $registry->setDataField('modname', 'Clip');
            $registry->setDataField('table', 'clip_pubtypes');
            $registry->setDataField('property', 'Lists');
            $registry->setDataField('category_id', $rootcat['id']);
            if ($registry->validatePostProcess()) {
                $registry->insert();
            }
        } else {
            LogUtil::registerError($this->__f('Error! Could not create the [%s] Category Registry for Clip.', 'Lists'));
        }
    }

    /**
     * Upload directory creation
     */
    private function createTempDir()
    {
        // upload dir creation
        $uploaddir = FileUtil::getDataDirectory().'/Clip/uploads';

        if (!FileUtil::mkdirs($uploaddir, System::getVar('system.chmod_dir', 0777))) {
            LogUtil::registerStatus($this->__f('Clip created the upload directory successfully at [%s]. Be sure that this directory is accessible via web and writable by the webserver.', $tmpdir));
        }

        return $tmpdir;
    }

    /**
     * Transition method to rename PageMaster to Clip
     */
    private static function renameClipTables()
    {
        $tables = DBUtil::getTables();

        $tables['pagemaster_relations'] = DBUtil::getLimitedTablename('pagemaster_relations');
        $tables['pagemaster_pubfields'] = DBUtil::getLimitedTablename('pagemaster_pubfields');
        $tables['pagemaster_pubtypes']  = DBUtil::getLimitedTablename('pagemaster_pubtypes');

        $tables['clip_relations'] = DBUtil::getLimitedTablename('clip_relations');
        $tables['clip_pubfields'] = DBUtil::getLimitedTablename('clip_pubfields');
        $tables['clip_pubtypes']  = DBUtil::getLimitedTablename('clip_pubtypes');

        $serviceManager = ServiceUtil::getManager();
        $dbtables = $serviceManager['dbtables'];
        $serviceManager['dbtables'] = array_merge($dbtables, (array)$tables);

        $existingtables = DBUtil::metaTables();

        // detects and update the relations table
        if (in_array(DBUtil::getLimitedTablename('pagemaster_relations'), $existingtables)) {
            DBUtil::renameTable('pagemaster_relations', 'clip_relations');
        }
        $tableObj = Doctrine_Core::getTable('Clip_Model_Pubrelation');
        if (in_array(DBUtil::getLimitedTablename('pagemaster_relations'), $existingtables)) {
            $tableObj->dropTable();
        }
        $tableObj->createTable();

        // rename the others
        DBUtil::renameTable('pagemaster_pubfields', 'clip_pubfields');
        DBUtil::renameTable('pagemaster_pubtypes',  'clip_pubtypes');

        $pubtypes = Doctrine_Core::getTable('Clip_Model_Pubtype')->selectFieldArray('tid');
        foreach ($pubtypes as $tid) {
            if (in_array(DBUtil::getLimitedTablename('pagemaster_pubdata'.$tid), $existingtables)) {
                $tables['pagemaster_pubdata'.$tid] = DBUtil::getLimitedTablename('pagemaster_pubdata'.$tid);
                $tables['clip_pubdata'.$tid] = DBUtil::getLimitedTablename('clip_pubdata'.$tid);
                $serviceManager['dbtables'] = array_merge($dbtables, (array)$tables);
                DBUtil::renameTable('pagemaster_pubdata'.$tid, 'clip_pubdata'.$tid);
            }
        }
    }

    /**
     * Transition method to rename PageMaster to Clip
     */
    private function renameRoutine()
    {
        // update db tables values
        $tables = DBUtil::getTables();
        $tables['clip_pubfields'] = DBUtil::getLimitedTablename('clip_pubfields');
        $tables['clip_pubtypes']  = DBUtil::getLimitedTablename('clip_pubtypes');

        $sql = array();

        // fieldplugin class names
        $sql[] = "UPDATE {$tables['clip_pubfields']} SET pm_fieldplugin = REPLACE(pm_fieldplugin, 'PageMaster_', 'Clip_')";

        // fieldplugin type change: X to C(65535)
        $sql[] = "UPDATE {$tables['clip_pubfields']} SET pm_fieldtype = REPLACE(pm_fieldtype, 'X', 'C(65535)')";

        // workflow registries
        $sql[] = "UPDATE {$tables['workflows']} SET module = 'Clip', obj_table = REPLACE(obj_table, 'pagemaster_', 'clip_') WHERE module = 'PageMaster' OR module = 'pagemaster'";

        // rename the category registries
        $sql[] = "UPDATE {$tables['categories_registry']} SET crg_modname = 'Clip', crg_table  = REPLACE(crg_table , 'pagemaster_', 'clip_') WHERE crg_modname = 'PageMaster' OR crg_modname = 'pagemaster'";

        // rename the permissions component
        $sql[] = "UPDATE {$tables['group_perms']} SET z_component  = REPLACE(z_component , 'pagemaster', 'clip')";

        // replace any pm_* in the pubtype sortfields
        $sql[] = "UPDATE {$tables['clip_pubtypes']} SET pm_sortfield1 = REPLACE(pm_sortfield1, 'pm_', 'core_'), pm_sortfield2 = REPLACE(pm_sortfield2, 'pm_', 'core_'), pm_sortfield3 = REPLACE(pm_sortfield3, 'pm_', 'core_')";

        // fill the output/input sets if empty
        $sql[] = "UPDATE {$tables['clip_pubtypes']} SET pm_filename = pm_title WHERE pm_filename = ''";
        $sql[] = "UPDATE {$tables['clip_pubtypes']} SET pm_formname = pm_title WHERE pm_formname = ''";

        // map the field classnames to IDs
        $plugins = array(
            'Checkbox' => array(
                'Clip_Form_Plugin_Checkbox',
                'pmformcheckboxinput'
            ),
            'Date' => array(
                'Clip_Form_Plugin_Date',
                'pmformdateinput'
            ),
            'Email' => array(
                'Clip_Form_Plugin_Email',
                'pmformemailinput'
            ),
            'Float' => array(
                'Clip_Form_Plugin_Float',
                'pmformfloatinput'
            ),
            'Image' => array(
                'Clip_Form_Plugin_Image',
                'pmformimageinput'
            ),
            'Int' => array(
                'Clip_Form_Plugin_Int',
                'pmformintinput'
            ),
            'List' => array(
                'Clip_Form_Plugin_List',
                'pmformlistinput'
            ),
            'Ms' => array(
                'Clip_Form_Plugin_Ms',
                'pmformmsinput'
            ),
            'MultiCheck' => array(
                'Clip_Form_Plugin_MultiCheck',
                'pmformmulticheckinput'
            ),
            'MultiList' => array(
                'Clip_Form_Plugin_MultiList',
                'pmformmultilistinput'
            ),
            'Pub' => array(
                'Clip_Form_Plugin_Pub',
                'pmformpubinput'
            ),
            'String' => array(
                'Clip_Form_Plugin_String',
                'pmformstringinput'
            ),
            'Text' => array(
                'Clip_Form_Plugin_Text',
                'pmformtextinput'
            ),
            'Upload' => array(
                'Clip_Form_Plugin_Upload',
                'pmformuploadinput'
            ),
            'Url' => array(
                'Clip_Form_Plugin_Url',
                'pmformurlinput'
            ),
            'RadioList' => array(
                'Clip_Form_Plugin_RadioList'
            )
        );
        foreach ($plugins as $newid => $oldnames) {
            foreach ($oldnames as $oldname) {
                $sql[] = "UPDATE {$tables['clip_pubfields']} SET pm_fieldplugin = REPLACE(pm_fieldplugin, '{$oldname}', '{$newid}')";
            }
        }

        // Image and Upload fieldplugin type change: C(255) to C(1024)
        $sql[] = "UPDATE {$tables['clip_pubfields']} SET pm_fieldtype = 'C(1024)' WHERE pm_fieldplugin = 'Image' OR pm_fieldplugin = 'Upload'";

        foreach ($sql as $q) {
            if (!DBUtil::executeSQL($q)) {
                return LogUtil::registerError($this->__('Error! Update attempt failed.')." - $sql");
            }
        }

        // updates system startpage and default shortURL module if it was PM
        $sysvars = array('startpage', 'shorturlsdefaultmodule');
        foreach ($sysvars as $sysname) {
            $sysvar = System::getVar($sysname);
            if ($sysvar == 'PageMaster' || $sysvar == 'pagemaster') {
                System::setVar($sysname, 'Clip');
            }
        }

        // rename the filename/formname columns
        $ptcols = array_keys(DBUtil::metaColumnNames('clip_pubtypes'));
        if (in_array('pm_filename', $ptcols)) {
            DoctrineUtil::renameColumn('clip_pubtypes', 'pm_filename', 'pm_outputset');
        }
        if (in_array('pm_formname', $ptcols)) {
            DoctrineUtil::renameColumn('clip_pubtypes', 'pm_formname', 'pm_inputset');
        }
        // old installs presents this case sensitive error
        if (in_array('pm_defaultFilter', $ptcols)) {
            DoctrineUtil::renameColumn('clip_pubtypes', 'pm_defaultFilter', 'pm_defaultfilter');
        }

        if (!Doctrine_Core::getTable('Clip_Model_Pubtype')->changeTable()) {
            return false;
        }

        // fills the empty publish dates
        $pubtypes = Doctrine_Core::getTable('Clip_Model_Pubtype')->selectFieldArray('tid');
        if (!empty($pubtypes)) {
            // update each pubdata table
            // and update the new field value with the good old pm_cr_uid
            $existingtables = DBUtil::metaTables();
            foreach ($pubtypes as $tid) {
                $tables['clip_pubdata'.$tid] = DBUtil::getLimitedTablename('clip_pubdata'.$tid);
                if (in_array($tables['clip_pubdata'.$tid], $existingtables)) {
                    $sql = "UPDATE {$tables['clip_pubdata'.$tid]} SET pm_publishdate = pm_cr_date WHERE pm_publishdate IS NULL";

                    if (!DBUtil::executeSQL($sql)) {
                        return LogUtil::registerError($this->__('Error! Update attempt failed.'));
                    }
                }
            }
        }

        return true;
    }

    /**
     * Deprecation of the Publication field.
     */
    private static function migratePubField()
    {
        if (!Doctrine_Core::getTable('Clip_Model_Pubfield')->changeTable()) {
            return false;
        }

        $pubtypes = Doctrine_Core::getTable('Clip_Model_Pubtype')->selectFieldArray('inputset', null, '', false, 'tid');

        $fields = Doctrine_Core::getTable('Clip_Model_Pubfield')->selectCollection("fieldplugin = 'Pub'", 'tid');

        // 1. map all the existing 'Publication' fields
        $tid = 0;
        $relations = array();
        foreach ($fields as $field) {
            // assigns the related tids
            $tid1 = $field['tid'];

            $tid2 = explode(';', $field['typedata']);
            $tid2 = $tid2[0];

            // discard non-existing pubtypes
            if (!isset($pubtypes[$tid1]) || !isset($pubtypes[$tid2])) {
                continue;
            }

            $key = Clip_Util::getStringPrefix($field['name']);
            $relations[$tid1][$tid2][$key]['ids'][$field['id']] = $field['name'];
            $relations[$tid1][$tid2][$key]['info']  = array(
                'title'       => $field['title'],
                'description' => $field['description']
            );
        }

        // 2. Create the new relations
        foreach ($relations as $tid1 => $y) {
            foreach ($y as $tid2 => $x) {
                foreach ($x as $fieldname => $v) {
                    $reldata = array(
                        'tid1'   => $tid1,
                        'alias1' => $fieldname,
                        'title1' => $v['info']['title'],
                        'descr1' => $v['info']['description'],
                        'tid2'   => $tid2,
                        'alias2' => $pubtypes[$tid1].'_'.$fieldname,
                        'title2' => $v['info']['title']
                    );
                    $relation = new Clip_Model_Pubrelation();

                    // check if we need a n:n relation
                    if (count($v['ids']) > 1) {
                        // setup the n:n relation
                        $reldata['type'] = 3;
                    } else {
                        // setup the n:1 relation
                        $reldata['type'] = 2;
                    }

                    $relation->fromArray($reldata);
                    $relation->save();

                    $relations[$tid1][$tid2][$fieldname]['relid'] = $relation['id'];
                }
            }
        }

        // 3. migrate the data to the relations
        Clip_Generator::loadDataClasses();
        // update the pubdata tables
        $existingtables = DBUtil::metaTables();
        foreach (array_keys($pubtypes) as $tid) {
            $table = DBUtil::getLimitedTablename('clip_pubdata'.$tid);
            if (!in_array($table, $existingtables)) {
                Doctrine_Core::getTable('Clip_Model_Pubdata'.$tid)->createTable();
            }
        }
        unset($existingtables);

        // move the data and rename/drop the columns
        $dbfields = array();
        foreach ($relations as $tid1 => $y) {
            foreach ($y as $tid2 => $x) {
                foreach ($x as $fieldname => $v) {
                    if (!isset($dbfields[$tid1])) {
                        $dbfields[$tid1] = array_keys(DBUtil::metaColumnNames('clip_pubdata'.$tid1));
                    }
                    $tbl1 = Doctrine_Core::getTable('Clip_Model_Pubdata'.$tid1);
                    $tbl2 = Doctrine_Core::getTable('Clip_Model_Pubdata'.$tid2);

                    // process n:n relations
                    if (count($v['ids']) > 1) {
                        // create the n:n table
                        $relclass = 'Clip_Model_Relation'.$v['relid'];
                        Doctrine_Core::getTable($relclass)->createTable();

                        foreach ($v['ids'] as $fid => $name) {
                            // verify that the field exists
                            if (!in_array('pm_'.$fid, $dbfields[$tid1])) {
                                continue;
                            }
                            // map all the values to the new n:n table
                            $where = array(
                                $name.' IS NOT NULL',
                                array($name.' != ?', 0),
                                array('core_online = ?', 1)
                            );
                            $ids = $tbl1->selectFieldArray($name, $where, '', false, 'id');

                            // migrate the existing values if exists
                            if ($ids) {
                                $where = array(
                                    array('core_pid IN ?', array_unique(array_values($ids))),
                                    array('core_online = ?', 1)
                                );
                                $pids = $tbl2->selectFieldArray('id', $where, '', false, 'core_pid');

                                // fill the relations map
                                foreach ($ids as $id => $pid) {
                                    if (!isset($pids[$pid])) {
                                        $pids[$pid] = $tbl2->selectFieldBy('id', $pid, 'core_pid');
                                    }

                                    $rec = new $relclass;
                                    $rec['rel_'.$relation['id'].'_1'] = $id;
                                    $rec['rel_'.$relation['id'].'_2'] = $pids[$pid];
                                    $rec->save();
                                }
                            }

                            // drop the field
                            DoctrineUtil::dropColumn('clip_pubdata'.$tid1, 'pm_'.$fid);
                        }
                    } else {
                        // process the n:1 relation
                        foreach ($v['ids'] as $fid => $name) {
                            // verify that the field exists
                            if (!in_array('pm_'.$fid, $dbfields[$tid1])) {
                                continue;
                            }
                            // update all the zero values to NULL
                            $q = $tbl1->createQuery();
                            $q->update()
                              ->set($name, 'NULL')
                              ->where($name.' = 0')
                              ->execute();

                            $where = array(
                                $name.' IS NOT NULL',
                                array('core_online = ?', 1)
                            );
                            $ids = $tbl1->selectFieldArray($name, $where, '', false, 'id');

                            if ($ids) {
                                $where = array(
                                    'whereIn' => array('core_pid', array_unique(array_values($ids))),
                                    array('core_online = ?', 1)
                                );
                                $pids = $tbl2->selectFieldArray('id', $where, '', false, 'core_pid');

                                // updates the PIDs to IDs
                                foreach ($ids as $id => $pid) {
                                    if (!isset($pids[$pid])) {
                                        $pids[$pid] = $tbl2->selectFieldBy('id', $pid, 'core_pid');
                                    }

                                    if (!$pids[$pid]) {
                                        continue;
                                    }

                                    $q = $tbl1->createQuery();
                                    $q->update()
                                      ->set($name, $pids[$pid])
                                      ->where('id = ?', $id)
                                      ->execute();
                                }
                            }

                            // rename the field
                            DoctrineUtil::renameColumn('clip_pubdata'.$tid1, 'pm_'.$fid, 'pm_rel_'.$v['relid']);
                        }
                    }
                }
            }
        }
        unset($dbfields);

        $fields->delete();

        return true;
    }

    /**
     * Deprecation of the Publication field.
     */
    private static function tempUpdate047()
    {
        // TEMP UPDATE: Image fieldplugin type change: C(255) to C(1024)
        $table = DBUtil::getLimitedTablename('clip_pubfields');
        $sql[] = "UPDATE $table SET pm_fieldtype = 'C(1024)' WHERE pm_fieldplugin = 'Image' OR pm_fieldplugin = 'Upload'";

        // update the pubtypes table
        Clip_Generator::loadDataClasses();
        $pubtypes = Doctrine_Core::getTable('Clip_Model_Pubtype')->selectFieldArray('tid');
        foreach ($pubtypes as $tid) {
            Doctrine_Core::getTable('Clip_Model_Pubdata'.$tid)->changeTable();
        }
    }

    /**
     * map old ContentType names to new
     * @return array
     */
    public function LegacyContentTypeMap()
    {
        $oldToNew = array(
            'pagesetter_pub' => 'ClipPub',
            'pagesetter_publist' => 'ClipPublist'
        );
        return $oldToNew;
    }

}
