<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Base_Class
 */

/**
 * This is the model class that define the entity structure and behaviours.
 */
class Clip_Base_Pubdata extends Doctrine_Record
{
    /**
     * Record load post process.
     *
     * @param boolean $args['handleplugins']        Whether to parse the plugin fields.
     * @param boolean $args['loadworkflow']         Whether to add the workflow information.
     * @param boolean $args['rel']['processrefs']   Whether to process the related records.
     * @param boolean $args['rel']['onlyown']       Whether to check the permissions.
     * @param boolean $args['rel']['checkperm']     Whether to check the permissions.
     * @param boolean $args['rel']['handleplugins'] Whether to parse the plugin fields.
     * @param boolean $args['rel']['loadworkflow']  Whether to add the workflow information.
     *
     * @return void
     */
    public function clipProcess($args = array())
    {
        // map basic values
        $this->clipValues();
        $this->mapValue('__WORKFLOW__',  array('state' => 'initial'));

        // handle the plugins data if needed
        if (isset($args['handleplugins']) && $args['handleplugins']) {
            $this->clipData();
        }

        // load the workflow data if needed
        if (isset($args['loadworkflow']) && $args['loadworkflow']) {
            $this->clipWorkflow();
        }

        $this->mapValue('core_approvalstate', isset($this['__WORKFLOW__']['state']) ? $this['__WORKFLOW__']['state'] : null);

        // post process related records
        if (isset($args['rel']['processrefs']) && $args['rel']['processrefs']) {
            // new default values
            $args = array(
                'handleplugins' => isset($args['rel']['handleplugins']) ? $args['rel']['handleplugins'] : false,
                'loadworkflow'  => isset($args['rel']['loadworkflow']) ? $args['rel']['loadworkflow'] : false,
                'rel' => array(
                    'processrefs'   => false,
                    'onlyown'       => isset($args['rel']['onlyown']) ? $args['rel']['onlyown'] : true,
                    'checkperm'     => isset($args['rel']['checkperm']) ? $args['rel']['checkperm'] : false,
                    'handleplugins' => isset($args['rel']['handleplugins']) ? $args['rel']['handleplugins'] : false,
                    'loadworkflow'  => isset($args['rel']['loadworkflow']) ? $args['rel']['loadworkflow'] : false
                )
            );
            // process the loaded related records
            foreach ($this->getRelations($args['rel']['onlyown']) as $alias => $relation) {
                if ($this->hasReference($alias)) {
                    if ($this->clipRelation($alias, $args['rel']['checkperm'])) {
                        if ($this[$alias] instanceof Doctrine_Record) {

                        }
                    }
                }
            }
        }
    }

    /**
     * Basic values loader for the Record.
     *
     * @return void
     */
    public function clipValues($handleplugins=false)
    {
        $tablename = $this->_table->getInternalTableName();
        $tid = Clip_Util::getTidFromString($tablename);

        $core_title = Clip_Util::getTitleField($tid);

        $this->mapValue('core_tid',      $tid);
        $this->mapValue('core_uniqueid', $tid.'-'.$this['core_pid']);
        $this->mapValue('core_title',    $this[$core_title]);
        $this->mapValue('core_creator',  ($this['core_author'] == UserUtil::getVar('uid')) ? true : false);

        if ($handleplugins) {
            $this->clipData();
        }
    }

    /**
     * Plugins postRead processing for the Record.
     *
     * @return void
     */
    public function clipData()
    {
        Clip_Util::handlePluginFields($this);
    }

    /**
     * Workflow loader for the Record.
     *
     * @return void
     */
    public function clipWorkflow()
    {
        if ($this->id) {
            $tablename = $this->_table->getInternalTableName();
            Zikula_Workflow_Util::getWorkflowForObject($this, $tablename, 'id', 'Clip');

        } else {
            $this->mapValue('__WORKFLOW__',  array('state' => 'initial'));
        }

        $this->mapValue('core_approvalstate', isset($this['__WORKFLOW__']['state']) ? $this['__WORKFLOW__']['state'] : null);
    }

    /**
     * Relations permission filter.
     *
     * @return boolean Existing relation flag.
     */
    public function clipRelation($alias, $checkperm=true)
    {
        if (!$this->get($alias, true) || !($relation = $this->getRelation($alias))) {
            return false;
        }

        if ($this[$alias] instanceof Doctrine_Record) {
            if ($checkperm && !SecurityUtil::checkPermission('clip:full:', "{$relation['tid']}:{$this[$alias]['core_pid']}:", ACCESS_READ)) {
                $this[$alias] = false;
            }
            return (bool)$this[$alias];

        } elseif ($this[$alias] instanceof Doctrine_Collection) {
            foreach ($this[$alias] as $k => $v) {
                if ($checkperm && !SecurityUtil::checkPermission('clip:full:', "{$relation['tid']}:{$this[$alias]['core_pid']}:", ACCESS_READ)) {
                    unset($this[$alias][$k]);
                }
            }
            return (bool)count($this[$alias]);
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

        // FIXME From Doctrine: prevent mapped Doctrine_Records from being displayed fully
        foreach ($this->_values as $key => $value) {
            $fields[$key] = 'value';
        }

        foreach ($this->_table->getRelations() as $key => $relation) {
            if (strpos($key, 'Clip_Model_Relation') !== 0) {
                $fields[$key] = 'relation';
            }
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
        $tid = Clip_Util::getTidFromString($tablename);

        return Clip_Util::getPubType($tid)->getRelations($onlyown);
    }

    /**
     * Returns the information of a specific relation.
     *
     * @param string $alias Alias of the relation.
     *
     * @return array Information of the relation if exists, false otherwise.
     */
    public function getRelation($alias)
    {
        $relations = $this->getRelations(false);

        return isset($relations[$alias])? $relations[$alias] : false;
    }
}
