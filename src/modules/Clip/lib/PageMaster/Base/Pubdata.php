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
 * This is the model class that define the entity structure and behaviours.
 */
class PageMaster_Base_Pubdata extends Doctrine_Record
{
    /**
     * Record post process.
     *
     * @param boolean $args['handleplugins']     Whether to parse the plugin fields.
     * @param boolean $args['loadworkflow']      Whether to add the workflow information.
     * @param boolean $args['checkrefs']         Whether to process the related records.
     * @param boolean $args['rel.onlyown']       Whether to check the permissions.
     * @param boolean $args['rel.checkperm']     Whether to check the permissions.
     * @param boolean $args['rel.handleplugins'] Whether to parse the plugin fields.
     * @param boolean $args['rel.loadworkflow']  Whether to add the workflow information.
     *
     * @return void
     */
    public function pubPostProcess($args = array())
    {
        $tablename = $this->_table->getInternalTableName();
        $tid = PageMaster_Util::getTidFromStringSuffix($tablename);

        // mapped values
        $core_title = PageMaster_Util::getTitleField($tid);

        $this->mapValue('core_tid', $tid);
        $this->mapValue('core_uniqueid', "{$tid}-{$this['core_pid']}");
        $this->mapValue('core_title' , $this[$core_title]);
        $this->mapValue('core_creator', ($this['core_author'] == UserUtil::getVar('uid')) ? true : false);
        $this->mapValue('__WORKFLOW__', array('state' => null));

        // handle the plugins data if needed
        if (!isset($args['handleplugins']) || $args['handleplugins']) {
            PageMaster_Util::handlePluginFields($this);
        }

        // load the workflow data if needed
        if (isset($args['loadworkflow']) && $args['loadworkflow']) {
            Zikula_Workflow_Util::getWorkflowForObject($this, $tablename, 'id', 'PageMaster');
        }

        $this->mapValue('core_approvalstate', isset($this['__WORKFLOW__']['state']) ? $this['__WORKFLOW__']['state'] : null);

        // post process related records
        if (isset($args['checkrefs']) && $args['checkrefs']) {
            // new default values
            $args = array(
                'checkrefs'         => false,
                'handleplugins'     => isset($args['rel.handleplugins']) ? $args['rel.handleplugins'] : true,
                'loadworkflow'      => isset($args['rel.loadworkflow']) ? $args['rel.loadworkflow'] : false,
                'rel.onlyown'       => isset($args['rel.onlyown']) ? $args['rel.onlyown'] : false,
                'rel.checkperm'     => isset($args['rel.checkperm']) ? $args['rel.checkperm'] : false,
                'rel.handleplugins' => isset($args['rel.handleplugins']) ? $args['rel.handleplugins'] : true,
                'rel.loadworkflow'  => isset($args['rel.loadworkflow']) ? $args['rel.loadworkflow'] : false
            );
            // loop the related records
            foreach (array_keys($this->getRelations($args['rel.onlyown'])) as $alias) {
                if ($this[$alias] instanceof Doctrine_Record) {
                    if (!$args['rel.checkperm'] || SecurityUtil::checkPermission('pagemaster:full:', "$this[core_tid]:$this[core_pid]:", ACCESS_READ)) {
                        $this[$alias]->pubPostProcess($args);
                    }
                } elseif ($this[$alias] instanceof Doctrine_Collection) {
                    foreach ($this[$alias] as $k => $v) {
                        if (!$args['rel.checkperm'] || SecurityUtil::checkPermission('pagemaster:full:', "$this[core_tid]:$this[core_pid]:", ACCESS_READ)) {
                            $this[$alias][$k]->pubPostProcess($args);
                        }
                    }
                }
            }
        }
    }

    /**
     * Returns the record fields as keys of a result array.
     *
     * @return array List of available fields as keys.
     */
    public function pubFields()
    {
        $fields = array();

        foreach ($this as $column => $value) {
            $fields[$column] = 'column';
        }

        // FIXME Prevent mapped Doctrine_Records from being displayed fully
        foreach ($this->_values as $key => $value) {
            $fields[$key] = 'value';
        }

        foreach ($this->_table->getRelations() as $key => $relation) {
            $fields[$key] = 'relation';
        }

        // reorder the fields conveniently
        $reorder = array(
            'core_title' => 'map',
            'core_uniqueid' => 'map',
            'core_tid' => 'map',
            'core_pid' => 'value',
            'core_author' => 'value',
            'core_creator' => 'map',
            'core_approvalstate' => 'map'
        );
        $fields = array_merge($reorder, $fields);

        return $fields;
    }

    /**
     * Returns the publication as an array.
     *
     * @param boolean $deep      Whether to include relations.
     * @param boolean $prefixKey Not used.
     *
     * @return array
     */
    public function toArray($deep = true, $prefixKey = false)
    {
        $fields = $this->pubFields();

        $a = parent::toArray($deep, $prefixKey);
        $a = array_intersect_key($a, $fields);

        return $a;
    }

    /**
     * Returns the record relations as an indexed array.
     *
     * @param boolean $onlyown Retrieves owning relations only (default: false).
     *
     * @return array List of available relations => tids.
     */
    public function getRelations($onlyown = true)
    {
        $tablename = $this->_table->getInternalTableName();
        $tid = PageMaster_Util::getTidFromStringSuffix($tablename);

        $relations = array();

        // load own
        $records = PageMaster_Util::getRelations($tid, true);
        foreach ($records as $relation) {
            $relations[$relation['alias1']] = array(
                'tid'   => $relation['tid2'],
                'type'  => $relation['type'],
                'own'   => true
            );
        }

        if (!$onlyown) {
            // load foreign
            $records = PageMaster_Util::getRelations($tid, false);

            foreach ($records as $relation) {
                $relations[$relation['alias2']] = array(
                    'tid'   => $relation['tid1'],
                    'type'  => $relation['type'],
                    'own'   => false
                );
            }
        }

        return $relations;
    }
}
