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
            'Clip_Model_Grouptype',
            'Clip_Model_Pubrelation',
            'Clip_Model_WorkflowVars'
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

        // try to create the Clip directories
        $dirs = self::createDirectories(array('upload', 'models'));

        // modvars
        $modvars = array(
            'uploadpath' => $dirs['upload'],
            'modelspath' => $dirs['models'],
            'pubtype'    => null,
            'shorturls'  => 'htm',
            'maxperpage' => 100,
            'commontpls' => false,
            'devmode'    => true
        );
        $this->setVars($modvars);

        // register the ClipModels in the created path
        ZLoader::addAutoloader('ClipModels', realpath(StringUtil::left($dirs['models'], -11)));

        //  install: default grouptypes and pubtypes
        $this->createGrouptypesTree();
        Clip_Util::installDefaultypes();

        if (ModUtil::available('Content')) {
            // include the integration with the Content module
            Content_Installer::updateContentType('Clip');
        }

        // register persistent event listeners (handlers)
        EventUtil::registerPersistentModuleHandler('Clip', 'zikula.filterutil.get_plugin_classes', array('Clip_EventHandler_Listeners', 'getFilterClasses'));
        EventUtil::registerPersistentModuleHandler('Clip', 'module.content.gettypes', array('Clip_EventHandler_Listeners', 'getContentTypes'));

        return true;
    }

    /**
     * Clip upgrade.
     */
    public function upgrade($oldversion)
    {
        if (version_compare($oldversion, '0.4.6') >= 0) {
            Clip_Util::boot();
        }

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
                if (!$this->migratePubField()) {
                    return '0.4.5';
                }
            case '0.4.6':
                // register persistent event listeners (handlers)
                EventUtil::registerPersistentModuleHandler('Clip', 'zikula.filterutil.get_plugin_classes', array('Clip_EventHandler_Listeners', 'getFilterClasses'));
            case '0.4.7':
                self::tempUpdate047();
                //self::updatePubTables();
            case '0.4.8':
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
            case '0.4.12':
                $this->createCategoryTree();
            case '0.4.13':
                if (!Doctrine_Core::getTable('Clip_Model_Grouptype')->createTable()) {
                    return '0.4.13';
                }
                if (!Doctrine_Core::getTable('Clip_Model_Pubtype')->changeTable()) {
                    return '0.4.13';
                }
                $this->createGrouptypesTree();
            case '0.4.14':
            case '0.4.15':
                // update the permission schema
                $table = DBUtil::getLimitedTablename('group_perms');
                DBUtil::executeSQL("UPDATE $table SET component = 'Clip:.*?:display' WHERE component = 'Clip:full:'");
                // regenerate the hook information
                $regtables = array('hook_runtime' => 'sowner', 'hook_binding' => 'sowner', 'hook_subscriber' => 'owner');
                foreach ($regtables as $rtable => $rfield) {
                    $table = DBUtil::getLimitedTablename($rtable);
                    DBUtil::executeSQL("DELETE FROM $table WHERE $rfield = 'Clip'");
                }
                // register the pubtype hooks
                $this->version->setupPubtypeBundles();
                HookUtil::registerSubscriberBundles($this->version->getHookSubscriberBundles());
            case '0.4.16':
                $this->setVar('shorturls', 'htm');
            case '0.4.17':
                if (!Doctrine_Core::getTable('Clip_Model_WorkflowVars')->createTable()) {
                    return '0.4.17';
                }
                if (!$this->upgradeDBpre09()) {
                    return '0.4.17';
                }
            case '0.4.18':
                $dirs = self::createDirectories(array('models'), true);
                $this->setVar('modelspath', $dirs['models']);
            case '0.4.19':
                if (!self::introduceUrltitle()) {
                    return false;
                }
            case '0.4.20':
                if (!self::pubtypeConfigs()) {
                    return false;
                }
            case '0.4.21':
                $this->setVar('pubtype', null);
            case '0.4.22':
                self::updatePubTables();
            case '0.9.0':
            case '0.9.1':
                $this->setVar('commontpls', false);
                // update the model generator changes
                Clip_Generator::resetModels();
                // include the integration with the Content module if available
                if (ModUtil::available('Content')) {
                    Content_Installer::updateContentType('Clip');
                }
                EventUtil::registerPersistentModuleHandler('Clip', 'module.content.gettypes', array('Clip_EventHandler_Listeners', 'getContentTypes'));
            case '0.9.2':
                // further upgrade handling
        }

        return true;
    }

    /**
     * Clip deinstallation.
     */
    public function uninstall()
    {
        // loads the pubdata models
        ZLoader::addAutoloader('ClipModels', realpath(StringUtil::left(ModUtil::getVar('Clip', 'modelspath'), -11)));

        // drop pubtype tables
        $pubtypes = Doctrine_Core::getTable('Clip_Model_Pubtype')->findAll();

        foreach ($pubtypes as $pubtype) {
            if (!$pubtype->delete()) {
                return false;
            }
        }

        // delete m2m relation tables
        $rels = Clip_Util::getRelations(-1, false, true);
        foreach ($rels as $tid => $relations) {
            foreach ($relations as $relation) {
                if ($relation['type'] == 3) {
                    $table = 'ClipModels_Relation'.$relation['id'];
                    if (!Doctrine_Core::getTable($table)->dropTable()) {
                        return false;
                    }
                }
            }
        }

        // unregister the pubtype hooks
        $this->version->setupPubtypeBundles();
        HookUtil::unregisterSubscriberBundles($this->version->getHookSubscriberBundles());

        // drop base tables
        $tables = array(
            'Clip_Model_Pubfield',
            'Clip_Model_Pubtype',
            'Clip_Model_Grouptype',
            'Clip_Model_Pubrelation',
            'Clip_Model_WorkflowVars'
        );

        foreach ($tables as $table) {
            if (!Doctrine_Core::getTable($table)->dropTable()) {
                return false;
            }
        }

        // delete the workflow registries
        Clip_Workflow_Util::deleteWorkflowsForModule('Clip');

        // delete the category registry
        CategoryRegistryUtil::deleteEntry('Clip');
        //CategoryUtil::deleteCategoriesByPath('/__SYSTEM__/Modules/Clip', 'path');

        // delete temporary folder
        FileUtil::deldir($this->getVar('modelspath'), true);

        // delete the modvars
        $this->delVars();

        // FIXME anything more to delete on uninstall?

        return true;
    }



    /**
     * Default category tree creation.
     */
    private function createCategoryTree($regpath = '/__SYSTEM__/Modules')
    {
        $lang = ZLanguage::getLanguageCode();

        // Create the global Category Registry
        $c = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules/Global');

        $args = array(
            'prop' => 'Global',
            'cid'  => $c['id']
        );
        $this->createCategoryRegistry($args);

        // Create the Clip's category tree
        $c = CategoryUtil::getCategoryByPath($regpath.'/Clip');
        if (!$c) {
            $c = CategoryUtil::getCategoryByPath($regpath);

            $args = array(
                'cid'   => $c['id'],
                'name'  => 'Clip',
                'dname' => array($lang => $this->__('Clip')),
                'ddesc' => array($lang => $this->__('Clip root category'))
            );
            if (!$this->createCategory($args)) {
                return false;
            }
        }

        $c = CategoryUtil::getCategoryByPath($regpath.'/Clip/Topics');
        if (!$c) {
            $c = CategoryUtil::getCategoryByPath($regpath.'/Clip');

            $args = array(
                'cid'   => $c['id'],
                'name'  => 'Topics',
                //! this is the 'Topics' category name to registry
                'dname' => array($lang => $this->__('Topics')),
                'ddesc' => array($lang => $this->__('Clip topics for its publications'))
            );
            if (!$this->createCategory($args)) {
                return false;
            }
        }

        // create some example subcategories
        $c = CategoryUtil::getCategoryByPath($regpath.'/Clip/Topics');

        if ($c) {
            $args = array(
                'cid'   => $c['id'],
                'name'  => 'Zikula',
                'dname' => array($lang => 'Zikula'),
                'ddesc' => array($lang => $this->__('Zikula related publications')),
                'leaf'  => 1
            );
            $this->createCategory($args);

            $args = array(
                'cid'   => $c['id'],
                'name'  => 'FreeSoftware',
                //! this is the 'Free Software' example category name
                'dname' => array($lang => $this->__('Free Software')),
                'ddesc' => array($lang => $this->__('Free software related publications')),
                'leaf'  => 1
            );
            $this->createCategory($args);

            $args = array(
                'cid'   => $c['id'],
                'name'  => 'Community',
                //! this is the 'Community' example category name
                'dname' => array($lang => $this->__('Community')),
                'ddesc' => array($lang => $this->__('Community related publications')),
                'leaf'  => 1
            );
            $this->createCategory($args);

            // create the Category Registry
            // create an entry in the categories registry to the Topics property
            $args = array(
                'prop' => 'Topics',
                'cid'  => $c['id']
            );
            $this->createCategoryRegistry($args);

        } else {
            LogUtil::registerError($this->__f('Error! Could not create the [%s] Category Registry for Clip.', 'Topics'));
        }
    }

    private function createCategory($args)
    {
        $cat = new Categories_DBObject_Category();
        $cat->setDataField('parent_id',     $args['cid']);
        $cat->setDataField('name',          $args['name']);
        //! this is the 'lists' root category name
        $cat->setDataField('display_name',  $args['dname']);
        $cat->setDataField('display_desc',  $args['ddesc']);
        $cat->setDataField('value',   isset($args['value']) ? $args['value'] : '');
        $cat->setDataField('is_leaf', isset($args['leaf']) ? $args['leaf'] : 0);
        if (!$cat->validate()) {
            $ve = FormUtil::getValidationErrors();
            // compares the Categories_DBObject already exist error message
            $error = __f('Category %s must be unique under parent', $args['name']);
            if ($ve && $ve[$cat->_objPath]['name'] != $error) {
                LogUtil::registerError($ve[$cat->_objPath]['name']);
                return LogUtil::registerError($this->__f('Error! Could not create the [%s] category.', $args['name']));
            }
        }
        $cat->insert();
        $cat->update();

        return true;
    }

    private function createCategoryRegistry($args)
    {
        $registry = new Categories_DBObject_Registry();
        $registry->setDataField('modname',     'Clip');
        $registry->setDataField('table',       'clip_pubtypes');
        $registry->setDataField('property',    $args['prop']);
        $registry->setDataField('category_id', $args['cid']);
        if ($registry->validatePostProcess()) {
            $registry->insert();
        }
        FormUtil::clearValidationErrors();
    }

    /**
     * Default grouptypes tree creation.
     */
    private function createGrouptypesTree()
    {
        $lang = ZLanguage::getLanguageCode();

        // Create the Root and the 'Ungrouped' root category and adds there all the existing pubtypes
        $tids = Doctrine_Core::getTable('Clip_Model_Pubtype')->selectFieldArray('tid');
        $tree = Doctrine_Core::getTable('Clip_Model_Grouptype')->getTree();

        // root
        $root = new Clip_Model_Grouptype();
        $root->name = '__ROOT__';
        $root->save();
        $tree->createRoot($root);

        // ungrouped
        $ungr = new Clip_Model_Grouptype();
        //! name of the Default group of pubtypes
        $ungr->name = array($lang => $this->__('Contents'));
        $ungr->description = array($lang => $this->__('Publication contents of this site.'));
        $ungr->link('pubtypes', $tids);
        $ungr->getNode()->insertAsLastChildOf($root);
    }

    /**
     * Upload and Models directories creation
     */
    private function createDirectories($toCreate = array(), $silent = false)
    {
        $dirs = array();
        $dirs['upload'] = FileUtil::getDataDirectory().'/Clip/uploads';
        $dirs['models'] = CacheUtil::getLocalDir('ClipModels');

        foreach ($toCreate as $name) {
            $dir = $dirs[$name];

            if (!file_exists($dir) && mkdir($dir, System::getVar('system.chmod_dir', 0777), true)) {
                $msg = $this->__f('Clip created the \'%1$s\' directory successfully at [%2$s]. Be sure that this directory is writable by the webserver', array($name, $dir));
                if ($name == 'upload') {
                    $msg .= ' '.$this->__('and is accessible via web');
                }
            } elseif (file_exists($dir)) {
                if (!is_writable($dir)) {
                    $msg = $this->__f('Clip detected that the \'%1$s\' directory is already created at [%2$s] but it\'s not writable. Be sure to correct that', array($name, $dir));
                    if ($name == 'upload') {
                        $msg .= ' '.$this->__('and that it\'s accessible via web');
                    }
                } else {
                    $msg = $this->__f('Clip detected that the \'%1$s\' directory is already created at [%2$s]', array($name, $dir));
                    if ($name == 'upload') {
                        $msg .= ' '.$this->__('Be sure that it\'s accessible via web');
                    }
                }
            }
            if (!$silent) {
                LogUtil::registerStatus($msg.'.');
            }
        }

        return $dirs;
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
            DBUtil::dropTable('pagemaster_relations');
        }
        Doctrine_Core::getTable('Clip_Model_Pubrelation')->createTable();

        // rename the others
        DBUtil::renameTable('pagemaster_pubfields', 'clip_pubfields');
        DBUtil::renameTable('pagemaster_pubtypes',  'clip_pubtypes');

        // ensure tid is not pm_tid
        DoctrineUtil::renameColumn('clip_pubtypes', 'pm_tid', 'tid');

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

        $serviceManager = ServiceUtil::getManager();
        $dbtables = $serviceManager['dbtables'];
        $serviceManager['dbtables'] = array_merge($dbtables, (array)$tables);

        $sql = array();

        // fieldplugin class names
        $sql[] = "UPDATE {$tables['clip_pubfields']} SET pm_fieldplugin = REPLACE(pm_fieldplugin, 'PageMaster_', 'Clip_')";

        // fieldplugin type change: X to C(65535)
        $sql[] = "UPDATE {$tables['clip_pubfields']} SET pm_fieldtype = REPLACE(pm_fieldtype, 'X', 'C(65535)')";

        // workflow registries
        $sql[] = "UPDATE {$tables['workflows']} SET module = 'Clip', obj_table = REPLACE(obj_table, 'pagemaster_', 'clip_') WHERE module = 'PageMaster' OR module = 'pagemaster'";

        // rename the category registries
        $sql[] = "UPDATE {$tables['categories_registry']} SET modname = 'Clip', tablename  = REPLACE(tablename , 'pagemaster_', 'clip_') WHERE modname = 'PageMaster' OR modname = 'pagemaster'";

        // rename the permissions component
        $sql[] = "UPDATE {$tables['group_perms']} SET component  = REPLACE(component , 'pagemaster', 'Clip')";

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
                return LogUtil::registerError($this->__('Error! Update attempt failed.')." - $q");
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

        // ensure tid is not pm_tid
        DoctrineUtil::renameColumn('clip_pubtypes', 'pm_tid', 'tid');

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
    private function migratePubField()
    {
        self::upgradeDBpre09();

        if (!Doctrine_Core::getTable('Clip_Model_Pubtype')->changeTable()) {
            return false;
        }

        if (!Doctrine_Core::getTable('Clip_Model_Pubfield')->changeTable()) {
            return false;
        }
        
        // create the modelspath to be able to build the relations models
        $dirs = self::createDirectories(array('models'), true);
        $this->setVar('modelspath', $dirs['models']);
        ZLoader::addAutoloader('ClipModels', realpath(StringUtil::left($dirs['models'], -11)));

        $pubtypes = Doctrine_Core::getTable('Clip_Model_Pubtype')->selectFieldArray('folder', null, '', false, 'tid');

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
        Clip_Generator::checkModels();
        // update the pubdata tables
        $existingtables = DBUtil::metaTables();
        foreach (array_keys($pubtypes) as $tid) {
            $table = DBUtil::getLimitedTablename('clip_pubdata'.$tid);
            if (!in_array($table, $existingtables)) {
                Doctrine_Core::getTable('ClipModels_Pubdata'.$tid)->createTable();
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
                    $tbl1 = Doctrine_Core::getTable('ClipModels_Pubdata'.$tid1);
                    $tbl2 = Doctrine_Core::getTable('ClipModels_Pubdata'.$tid2);

                    // process n:n relations
                    if (count($v['ids']) > 1) {
                        // create the n:n table
                        $relclass = 'ClipModels_Relation'.$v['relid'];
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
        DoctrineUtil::renameColumn('clip_pubfields', 'pm_fieldtype', 'fielddbtype');

        // TEMP UPDATE: Image fieldplugin type change: C(255) to C(1024)
        $table = DBUtil::getLimitedTablename('clip_pubfields');
        $sql[] = "UPDATE $table SET fielddbtype = 'C(1024)' WHERE fielddbtype = 'Image' OR fielddbtype = 'Upload'";
    }

    /**
     * Upgrade pubtypes table.
     */
    private static function upgTablePubtypes()
    {
        static $done = false;
        if ($done) { return true; }
        $done = true;

        $ptcols = array_keys(DBUtil::metaColumnNames('clip_pubtypes'));

        // pubtypes
        DoctrineUtil::renameColumn('clip_pubtypes', 'pm_tid', 'tid');
        DoctrineUtil::renameColumn('clip_pubtypes', 'pm_title', 'title');
        DoctrineUtil::renameColumn('clip_pubtypes', 'pm_urltitle', 'urltitle');
        DoctrineUtil::renameColumn('clip_pubtypes', 'pm_description', 'description');
        DoctrineUtil::createColumn('clip_pubtypes', 'fixedfilter', array('type' => 'string', 'length' => 255));
        DoctrineUtil::renameColumn('clip_pubtypes', 'pm_defaultfilter', 'defaultfilter');
        DoctrineUtil::alterColumn('clip_pubtypes', 'pm_itemsperpage', array('type' => 'integer', 'options' => array('length' => 4, 'notnull' => true, 'default' => 15)));
        DoctrineUtil::renameColumn('clip_pubtypes', 'pm_itemsperpage', 'itemsperpage');
        DoctrineUtil::renameColumn('clip_pubtypes', 'pm_cachelifetime', 'cachelifetime');
        DoctrineUtil::renameColumn('clip_pubtypes', 'pm_sortfield1', 'sortfield1');
        DoctrineUtil::renameColumn('clip_pubtypes', 'pm_sortdesc1', 'sortdesc1');
        DoctrineUtil::renameColumn('clip_pubtypes', 'pm_sortfield2', 'sortfield2');
        DoctrineUtil::renameColumn('clip_pubtypes', 'pm_sortdesc2', 'sortdesc2');
        DoctrineUtil::renameColumn('clip_pubtypes', 'pm_sortfield3', 'sortfield3');
        DoctrineUtil::renameColumn('clip_pubtypes', 'pm_sortdesc3', 'sortdesc3');
        DoctrineUtil::alterColumn('clip_pubtypes', 'pm_enableeditown', array('type' => 'boolean', 'options' => array('length' => null, 'notnull' => true, 'default' => 0)));
        DoctrineUtil::renameColumn('clip_pubtypes', 'pm_enableeditown', 'enableeditown');
        DoctrineUtil::alterColumn('clip_pubtypes', 'pm_enablerevisions', array('type' => 'boolean', 'options' => array('length' => null, 'notnull' => true, 'default' => 0)));
        DoctrineUtil::renameColumn('clip_pubtypes', 'pm_enablerevisions', 'enablerevisions');
        DoctrineUtil::renameColumn('clip_pubtypes', 'pm_outputset', 'folder');
        DoctrineUtil::renameColumn('clip_pubtypes', 'pm_workflow', 'workflow');
        DoctrineUtil::renameColumn('clip_pubtypes', 'pm_group', 'grouptype');
        if (in_array('pm_config', $ptcols)) {
            DoctrineUtil::alterColumn('clip_pubtypes', 'pm_config', array('type' => 'clob', 'options' => array('length' => 65532)));
            DoctrineUtil::renameColumn('clip_pubtypes', 'pm_config', 'config');
        }
        DoctrineUtil::dropColumn('clip_pubtypes', 'pm_inputset');
        DoctrineUtil::renameColumn('clip_pubtypes', 'pm_obj_status', 'obj_status');
        DoctrineUtil::renameColumn('clip_pubtypes', 'pm_cr_date', 'cr_date');
        DoctrineUtil::renameColumn('clip_pubtypes', 'pm_cr_uid', 'cr_uid');
        DoctrineUtil::renameColumn('clip_pubtypes', 'pm_lu_date', 'lu_date');
        DoctrineUtil::renameColumn('clip_pubtypes', 'pm_lu_uid', 'lu_uid');
    }

    /**
     * Upgrade pubtypes table.
     */
    private static function upgTablePubfields()
    {
        static $done = false;
        if ($done) { return true; }
        $done = true;

        $pfcols = array_keys(DBUtil::metaColumnNames('clip_pubfields'));

        // pubfields
        DoctrineUtil::renameColumn('clip_pubfields', 'pm_id', 'id');
        DoctrineUtil::alterColumn('clip_pubfields', 'pm_tid', array('type' => 'integer', 'options' => array('length' => 4, 'notnull' => false)));
        DoctrineUtil::renameColumn('clip_pubfields', 'pm_tid', 'tid');
        DoctrineUtil::renameColumn('clip_pubfields', 'pm_name', 'name');
        DoctrineUtil::renameColumn('clip_pubfields', 'pm_title', 'title');
        DoctrineUtil::renameColumn('clip_pubfields', 'pm_description', 'description');
        DoctrineUtil::renameColumn('clip_pubfields', 'pm_fieldplugin', 'plugin');
        DoctrineUtil::renameColumn('clip_pubfields', 'pm_typedata', 'config');
        DoctrineUtil::renameColumn('clip_pubfields', 'pm_fieldtype', 'fielddbtype');
        DoctrineUtil::renameColumn('clip_pubfields', 'pm_fieldmaxlength', 'fieldmaxlength');
        DoctrineUtil::alterColumn('clip_pubfields', 'pm_lineno', array('type' => 'integer', 'options' => array('length' => 4, 'notnull' => false)));
        DoctrineUtil::renameColumn('clip_pubfields', 'pm_lineno', 'weight');
        DoctrineUtil::renameColumn('clip_pubfields', 'pm_istitle', 'is_title');
        DoctrineUtil::renameColumn('clip_pubfields', 'pm_ismandatory', 'is_mandatory');
        DoctrineUtil::renameColumn('clip_pubfields', 'pm_issearchable', 'is_searchable');
        DoctrineUtil::createColumn('clip_pubfields', 'is_filterable', array('type' => 'boolean', 'length' => null, 'notnull' => true, 'default' => 0));
        DoctrineUtil::renameColumn('clip_pubfields', 'pm_ispageable', 'is_pageable');
        DoctrineUtil::createColumn('clip_pubfields', 'is_counter', array('type' => 'boolean', 'length' => null, 'notnull' => true, 'default' => 0));
        if (in_array('pm_isuid', $pfcols)) {
            DoctrineUtil::dropColumn('clip_pubfields', 'pm_isuid');
        }
        DoctrineUtil::renameColumn('clip_pubfields', 'pm_obj_status', 'obj_status');
        DoctrineUtil::renameColumn('clip_pubfields', 'pm_cr_date', 'cr_date');
        DoctrineUtil::renameColumn('clip_pubfields', 'pm_cr_uid', 'cr_uid');
        DoctrineUtil::renameColumn('clip_pubfields', 'pm_lu_date', 'lu_date');
        DoctrineUtil::renameColumn('clip_pubfields', 'pm_lu_uid', 'lu_uid');
    }

    /**
     * Upgrade Database by last time before 0.9 release.
     *
     * @return boolean
     */
    private function upgradeDBpre09()
    {
        static $done = false;
        if ($done) { return true; }
        $done = true;

        // last db changes before Clip 0.9
        $tables = Doctrine_Manager::connection()->import->listTables();

        // table structure
        self::upgTablePubtypes();
        self::upgTablePubfields();
        // grouptypes
        if (in_array(DBUtil::getLimitedTablename('clip_grouptypes'), $tables)) {
            DoctrineUtil::renameColumn('clip_grouptypes', 'c_gid', 'gid');
            DoctrineUtil::renameColumn('clip_grouptypes', 'c_name', 'name');
            DoctrineUtil::renameColumn('clip_grouptypes', 'c_description', 'description');
            DoctrineUtil::renameColumn('clip_grouptypes', 'c_order', 'sortorder');
        }
        // relations
        $rlcols = array_keys(DBUtil::metaColumnNames('clip_relations'));
        if (in_array('pm_id', $rlcols)) {
            DoctrineUtil::renameColumn('clip_relations', 'pm_id', 'id');
            DoctrineUtil::alterColumn('clip_relations', 'pm_type', array('type' => 'integer', 'options' => array('length' => 2, 'notnull' => true, 'default' => 1)));
            DoctrineUtil::renameColumn('clip_relations', 'pm_type', 'type');
            DoctrineUtil::alterColumn('clip_relations', 'pm_tid1', array('type' => 'integer', 'options' => array('length' => 4, 'notnull' => false)));
            DoctrineUtil::renameColumn('clip_relations', 'pm_tid1', 'tid1');
            DoctrineUtil::alterColumn('clip_relations', 'pm_alias1', array('type' => 'string', 'options' => array('length' => 100, 'notnull' => false)));
            DoctrineUtil::renameColumn('clip_relations', 'pm_alias1', 'alias1');
            DoctrineUtil::renameColumn('clip_relations', 'pm_title1', 'title1');
            DoctrineUtil::renameColumn('clip_relations', 'pm_desc1', 'desc1');
            DoctrineUtil::alterColumn('clip_relations', 'pm_tid2', array('type' => 'integer', 'options' => array('length' => 4, 'notnull' => false)));
            DoctrineUtil::renameColumn('clip_relations', 'pm_tid2', 'tid2');
            DoctrineUtil::alterColumn('clip_relations', 'pm_alias2', array('type' => 'string', 'options' => array('length' => 100, 'notnull' => false)));
            DoctrineUtil::renameColumn('clip_relations', 'pm_alias2', 'alias2');
            DoctrineUtil::renameColumn('clip_relations', 'pm_title2', 'title2');
            DoctrineUtil::renameColumn('clip_relations', 'pm_desc2', 'desc2');
        }
        // pubdatas
        $pubdata = DBUtil::getLimitedTablename('clip_pubdata');
        foreach ($tables as $k => $table) {
            if (strpos($table, $pubdata) !== 0) {
                unset($tables[$k]);
            } else {
                $tables[$k] = array('name' => $table, 'tid' => Clip_Util::getTidFromString($table));
            }
        }
        foreach ($tables as $table) {
            $pubdata = 'clip_pubdata'.$table['tid'];
            $columns = Doctrine_Manager::connection()->import->listTableColumns($table['name']);

            DoctrineUtil::createColumn($pubdata, 'locked', array('type' => 'boolean', 'length' => null, 'notnull' => true, 'default' => 0));

            foreach ($columns as $cname => $cdef) {
                if ($cname == 'pm_showinmenu') {
                    DoctrineUtil::dropColumn($pubdata, $cname);

                } elseif (strpos($cname, 'pm_') === 0) {
                    if (!$cdef['autoincrement'] && $cdef['notnull'] && $cdef['default'] === null) {
                        switch ($cdef['type']) {
                            case 'integer':
                                $cdef['default'] = 0;
                                break;
                            case 'string':
                                $cdef['default'] = '';
                                break;
                        }
                        DoctrineUtil::alterColumn($pubdata, $cname, array('type' => $cdef['type'], 'options' => $cdef));
                    }
                    $newcname = substr($cname, 3);
                    if (is_numeric($newcname)) {
                        $newcname = "field$newcname";
                    }
                    switch ($newcname) {
                        case 'hitcount':
                            $newcname = 'hits';
                            break;
                        case 'indepot':
                            $newcname = 'intrash';
                            break;
                        case 'showinlist':
                            $newcname = 'visible';
                            break;
                    }
                    DoctrineUtil::renameColumn($pubdata, $cname, $newcname);
                }
            }
        }

        // table data
        $tables = DBUtil::getTables();
        $sql = array();
        // update workflow state: 'preview' state to 'accepted' (?)
        $sql[] = "UPDATE {$tables['workflows']} SET state = 'accepted' WHERE module = 'Clip' AND schemaname = 'enterprise' AND state = 'preview'";
        // update permissions:
        // Clip:input: to Clip:.*?:edit
        $sql[] = "UPDATE {$tables['group_perms']} SET component = 'Clip:.*?:edit' WHERE component = 'Clip:input:'";
        // Clip:display: to Clip:.*?:display
        $sql[] = "UPDATE {$tables['group_perms']} SET component = 'Clip:.*?:display' WHERE component = 'Clip:display:'";
        // Clip:list: to Clip:.*?:list
        $sql[] = "UPDATE {$tables['group_perms']} SET component = 'Clip:.*?:list' WHERE component = 'Clip:list:'";
        // execute
        foreach ($sql as $q) {
            if (!DBUtil::executeSQL($q)) {
                return LogUtil::registerError($this->__('Error! Update attempt failed.')." - $q");
            }
        }
        // homepage function check module = 'Clip' => func: list/display
        if (System::getVar('startpage') == 'Clip') {
            switch (System::getVar('startfunc')) {
                case 'view':
                    System::setVar('startfunc', 'list');
                    break;
            }
        }

        return true;
    }

    /**
     * Upgrade models and databases to introduce the urltitle field.
     *
     * @return boolean
     */
    private static function introduceUrltitle()
    {
        // regen models
        Clip_Generator::resetModels();
        Clip_Generator::checkModels(true);

        // update the database
        $pubtypes = Doctrine_Core::getTable('Clip_Model_Pubtype')->selectFieldArray('tid');
        foreach ($pubtypes as $tid) {
            $cols = array_keys(DBUtil::metaColumnNames('clip_pubdata'.$tid));
            if (!in_array('urltitle', $cols)) {
                DoctrineUtil::createColumn('clip_pubdata'.$tid, 'urltitle', array('type' => 'string', 'length' => 255));
            }

            // fill the urltitles
            $urltitles  = array();

            $titlefield = Clip_Util::getTitleField($tid);
            $titles     = Doctrine_Core::getTable('ClipModels_Pubdata'.$tid)->selectFieldArray($titlefield, array(), '', false, 'id');

            foreach ($titles as $id => $title) {
                $urltitle = substr(DataUtil::formatPermalink($title), 0, 255);

                while (in_array($urltitle, $urltitles)) {
                    $urltitle++;
                }

                $urltitles[$id] = $urltitle;
            }

            $tablename = DBUtil::getLimitedTablename('clip_pubdata'.$tid);

            foreach ($urltitles as $id => $urltitle) {
                $q = "UPDATE {$tablename} SET urltitle = '{$urltitle}' WHERE id = {$id}";

                if (!DBUtil::executeSQL($q)) {
                    return LogUtil::registerError($this->__('Error! Update attempt failed.')." - $q");
                }
            }
        }

        return true;
    }

    /**
     * Upgrade all the pubtype's configurations.
     *
     * @return boolean
     */
    private static function pubtypeConfigs()
    {
        $tablename = DBUtil::getLimitedTablename('clip_pubtypes');
        $q = "UPDATE {$tablename} SET config = REPLACE(config, 'view', 'list')";

        if (!DBUtil::executeSQL($q)) {
            return LogUtil::registerError($this->__('Error! Update attempt failed.')." - $q");
        }

        $pubtypes = Clip_Util::getPubType(-1, null, true);
        $pubtypes->save();

        return true;
    }

    /**
     * Map old ContentType names to new.
     *
     * @return array
     */
    public static function LegacyContentTypeMap()
    {
        $map = array(
            'pagesetter_pub'     => 'ClipPub',
            'pagesetter_publist' => 'ClipPublist'
        );

        return $map;
    }

    /**
     * Utility method to update the pubdata tables.
     */
    private static function updatePubTables()
    {
        static $already = false;

        if ($already) {
            return;
        }        

        $already = true;

        // update the pubdata models files
        Clip_Generator::resetModels();
        Clip_Generator::checkModels(true);

        // update the database
        $pubtypes = Doctrine_Core::getTable('Clip_Model_Pubtype')->selectFieldArray('tid');
        foreach ($pubtypes as $tid) {
            Doctrine_Core::getTable('ClipModels_Pubdata'.$tid)->changeTable();
        }
    }
}
