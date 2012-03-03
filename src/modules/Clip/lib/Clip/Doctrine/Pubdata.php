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
    public $clip_state;

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
        $a = $a ? array_intersect_key($a, $fields) : $fields;

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
     * Generates a copy of this object.
     *
     * @param boolean $deep Whether to include relations or not.
     *
     * @return object
     */
    public function copy($deep = true)
    {
        $copy = parent::copy($deep);

        foreach ($this->_values as $k => $v) {
            $copy->mapValue($k, $v);
        }

        return $copy;
    }
    
    /**
     * Returns the parents for breadcrumbs.
     *
     * @return array List of parents.
     */
    public function getAncestors()
    {
        $parents = array();

        if (!$this->hasRelation('parent')) {
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
     * Checks if a field is modified.
     *
     * @param string $field Field name to check if it is modified.
     *
     * @return boolean
     */
    public function clipModified($field)
    {
        return in_array($field, $this->_lastModified);
    }

    /**
     * Old values getter.
     *
     * @return array
     */
    public function clipOldValues($column = null)
    {
        // validate the requested column
        if ($column && !array_key_exists($column, $this->_oldValues)) {
            return false;
        }

        if ($column) {
            $value = $this->_oldValues[$column];

            return ($value instanceof Doctrine_Null ? null : $value);
        }

        $values = $this->_oldValues;

        foreach ($values as &$value) {
            $value = $value instanceof Doctrine_Null ? null : $value;
        }

        return $values;
    }

    /**
     * Record load post process.
     * For internal use only.
     *
     * @param boolean $args['handleplugins']      Whether to parse the plugin fields.
     * @param boolean $args['loadworkflow']       Whether to add the workflow information.
     * @param boolean $args['rel']['load']        Whether to load the relations or not.
     * @param boolean $args['rel']['onlyown']     Whether to load onlyown relations.
     * @param boolean $args['rel']['checkperm']   Whether to check the permissions.
     *
     * @return $this
     */
    public function clipProcess($args = array())
    {
        // load the workflow data if needed
        if ($args['loadworkflow']) {
            $this->clipWorkflow();
        }

        // handle the default values and plugins data if needed
        $this->clipValues($args['handleplugins']);

        // post process related records
        if ($args['rel']['load'] && $args['rel']['checkperm']) {
            // process the loaded related records
            foreach ($this->getRelations($args['rel']['onlyown']) as $alias => $relation) {
                if ($this->hasReference($alias)) {
                    // process the relation permissions
                    $this->clipRelation($alias, $args['rel']['checkperm']);
                }
            }
        }

        return $this;
    }

    /**
     * Basic values loader for the Record.
     *
     * @param boolean $handleplugins Whether to parse the plugin fields.
     *
     * @return $this
     */
    public function clipValues($handleplugins=false)
    {
        $this->mapValue('core_title',    $this[$this->core_titlefield]);
        $this->mapValue('core_uniqueid', $this->core_pid ? $this->core_tid.'-'.$this->core_pid : null);
        $this->mapValue('core_creator',  ($this->core_author == UserUtil::getVar('uid')) ? true : false);

        $this->mapValue('core_approvalstate', isset($this['__WORKFLOW__']) ? $this['__WORKFLOW__']['state'] : null);

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
        if (!$this->clip_state) {
            $this->clip_state = true;

            Clip_Util_Plugins::postRead($this);
        }

        return $this;
    }

    /**
     * Workflow loader for the Record.
     *
     * @param boolean $field Field of the workflow information to retrieve (optional).
     *
     * @return $this
     */
    public function clipWorkflow($field = null)
    {
        if (isset($this['__WORKFLOW__'])) {
            return ($field && array_key_exists($field, $this['__WORKFLOW__'])) ? $this['__WORKFLOW__'][$field] : $this;
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
     * @param boolean $alias     Relation alias to process.
     * @param boolean $checkperm Whether to check or not the related publications permissions (default: true).
     *
     * @return boolean Existing relation flag.
     */
    public function clipRelation($alias, $checkperm = true)
    {
        if (!$this->get($alias, true) || !($relation = $this->getRelation($alias))) {
            return false;
        }

        // process a record
        if ($this[$alias] instanceof Doctrine_Record) {
            // check the list and individual permission if needed
            if ($checkperm && (!Clip_Access::toPubtype($relation['tid'], 'list') || !Clip_Access::toPub($relation['tid'], $this[$alias], null, ACCESS_READ, null, 'display'))) {
                $this[$alias] = false;
            }

            return (bool)$this[$alias];

        // process a collection
        } elseif ($this[$alias] instanceof Doctrine_Collection) {
            // check the list permission if needed
            if ($checkperm && !Clip_Access::toPubtype($relation['tid'], 'list')) {
                $this[$alias] = false;

            } else {
                // process each related publication permission
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
     * Form initial processing.
     *
     * @param boolean $loadrels Whether to load the related publications or not (default: false).
     * @param boolean $onlyown  Whether to load only own relations or all (default: true).
     *
     * @return array Requested publication data as an array.
     */
    public function clipFormGet($loadrels = false, $onlyown = true)
    {
        $data = $this->toArray(false);

        // load the relation if requested to
        if ($loadrels) {
            foreach (array_keys($this->getRelations($onlyown)) as $alias) {
                // set the data object
                if ($this->$alias instanceof Doctrine_Collection) {
                    foreach ($this->$alias as $k => &$v) {
                        // exclude null records
                        if (!$v->exists()) {
                            unset($this->$alias[$k]);
                        }
                    }
                    $data[$alias] = $this->$alias->toArray(false);

                } elseif ($this->$alias instanceof Doctrine_Record && $this->$alias->exists()) {
                    $data[$alias] = $this->$alias->toArray(false);

                } else {
                    $data[$alias] = null;
                }
            }
        }

        // fill the existing relations with the aliases to be able to resolve the id
        foreach ($this->getRelations(false) as $alias => $info) {
            $field = "rel_{$info['id']}";
            // check that the relation is not loaded and the feld exists
            if ($info['single'] && !isset($data[$alias]) && isset($data[$field])) {
                $data[$alias] = $data[$field];
            }
        }

        return $data;
    }

    /**
     * Form post processing.
     *
     * @param array $pubdata Publication data to fill into this record.
     * @param array $links   Relations to be filled into this record.
     *
     * @return $this
     */
    public function clipFormFill($pubdata, $links)
    {
        // allow specify fixed PIDs for new pubs
        if (isset($pubdata['core_pid']) && !$this->core_pid) {
            // accessing core_pid will fetch the record from the DB if not set
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
                    foreach ($links[$alias] as $id) {
                        if ($id && !in_array((string)$id, $pubdata[$alias])) {
                            $tounlink[] = (int)$id;
                        }
                    }
                    // check the added ones
                    foreach ($pubdata[$alias] as $id) {
                        if ($id && !in_array((int)$id, $links[$alias])) {
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
            if ($this->getTable()->hasField($key) || $this->hasMappedValue($key)) {              
                $this->set($key, $value, false);
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
     * Validates if the passed field name is a valid column.
     *
     * @return boolean
     */
    public function isPubField($fieldname)
    {
        $fields = array();

        foreach ($this as $column => $value) {
            $fields[$column] = true;
        }

        return isset($fields[$fieldname]);
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
            'core_title' => 'value',
            'core_urltitle' => 'column',
            'core_uniqueid' => 'value',
            'core_tid' => 'value',
            'core_pid' => 'column',
            'core_author' => 'column',
            'core_creator' => 'value',
            'core_approvalstate' => 'value'
        );
        $fields = array_merge($reorder, $fields);

        return $fields;
    }

    /**
     * Zikula hooks notifier.
     *
     * @param string $hooktype Hook type to notify.
     *
     * @return void
     */
    public function notifyHooks($hooktype)
    {
        $pubtype = Clip_Util::getPubType($this->core_tid);
        $urlobj  = Clip_Util::urlobj($this, 'display'); // describes how to retrieve this object by URL metadata
        $hook    = new Zikula_ProcessHook($pubtype->getHooksEventName($hooktype), $this->core_uniqueid, $urlobj);
        ServiceUtil::getManager()->getService('zikula.hookmanager')->notify($hook);
    }

    /**
     * urltitle corrector.
     *
     * @return void
     */
    public function validateUrltitle()
    {
        if (!$this->core_urltitle) {
            return;
        }

        // validate the unique urltitle
        $pid = $this->getTable()->selectFieldBy('core_pid', $this->core_urltitle, 'core_urltitle');

        while ($pid && $pid != $this->core_pid) {
            // TODO better to throw a validation exception
            ++$this->core_urltitle;

            $pid = $this->getTable()->selectFieldBy('core_pid', $this->core_urltitle, 'core_urltitle');
        }
    }

    /**
     * preInsert hook.
     *
     * @return void
     */
    public function preInsert($event)
    {
        $pub = $event->getInvoker();

        // figures out a publication id
        if (!$pub['core_pid']) {
            $pub['core_pid'] = $this->getTable()->selectFieldFunction('core_pid', 'MAX') + 1;
        }

        // assign the author
        $pub['core_author'] = (int)UserUtil::getVar('uid');

        // assign the language
        if (is_null($pub['core_language'])) {
            $pub['core_language'] = '';
        }

        // fills the publish date automatically
        if (empty($pub['core_publishdate'])) {
            $pub['core_publishdate'] = DateUtil::getDatetime();
        }
    }

    /**
     * preSave hook.
     *
     * @return void
     */
    public function preSave($event)
    {
        $pub = $event->getInvoker();

        // figures out a publication id
        if (!$pub['core_pid']) {
            $pub['core_pid'] = $this->getTable()->selectFieldFunction('core_pid', 'MAX') + 1;
        }

        // fills the urltitle
        if (!$pub['core_urltitle']) {
            $urltitle = $pub[$pub['core_titlefield']] ? $pub[$pub['core_titlefield']] : $pub['core_pid'];
            $pub['core_urltitle'] = substr(DataUtil::formatPermalink($urltitle), 0, 255);
        }

        $pub->validateUrltitle();

        // invoke the preSave hook on pubfields
        if (isset($pub['core_tid'])) {
            // FIXME move to a non-util method? internal recognition
            $pubfields = Clip_Util::getPubFields($pub['core_tid']);

            $modified = array_keys($pub->getModified());

            foreach ($pubfields as $fieldname => $field)
            {
                if (!in_array($fieldname, $modified)) {
                    continue;
                }

                $plugin = Clip_Util_Plugins::get($field['fieldplugin']);

                if (method_exists($plugin, 'preSave')) {
                    $pub[$fieldname] = $plugin->preSave($pub, $field);
                }
            }
        }
    }

    /**
     * postSave hook.
     *
     * @return void
     */
    public function postSave($event)
    {
        $pub = $event->getInvoker();

        // update the meta values
        $pub->clipValues();
    }
}
