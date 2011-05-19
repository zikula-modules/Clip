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

        $this->hasColumn('pm_tid as tid', 'integer', 4, array(
            'primary' => true,
            'autoincrement' => true
        ));

        $this->hasColumn('pm_title as title', 'string', 255, array(
            'notnull' => true,
            'default' => ''
        ));

        $this->hasColumn('pm_urltitle as urltitle', 'string', 255, array(
            'notnull' => true,
            'default' => ''
        ));

        $this->hasColumn('pm_outputset as outputset', 'string', 255, array(
            'notnull' => true,
            'default' => ''
        ));

        $this->hasColumn('pm_inputset as inputset', 'string', 255, array(
            'notnull' => true,
            'default' => ''
        ));

        $this->hasColumn('pm_description as description', 'string', 255, array(
            'notnull' => true,
            'default' => ''
        ));

        $this->hasColumn('pm_itemsperpage as itemsperpage', 'integer', 4, array(
            'notnull' => true
        ));

        $this->hasColumn('pm_sortfield1 as sortfield1', 'string', 255);

        $this->hasColumn('pm_sortdesc1 as sortdesc1', 'boolean');

        $this->hasColumn('pm_sortfield2 as sortfield2', 'string', 255);

        $this->hasColumn('pm_sortdesc2 as sortdesc2', 'boolean');

        $this->hasColumn('pm_sortfield3 as sortfield3', 'string', 255);

        $this->hasColumn('pm_sortdesc3 as sortdesc3', 'boolean');

        $this->hasColumn('pm_workflow as workflow', 'string', 255, array(
            'notnull' => true
        ));

        $this->hasColumn('pm_defaultfilter as defaultfilter', 'string', 255);

        $this->hasColumn('pm_enablerevisions as enablerevisions', 'boolean', null, array(
            'notnull' => true
        ));

        $this->hasColumn('pm_enableeditown as enableeditown', 'boolean', null, array(
            'notnull' => true
        ));

        $this->hasColumn('pm_cachelifetime as cachelifetime', 'integer', 8, array(
            'notnull' => true,
            'default' => 0
        ));

        $this->hasColumn('pm_config as config', 'clob');

        $this->hasColumn('pm_group as grouptype', 'integer', 4);
    }

    /**
     * Record setup.
     *
     * @return void
     */
    public function setUp()
    {
        $this->actAs('Zikula_Doctrine_Template_StandardFields', array('oldColumnPrefix' => 'pm_'));

        $this->hasOne('Clip_Model_Grouptype as group', array(
              'local' => 'grouptype',
              'foreign' => 'gid'
        ));
    }

    /**
     * Clip utility methods
     */
    public function getTableName()
    {
        return 'clip_pubdata'.$this->tid;
    }

    public function getRelations($onlyown = true)
    {
        $key = ($onlyown ? 'own' : 'all').'relations';

        if ($this->hasMappedValue($key)) {
            return $this->$key;
        }

        $relations = array();

        // load own
        $records = Clip_Util::getRelations($this->tid, true);
        foreach ($records as $relation) {
            $relations[$relation['alias1']] = array(
                'id'     => $relation['id'],
                'tid'    => $relation['tid2'],
                'type'   => $relation['type'],
                'alias'  => $relation['alias2'],
                'title'  => $relation['title1'],
                'descr'  => $relation['descr1'],
                'owned'  => $relation['alias1'],
                'single' => $relation['type']%2 == 0 ? true : false,
                'own'    => true
            );
        }

        if (!$onlyown) {
            // load foreign
            $records = Clip_Util::getRelations($this->tid, false);

            foreach ($records as $relation) {
                if (!isset($relations[$relation['alias2']])) {
                    $relations[$relation['alias2']] = array(
                        'id'     => $relation['id'],
                        'tid'    => $relation['tid1'],
                        'type'   => $relation['type'],
                        'alias'  => $relation['alias1'],
                        'title'  => $relation['title2'],
                        'descr'  => $relation['descr2'],
                        'owned'  => $relation['alias2'],
                        'single' => $relation['type'] <= 1 ? true : false,
                        'own'    => false
                    );
                }
            }
        }

        $this->mapValue($key, $relations);

        return $this->$key;
    }

    public function defaultConfig($config)
    {
        $default = array(
            'view' => array(
                'load' => false,
                'onlyown' => true,
                'processrefs' => false,
                'checkperm' => false,
                'handleplugins' => false,
                'loadworkflow' => false
            ),
            'display' => array(
                'load' => true,
                'onlyown' => true,
                'processrefs' => true,
                'checkperm' => true,
                'handleplugins' => false,
                'loadworkflow' => false
            ),
            'edit' => array(
                'load' => true,
                'onlyown' => true
            )
        );

        foreach ($default as $k => $v) {
            $config[$k] = isset($config[$k]) ? array_merge($v, $config[$k]) : $v;
        }

        return $config;
    }

    /**
     * Saving hook.
     *
     * @return void
     */
    public function preSave($event)
    {
        if (is_array($this->config)) {
            $this->config = serialize($this->config);
        }
    }

    /**
     * Hydrate hook.
     *
     * @return void
     */
    public function postHydrate($event)
    {
        $data = $event->data;

        if (is_object($data)) {
            if (isset($data->config) && !empty($data->config) && is_string($data->config)) {
                $data->config = unserialize($data->config);
            } else {
                $data->config = array();
            }
            $data->config = $this->defaultConfig($data->config);

        } elseif (is_array($data)) {
            if (isset($data['config']) && !empty($data['config']) && is_string($data['config'])) {
                $data['config'] = unserialize($data['config']);
            } else {
                $data['config'] = array();
            }
            $data['config'] = $this->defaultConfig($data['config']);
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
        $bundle = new Zikula_HookManager_SubscriberBundle('Clip', "subscriber.ui_hooks.clip.pubtype{$tid}", 'ui_hooks', $clipVersion->__f('%s Item Hooks', $name));
        $bundle->addEvent('display_view',    "clip.ui_hook.pubtype{$tid}.display_view");
        $bundle->addEvent('form_edit',       "clip.ui_hook.pubtype{$tid}.form_edit");
        $bundle->addEvent('form_delete',     "clip.ui_hook.pubtype{$tid}.form_delete");
        $bundle->addEvent('validate_edit',   "clip.ui_hook.pubtype{$tid}.validate_edit");
        $bundle->addEvent('validate_delete', "clip.ui_hook.pubtype{$tid}.validate_delete");
        $bundle->addEvent('process_edit',    "clip.ui_hook.pubtype{$tid}.process_edit");
        $bundle->addEvent('process_delete',  "clip.ui_hook.pubtype{$tid}.process_delete");
        $clipVersion->registerHookSubscriberBundle($bundle);

        // filter hooks
        $bundle = new Zikula_HookManager_SubscriberBundle('Clip', "subscriber.filter_hooks.clip.pubtype{$tid}", 'filter_hooks', $clipVersion->__f('%s Filter', $name));
        $bundle->addEvent('filter', "clip.filter_hooks.pubtype{$tid}.filter");
        $clipVersion->registerHookSubscriberBundle($bundle);
    }

    /**
     * Save hook.
     *
     * @return void
     */
    public function preInsert($event)
    {
        $invoker = $event->getInvoker();

        // make sure it belongs to a group (the first one after root)
        // TODO make this configurable
        $gid = Doctrine_Core::getTable('Clip_Model_Grouptype')
                   ->createQuery()
                   ->select('gid')
                   ->orderBy('gid')
                   ->where('gid > ?', 1)
                   ->fetchOne(array(), Doctrine_Core::HYDRATE_SINGLE_SCALAR);

        $this->grouptype = (int)$gid;
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
        $data = $event->getInvoker();
        $this->registerHookBundles($clipVersion, $data['tid'], $data['title']);

        HookUtil::registerSubscriberBundles($clipVersion->getHookSubscriberBundles());
    }

    /**
     * Delete hook.
     *
     * @return void
     */
    public function postDelete($event)
    {
        $data = $event->getInvoker();

        // delete m2m relation tables
        $ownSides = array(true, false);
        foreach ($ownSides as $ownSide) {
            $rels = Clip_Util::getRelations($data['tid'], $ownSide);
            foreach ($rels as $tid => $relations) {
                foreach ($relations as $relation) {
                    if ($relation['type'] == 3) {
                        $table = 'Clip_Model_Relation'.$relation['id'];
                        Doctrine_Core::getTable($table)->dropTable();
                    }
                }
            }
        }

        // delete any relation
        $where = array("tid1 = '{$data['tid']}' OR tid2 = '{$data['tid']}'");
        Doctrine_Core::getTable('Clip_Model_Pubrelation')->deleteWhere($where);

        // delete the data table
        Doctrine_Core::getTable('Clip_Model_Pubdata'.$this->tid)->dropTable();

        // delete workflows
        DBUtil::deleteWhere('workflows', "module = 'Clip' AND obj_table = 'clip_pubdata{$data['tid']}'");

        $clipVersion = new Clip_Version_Hooks();

        // unregister hook bundles
        $this->registerHookBundles($clipVersion, $data['tid'], $data['title']);

        HookUtil::unregisterSubscriberBundles($clipVersion);
    }
}
