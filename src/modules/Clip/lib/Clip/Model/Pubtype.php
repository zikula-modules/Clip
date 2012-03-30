<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Model
 */

/**
 * This is the model class that define the entity structure and behaviours.
 */
class Clip_Model_Pubtype extends Doctrine_Record
{
    /**
     * Set table definition.
     *
     * @return void
     */
    public function setTableDefinition()
    {
        $this->setTableName('clip_pubtypes');

        $this->hasColumn('tid as tid', 'integer', 4, array(
            'primary' => true,
            'autoincrement' => true
        ));

        $this->hasColumn('title as title', 'string', 255, array(
            'notnull' => true,
            'default' => ''
        ));

        $this->hasColumn('urltitle as urltitle', 'string', 255, array(
            'notnull' => true,
            'default' => ''
        ));

        $this->hasColumn('description as description', 'string', 255, array(
            'notnull' => true,
            'default' => ''
        ));

        $this->hasColumn('fixedfilter as fixedfilter', 'string', 255);

        $this->hasColumn('defaultfilter as defaultfilter', 'string', 255);

        $this->hasColumn('itemsperpage as itemsperpage', 'integer', 4, array(
            'notnull' => true,
            'default' => 15
        ));

        $this->hasColumn('cachelifetime as cachelifetime', 'integer', 8, array(
            'notnull' => true,
            'default' => 0
        ));

        $this->hasColumn('sortfield1 as sortfield1', 'string', 255);

        $this->hasColumn('sortdesc1 as sortdesc1', 'boolean');

        $this->hasColumn('sortfield2 as sortfield2', 'string', 255);

        $this->hasColumn('sortdesc2 as sortdesc2', 'boolean');

        $this->hasColumn('sortfield3 as sortfield3', 'string', 255);

        $this->hasColumn('sortdesc3 as sortdesc3', 'boolean');

        $this->hasColumn('enableeditown as enableeditown', 'boolean', null, array(
            'notnull' => true,
            'default' => 0
        ));

        $this->hasColumn('enablerevisions as enablerevisions', 'boolean', null, array(
            'notnull' => true,
            'default' => 0
        ));

        $this->hasColumn('folder as folder', 'string', 255, array(
            'notnull' => true,
            'default' => ''
        ));

        $this->hasColumn('workflow as workflow', 'string', 255, array(
            'notnull' => true,
            'default' => ''
        ));

        $this->hasColumn('grouptype as grouptype', 'integer', 4);

        $this->hasColumn('config as config', 'clob', 65532);
    }

    /**
     * Record setup.
     *
     * @return void
     */
    public function setUp()
    {
        $this->actAs('Zikula_Doctrine_Template_StandardFields');

        $this->hasOne('Clip_Model_Grouptype as group', array(
              'local' => 'grouptype',
              'foreign' => 'gid'
        ));
    }

    /**
     * Clip utility methods
     */
    public function getTitleField()
    {
        return Doctrine_Core::getTable('ClipModels_Pubdata'.$this->tid)->getRecord()->core_titlefield;
    }

    public function getTableName()
    {
        return 'clip_pubdata'.$this->tid;
    }

    public function getSchema()
    {
        return FileUtil::getFilebase($this->workflow);
    }

    public function updateTable($reloadfields = true)
    {
        // update the pubtype's model file
        Clip_Generator::updateModel($this->tid, $reloadfields, true);

        // update the pubtype's table
        $classname = Clip_Generator::createTempModel($this->tid);
        Doctrine_Core::getTable($classname)->changeTable(true);
    }

    public function getFields($attrs = false)
    {
        return $this->tid ? Clip_Util::getPubFields($this->tid, null, 'lineno', $attrs) : array();
    }

    public function getRelations($onlyown = true, $field = null)
    {
        return call_user_func_array(array('ClipModels_Pubdata'.$this->tid.'Table', 'clipRelations'), array($onlyown, $field));
    }

    public function getRelation($alias)
    {
        $relations = $this->getRelations(false);

        return isset($relations[$alias]) ? $relations[$alias] : null;
    }

    public function getPubInstance()
    {
        $className = 'ClipModels_Pubdata'.$this->tid;

        return new $className;
    }

    public function getHooksEventName($hooktype = 'display_view', $category = 'ui_hooks', $subarea = '')
    {
        return "clip.{$category}.pubtype{$this->tid}{$subarea}.{$hooktype}";
    }

    public function getHooksAreaName($category = 'ui_hooks', $subarea = '', $type = 'subscriber')
    {
        return "{$type}.clip.{$category}.pubtype{$this->tid}{$subarea}";
    }

    public function defaultConfig($config)
    {
        $default = array(
            'list' => array(
                'load' => false,
                'onlyown' => true,
                'checkperm' => false
            ),
            'display' => array(
                'load' => true,
                'onlyown' => true,
                'checkperm' => true
            ),
            'edit' => array(
                'load' => true,
                'onlyown' => true,
                'checkperm' => false
            )
        );

        foreach ($default as $k => $v) {
            if (isset($config[$k])) {
                $config[$k] = array_intersect_key($config[$k], $v);
                $config[$k] = array_merge($v, $config[$k]);
            } else {
                $config[$k] = $v;
            }
        }

        return $config;
    }

    /**
     * Hydrate hook.
     *
     * @return void
     */
    public function postHydrate($event)
    {
        $pubtype = $event->data;

        if (is_object($pubtype)) {
            if (isset($pubtype->config) && !empty($pubtype->config) && is_string($pubtype->config)) {
                $pubtype->config = unserialize($pubtype->config);
            } else {
                $pubtype->config = array();
            }
            $pubtype->config = $this->defaultConfig($pubtype->config);

        } elseif (is_array($pubtype)) {
            if (isset($pubtype['config']) && !empty($pubtype['config']) && is_string($pubtype['config'])) {
                $pubtype['config'] = unserialize($pubtype['config']);
            } else {
                $pubtype['config'] = array();
            }
            $pubtype['config'] = $this->defaultConfig($pubtype['config']);
        }
    }

    /**
     * Utility method to create the hook bundles.
     *
     * @return void
     */
    public function registerHookBundles(Clip_Version &$clipVersion, $tid=null, $name=null)
    {
        $tid  = $tid ? $tid : $this->tid;
        $name = $name ? $name : $this->title;

        // display/edit hooks
        $bundle = new Zikula_HookManager_SubscriberBundle('Clip', $this->getHooksAreaName(), 'ui_hooks', $clipVersion->__f('%s Item Hooks', $name));
        $bundle->addEvent('display_view',    $this->getHooksEventName('display_view'));
        $bundle->addEvent('form_edit',       $this->getHooksEventName('form_edit'));
        $bundle->addEvent('form_delete',     $this->getHooksEventName('form_delete'));
        $bundle->addEvent('validate_edit',   $this->getHooksEventName('validate_edit'));
        $bundle->addEvent('validate_delete', $this->getHooksEventName('validate_delete'));
        $bundle->addEvent('process_edit',    $this->getHooksEventName('process_edit'));
        $bundle->addEvent('process_delete',  $this->getHooksEventName('process_delete'));
        $clipVersion->registerHookSubscriberBundle($bundle);

        // filter hooks
        $bundle = new Zikula_HookManager_SubscriberBundle('Clip', $this->getHooksAreaName('filter_hooks'), 'filter_hooks', $clipVersion->__f('%s Filter', $name));
        $bundle->addEvent('filter', $this->getHooksEventName('filter', 'filter_hooks'));
        $clipVersion->registerHookSubscriberBundle($bundle);
    }

    /**
     * Create hook.
     *
     * @return void
     */
    public function preInsert($event)
    {
        // make sure it belongs to a group (the first one after root)
        if (!$this->grouptype) {
            // TODO make this select-able on the pubtype form
            $gid = Doctrine_Core::getTable('Clip_Model_Grouptype')
                       ->createQuery()
                       ->select('gid')
                       ->orderBy('gid')
                       ->where('gid > ?', 1)
                       ->fetchOne(array(), Doctrine_Core::HYDRATE_SINGLE_SCALAR);

            $this->grouptype = (int)$gid;
        }
    }

    /**
     * Create hook.
     *
     * @return void
     */
    public function postInsert($event)
    {
        $clipVersion = new Clip_Version_Hooks();

        // register hook bundles
        $pubtype = $event->getInvoker();
        $this->registerHookBundles($clipVersion, $pubtype->tid, $pubtype->title);

        HookUtil::registerSubscriberBundles($clipVersion->getHookSubscriberBundles());

        // create the pubtype table
        Clip_Generator::updateModel($pubtype->tid);

        $classname = Clip_Generator::createTempModel($pubtype->tid);
        Doctrine_Core::getTable($classname)->createTable();
    }

    /**
     * Saving hook.
     *
     * @return void
     */
    public function preSave($event)
    {
        // purge the folder names of undesired chars
        $this->folder = preg_replace(Clip_Util::REGEX_FOLDER, '', $this->folder);

        if (is_array($this->config)) {
            $this->config = serialize($this->config);
        }
    }

    /**
     * Delete hook.
     *
     * @return void
     */
    public function postDelete($event)
    {
        $pubtype = $event->getInvoker();

        // validates that the default pubtype id is correct
        $default = ModUtil::getVar('Clip', 'pubtype');

        if ($default && $this->tid == $default) {
            ModUtil::setVar('Clip', 'pubtype', null);
        }

        // delete its pubfields
        Clip_Util::getPubFields($this->tid)->delete();

        // delete any relation
        $where = array("tid1 = '{$pubtype->tid}' OR tid2 = '{$pubtype->tid}'");
        Doctrine_Core::getTable('Clip_Model_Pubrelation')->deleteWhere($where);

        // delete the data table
        Doctrine_Core::getTable('ClipModels_Pubdata'.$pubtype->tid)->dropTable();

        // delete the model file
        Clip_Generator::deleteModel($pubtype->tid);

        // delete workflows
        DBUtil::deleteWhere('workflows', "module = 'Clip' AND obj_table = 'clip_pubdata{$pubtype->tid}'");

        $clipVersion = new Clip_Version_Hooks();

        // unregister hook bundles
        $pubtype->registerHookBundles($clipVersion, $pubtype->tid, $pubtype->title);

        HookUtil::unregisterSubscriberBundles($clipVersion->getHookSubscriberBundles());
    }
}
