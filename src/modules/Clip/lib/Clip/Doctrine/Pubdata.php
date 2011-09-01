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
 * Clip's base class for publications.
 */
class Clip_Doctrine_Pubdata extends Doctrine_Record
{
    protected $state;

    /**
     * String output of a publication.
     *
     * @return string Empty string to not interfer calls from templates.
     */
    public function  __toString()
    {
        return '';
    }

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
     * @return $this
     */
    public function clipProcess($args = array())
    {
        // map basic values
        $this->clipValues();

        // handle the plugins data if needed
        if (isset($args['handleplugins']) && $args['handleplugins']) {
            $this->clipPostRead();
        }

        // load the workflow data if needed
        if (isset($args['loadworkflow']) && $args['loadworkflow']) {
            $this->clipWorkflow();
        }

        $this->mapValue('core_approvalstate', isset($this['__WORKFLOW__']) ? $this['__WORKFLOW__']['state'] : null);

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
                            // TODO process??
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Basic values loader for the Record.
     *
     * @return $this
     */
    public function clipValues($handleplugins=false)
    {
        if (!$this->hasMappedValue('core_tid') || !$this->hasMappedValue('core_title')) {
            $tablename = $this->_table->getInternalTableName();
            $tid = Clip_Util::getTidFromString($tablename);

            $core_title = Clip_Util::getTitleField($tid);

            $this->mapValue('core_tid',      $tid);
            $this->mapValue('core_title',    $this[$core_title]);
        }

        $this->mapValue('core_uniqueid', $this['core_tid'].'-'.$this['core_pid']);
        $this->mapValue('core_creator',  ($this['core_author'] == UserUtil::getVar('uid')) ? true : false);

        if ($handleplugins) {
            $this->clipPostRead();
        }

        return $this;
    }

    /**
     * Plugins postRead processing for the Record.
     *
     * @return $this
     */
    public function clipPostRead()
    {
        if (!$this->state) {
            $this->state = true;

            Clip_Util_Plugins::postRead($this);
        }

        return $this;
    }

    /**
     * Workflow loader for the Record.
     *
     * @return $this
     */
    public function clipWorkflow($field = null)
    {
        if (isset($this['__WORKFLOW__'])) {
            return ($field && array_key_exists($field, $this['__WORKFLOW__'])) ? $this['__WORKFLOW__'][$field] : $this;
        }

        if (!isset($this['core_tid'])) {
            $this->clipValues();
        }

        $pubtype  = Clip_Util::getPubType($this['core_tid']);
        $workflow = new Clip_Workflow($pubtype, $this);
        $workflow->getWorkflow();

        $this->mapValue('core_approvalstate', isset($this['__WORKFLOW__']['state']) ? $this['__WORKFLOW__']['state'] : null);

        return ($field && array_key_exists($field, $this['__WORKFLOW__'])) ? $this['__WORKFLOW__'][$field] : $this;
    }

    /**
     * Relations permission filter.
     *
     * @return boolean Existing relation flag.
     */
    public function clipRelation($alias, $checkperm = true)
    {
        if (!$this->get($alias, true) || !($relation = $this->getRelation($alias))) {
            return false;
        }

        if ($this[$alias] instanceof Doctrine_Record) {
            // check the list and individual permission if needed
            if ($checkperm && (!Clip_Access::toPubtype($relation['tid'], 'list') || !Clip_Access::toPub($relation['tid'], $this[$alias], null, ACCESS_READ, null, 'display'))) {
                $this[$alias] = false;
            }
            return (bool)$this[$alias];

        } elseif ($this[$alias] instanceof Doctrine_Collection) {
            if ($checkperm && !Clip_Access::toPubtype($relation['tid'], 'list')) {
                $this[$alias] = false;
            } else {
                foreach ($this[$alias] as $k => $v) {
                    if ($checkperm && !Clip_Access::toPub($relation['tid'], $this[$alias][$k], null, ACCESS_READ, null, 'display')) {
                        unset($this[$alias][$k]);
                    }
                }
            }
            return (bool)count($this[$alias]);
        }
    }

    /**
     * Form post processing.
     *
     * @return boolean Existing relation flag.
     */
    public function clipFormFill($pubdata, $links)
    {
        // allow specify fixed PIDs for new pubs
        if (!$this->core_pid && isset($pubdata['core_pid'])) {
            $this->core_pid = $pubdata['core_pid'];
        }

        foreach (array_keys($this->getRelations(false)) as $alias) {
            // stores the relations data if present
            // for later DB update
            if (isset($pubdata[$alias])) {
                // be sure it's an existing form relation
                if (isset($links[$alias])) {
                    $tolink = $tounlink = array();

                    // check the removed ones
                    foreach ($links as $id) {
                        if ($id && !in_array((string)$id, $pubdata[$alias])) {
                            $tounlink[] = (int)$id;
                        }
                    }
                    // check the added ones
                    foreach ($pubdata[$alias] as $id) {
                        if ($id && !in_array((int)$id, $links)) {
                            $tolink[] = (int)$id;
                        }
                    }

                    // perform the operations
                    if ($tolink) {
                        $this->link($alias, $tolink);
                    }
                    if ($tounlink) {
                        $this->unlink($alias, $tounlink);
                    }
                }

                // unset this data field
                unset($pubdata[$alias]);
            }
        }

        // fill any other data and map any "outer" value
        foreach ($pubdata as $key => $value) {
            if ($this->getTable()->hasField($key) || array_key_exists($key, $this->_values)) {
                $this->set($key, $value);
            } else {
                $method = 'set'.Doctrine_Inflector::classify($key);
                try {
                    if (is_callable(array($this, $method))) {
                        $this->$method($value);
                    } else {
                        $this->mapValue($key, $value);
                    }
                } catch (Doctrine_Record_Exception $e) {
                    $this->mapValue($key, $value);
                }
            }
        }

        return $this;
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

        foreach ($this->_values as $key => $value) {
            $fields[$key] = 'value';
        }

        foreach ($this->_table->getRelations() as $key => $relation) {
            if (strpos($key, 'ClipModels_Relation') !== 0) {
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
     * Returns an array of a publication property field.
     *
     * @param string $key   Name of the property to retrieve.
     * @param string $field Field to retrieve (optional).
     *
     * @return mixed Array with the requested field or the property if not specified.
     */
    public function toKeyValueArray($key, $field = null)
    {
        if (!$this->contains($key)) {
            throw new Exception("Invalid property [$key] requested on ".get_class()."->toKeyValueArray");
        }

        if (!$field) {
            return $this->$key;
        }

        $result = array();
        foreach ($this->$key as $k => $v) {
            if (!isset($v[$field])) {
                throw new Exception("Invalid field [$field] requested for the property [$key] on ".get_class()."->toKeyValueArray");
            }
            $result[$k] = $v[$field];
        }

        return $result;
    }

    /**
     * Returns the parents for breadcrumbs.
     *
     * @return array List of parents.
     */
    public function getAncestors()
    {
        $parents = array();

        if (!$this->hasReference('parent')) {
            return $parents;
        }

        $record = $this;

        while ($record->parent && $record->parent->exists()) {
            $record = $record->parent;
            $parents[] = $record;
        }

        return array_reverse($parents);
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

    /**
     * Save hooks.
     *
     * @return void
     */
    public function preSave($event)
    {
        $obj = $event->getInvoker();

        if (isset($obj['core_tid'])) {
            $pubfields = Clip_Util::getPubFields($obj['core_tid']);

            // TODO only modified fields check?
            // FIXME move to a non-util method
            foreach ($pubfields as $fieldname => $field)
            {
                $plugin = Clip_Util_Plugins::get($field['fieldplugin']);

                if (method_exists($plugin, 'preSave')) {
                    $obj[$fieldname] = $plugin->preSave($obj, $field);
                }
            }
        }
    }
}
