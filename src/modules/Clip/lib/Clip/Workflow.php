<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Workflow
 */

/**
 * Clip_Workflow class.
 *
 * From a developers standpoint, we only use this class to address workflows
 * as the rest is for internal use by the workflow engine.
 */
class Clip_Workflow extends Zikula_AbstractBase
{
    // Action types
    const ACTIONS_ALL = 1;
    const ACTIONS_MASSIVE = 2;
    const ACTIONS_FORM = 3;
    const ACTIONS_EXEC = 4;
    const ACTIONS_INLINE = 5;
    const ACTIONS_CUSTOM = 6;

    /**
     * Item object.
     *
     * @var object
     */
    protected $obj;

    /**
     * Schema name.
     *
     * @var string
     */
    protected $schema;

    /**
     * Module name.
     *
     * @var string
     */
    protected $module;

    /**
     * Module table.
     *
     * @var string
     */
    protected $table;

    /**
     * Table Id column.
     *
     * @var string
     */
    protected $idcolumn;

    /**
     * Constructor.
     *
     * @param array           $args Arguments as scheme, module, table and idcolumn.
     * @param Doctrine_Record $obj  Object to process its workflow.
     */
    /*
    public function __construct(array $args, Doctrine_Record &$obj = null)
    {
        if (!isset($args['schema']) || !isset($args['module']) || !isset($args['table']) || !isset($args['idcolumn'])) {
            throw new Exception('Missing required parameter for Clip_Workflow');
        }

        parent::__construct(ServiceUtil::getManager());

        $this->module   = $args['module'];
        $this->schema   = $args['schema'];
        $this->table    = $args['table'];
        $this->idcolumn = $args['idcolumn'];

        if ($obj) {
            $this->obj = $obj;
        }
    }
    */

    /**
     * Constructor with publication type.
     *
     * @param Clip_Model_Pubtype $pubtype Publication type of the object.
     * @param Doctrine_Record    $obj     Object to process its workflow.
     */
    public function __construct(Clip_Model_Pubtype $pubtype, Doctrine_Record &$obj = null)
    {
        parent::__construct(ServiceUtil::getManager());

        $this->setup($pubtype, $obj);
    }

    /**
     * Setup.
     *
     * @param Clip_Model_Pubtype $pubtype Publication type of the object.
     * @param Doctrine_Record    $obj     Object to process its workflow.
     *
     * @return void
     */
    public function setup(Clip_Model_Pubtype $pubtype, Doctrine_Record &$obj = null)
    {
        $this->module   = 'Clip';
        $this->schema   = $pubtype->getSchema();
        $this->table    = $pubtype->getTableName();
        $this->idcolumn = 'id';

        $this->obj = $obj ? $obj : $pubtype->getPubInstance();
    }

    /**
     * Load workflow for object.
     *
     * Will attach the array '__WORKFLOW__' to the object.
     *
     * @return mixed Workflow information or false for non existing object.
     */
    public function getWorkflow($field = null)
    {
        if (isset($this->obj['__WORKFLOW__'])) {
            $workflow = $this->obj['__WORKFLOW__'];

            if ($field && isset($workflow[$field])) {
                return $workflow[$field];
            }

            return $workflow;
        }

        $workflow = null;

        if (!empty($this->obj[$this->idcolumn])) {
            // get workflow data from DB
            $dbtables = DBUtil::getTables();
            $wfcolumn = $dbtables['workflows_column'];
            $where = "WHERE $wfcolumn[module] = '" . DataUtil::formatForStore($this->module) . "'
                        AND $wfcolumn[obj_table] = '" . DataUtil::formatForStore($this->table) . "'
                        AND $wfcolumn[obj_idcolumn] = '" . DataUtil::formatForStore($this->idcolumn) . "'
                        AND $wfcolumn[obj_id] = '" . DataUtil::formatForStore($this->obj[$this->idcolumn]) . "'";

            $workflow = DBUtil::selectObject('workflows', $where);
        }

        if (!$workflow) {
            $workflow = array('state'        => 'initial',
                              'schemaname'   => $this->schema,
                              'module'       => $this->module,
                              'obj_table'    => $this->table,
                              'obj_idcolumn' => $this->idcolumn,
                              'obj_id'       => null);
        }

        // adds the translated state title
        $states = Clip_Workflow_Util::getStatesMap($this->module, $this->schema);

        $workflow['statetitle'] = isset($states[$workflow['state']]) ? $states[$workflow['state']]['title'] : $this->__('Invalid');

        // attach workflow to object
        $this->obj->mapValue('__WORKFLOW__', $workflow);

        if ($field && isset($workflow[$field])) {
            return $workflow[$field];
        }

        return $workflow;
    }

    /**
     * Register workflow by $metaId.
     *
     * @param string $stateID State Id.
     *
     * @return boolean
     */
    private function registerWorkflow($stateID = 'initial')
    {
        $idcolumn = $this->obj['__WORKFLOW__']['obj_idcolumn'];

        $rec = array('obj_table'    => $this->obj['__WORKFLOW__']['obj_table'],
                     'obj_idcolumn' => $this->obj['__WORKFLOW__']['obj_idcolumn'],
                     'obj_id'       => $this->obj[$idcolumn],
                     'module'       => $this->module,
                     'schemaname'   => $this->schema,
                     'state'        => $stateID);

        if (!DBUtil::insertObject($rec, 'workflows')) {
            return false;
        }

        $this->obj->mapValue('__WORKFLOW__', $rec);

        return true;
    }

    /**
     * Update workflow state.
     *
     * @param string $stateID State Id.
     *
     * @return boolean
     */
    private function updateWorkflow($stateID)
    {
        $rec = array('id'    => $this->obj['__WORKFLOW__']['id'],
                     'state' => $stateID);

        return (bool)DBUtil::updateObject($rec, 'workflows');
    }

    /**
     * Delete a workflow and associated data.
     *
     * @return boolean
     */
    public function deleteWorkflow()
    {
        $wid = $this->obj['__WORKFLOW__']['id'];

        if (!$this->obj->delete()) {
            return false;
        }

        return (bool)DBUtil::deleteObjectByID('workflows', $wid);
    }

    /**
     * Execute workflow action.
     *
     * @param string $actionID Action Id.
     * @param string $stateID  State Id.
     *
     * @return mixed Array or false.
     */
    public function executeAction($actionID)
    {
        $stateID = $this->getWorkflow('state');

        $actionMap = Clip_Workflow_Util::getActionsMap($this->module, $this->schema, $stateID);

        // check if state exists
        if (!$actionMap) {
            return LogUtil::registerError($this->__f('State [%s] not found.', $stateID));
        }

        // check the action exists for given state
        if (!isset($actionMap[$actionID])) {
            return LogUtil::registerError($this->__f('Action [%1$s] not available in State [%2$s].', array($actionID, $stateID)));
        }

        $action = $actionMap[$actionID];

        // permission check
        if (!Clip_Workflow_Util::permissionCheck($this->obj, $this->module, $this->schema, $action['permission'], $actionID)) {
            return LogUtil::registerError($this->__f('No permission to execute the [%s] action.', $actionID));
        }

        // define the next state to be passed to the operations
        $nextState = (isset($action['nextState']) ? $action['nextState'] : $stateID);

        // process the action operations
        $result = array();
        foreach ($action['operations'] as $operation) {
            // execute the operation
            $r = $this->executeOperation($operation, $nextState);
            if ($r === false) {
                // if an operation fails here, do not process further and return false
                return false;
            }
            // adds the operation result to the result stack
            // operations must return an array instead of a false on failure
            if (isset($result[$operation['name']])) {
                $result[$operation['name']] = array_merge($result[$operation['name']], $r);
            } else {
                $result[$operation['name']] = $r;
            }
        }

        // if this is an initial object then we need to register in the DB
        if ($stateID == 'initial') {
            $this->registerWorkflow();
        }

        // test if state doesn't need to be updated
        if ($stateID == $nextState) {
            return $result;
        }

        // change the workflow state
        $this->updateWorkflow($nextState);

        // return result of all operations
        return $result;
    }

    /**
     * Execute workflow operation within action.
     *
     * @param string $operation  Operation name.
     * @param string &$nextState Next state.
     *
     * @return mixed|false
     */
    private function executeOperation($operation, &$nextState)
    {
        $params = $operation['parameters'];

        // FIXME review logic here. possible cases
        if (isset($params['nextstate'])) {
            $nextState = $params['nextstate'];
        }
        $params['nextstate'] = $nextState;

        $function = "{$this->module}_operation_{$operation['name']}";

        if (!function_exists($function)) {
            // test if operation file exists
            $file = "operations/function.{$operation['name']}.php";
            $path = Clip_Workflow_Util::findPath($file, $this->module);

            if (!$path) {
                return LogUtil::registerError($this->__f('Workflow operation file [%s] does not exist', $operation['name']));
            }

            // load file and test if function exists
            include_once $path;

            if (!function_exists($function)) {
                return LogUtil::registerError($this->__f('Workflow operation function [%s] is not defined', $function));
            }
        }

        // execute operation and return the result
        $result = $function($this->obj, $params);

        // checks for an valid next state value
        $states = array_keys(Clip_Workflow_Util::getStatesMap($this->module, $this->schema));

        if (isset($params['nextstate']) && in_array($params['nextstate'], $states)) {
            $nextState = $params['nextstate'];
        }

        // return the operation result
        return $result;
    }

    /**
     * Workflow actions filtered by mode.
     *
     * @param array   $actions Actions to filter.
     * @param integer $mode    One of the Clip_Workflow modes.
     *
     * @return array Filtered actions.
     */
    public function filterActionsByMode($actions, $mode = self::ACTIONS_INLINE)
    {
        if ($mode != self::ACTIONS_ALL) {
            // process the specific modes
            foreach ($actions as $id => $action) {
                switch ($mode)
                {
                    case self::ACTIONS_MASSIVE:
                        if (!isset($action['parameters']['action']['massive']) || !(bool)$action['parameters']['action']['massive']) {
                            unset($actions[$id]);
                        }
                        break;

                    case self::ACTIONS_FORM:
                        if (isset($action['parameters']['action']['mode']) && $action['parameters']['action']['mode'] != 'form') {
                            unset($actions[$id]);
                        }
                        break;

                    case self::ACTIONS_EXEC:
                        if (isset($action['parameters']['action']['mode']) && $action['parameters']['action']['mode'] != 'exec') {
                            unset($actions[$id]);
                        }
                        break;

                    case self::ACTIONS_INLINE:
                        if (!isset($action['parameters']['action']['mode']) || $action['parameters']['action']['mode'] != 'exec') {
                            unset($actions[$id]);
                        }
                        break;

                    case self::ACTIONS_CUSTOM:
                        if (!isset($action['parameters']['action']['mode']) || $action['parameters']['action']['mode'] != 'custom') {
                            unset($actions[$id]);
                        }
                        break;
                }
            }
        }

        return $actions;
    }

    /**
     * Workflow actions available for the current object and user.
     *
     * @param integer $mode  One of the Clip_Workflow modes.
     * @param string  $state State actions to retrieve, default = object's state.
     *
     * @return array Allowed actions.
     */
    public function getActions($mode = self::ACTIONS_ALL, $state = null)
    {
        if (!$state) {
            $state = $this->getWorkflow('state');
        }

        // load up the workflow actions
        $actions = Clip_Workflow_Util::getActionsMap($this->module, $this->schema, $state);

        if (!$actions) {
            return false;
        }

        // check if there's an object to evaluate the actions against
        if ($this->obj) {
            foreach ($actions as $id => $action) {
                // check if the action has restriction(s)
                $skip = false;
                if (isset($action['parameters']['condition'])) {
                    foreach ((array)$action['parameters']['condition'] as $field => $value) {
                        // only check valid fields
                        if ($this->obj->contains($field) && $this->obj[$field] != $value) {
                            $skip = true;
                            break;
                        }
                    }
                }
                // unset it if restricted or the user has no access to it
                if ($skip || !Clip_Workflow_Util::permissionCheck($this->obj, $this->module, $this->schema, $action['permission'], $id)) {
                    unset($actions[$id]);
                }
            }
        }

        return $this->filterActionsByMode($actions, $mode);
    }

    /**
     * Get workflow actions titles by state.
     *
     * Returns allowed action ids and titles only, for given state.
     *
     * @param string  $field Field to retrieve (title, description, permission, state, nextState).
     * @param integer $mode  One of the Clip_Workflow modes.
     * @param string  $state State actions to retrieve, default = object's state.
     *
     * @return mixed Array of allowed actions on the form $action[id] => $action[$field] or false on failure.
     */
    public function getActionsField($field = 'title', $mode = self::ACTIONS_ALL, $state = null)
    {
        if (!in_array($field, array('title', 'description', 'permission', 'state', 'nextState'))) {
            return false;
        }

        $actions = $this->getActions($mode, $state);

        if ($actions) {
            foreach (array_keys($actions) as $id) {
                $actions[$id] = $actions[$id][$field];
            }
        }

        return $actions;
    }

    /**
     * Get an access level by state.
     *
     * @param integer $level Level to get (default: 0 - minimum).
     * @param string  $state State to evaluate, default = object's state.
     * @param integer $mode  One of the Clip_Workflow modes.
     *
     * @return integer The level requested inside the available state permissions.
     */
    public function getPermissionLevel($level = 0, $state = null, $mode = self::ACTIONS_ALL)
    {
        $statelevels = $this->getActionsField('permission', $mode, $state);

        // checks an invalid state
        if (!$statelevels) {
            return false;
        }

        $statelevels = array_unique($statelevels);
        sort($statelevels);

        if ($level >= count($statelevels)) {
            $level = count($statelevels) - 1;
        }

        return $statelevels[$level];
    }

    /**
     * Get the highest allowed action for a given state.
     *
     * @param string  $field Optional field to retrieve (title, description, permission, state, nextState).
     * @param integer $mode  One of the Clip_Workflow modes.
     * @param string  $state State actions to retrieve, default = object's state.
     *
     * @return mixed Highest allowed actions or false on failure.
     */
    public function getHighestAction($field = null, $mode = self::ACTIONS_ALL, $state = null)
    {
        if ($field && !in_array($field, array('id', 'title', 'description', 'permission', 'state', 'nextState'))) {
            return false;
        }

        $statelevels = $this->getActionsField('permission', $mode, $state);

        // checks an invalid state
        if (!$statelevels) {
            return false;
        }

        $statelevels = array_unique($statelevels);
        rsort($statelevels);
        $higherlevel = reset($statelevels);

        // eval what's the FIRST action allowed with the highest level
        $actions = $this->getActions($mode, $state);

        foreach ($actions as $id => $action) {
            if ($action['permission'] == $higherlevel) {
                break;
            }
        }

        return $field ? $action[$field] : $action;
    }

    /**
     * Get the first allowed action for a given state.
     *
     * @param string  $field Optional field to retrieve (title, description, permission, state, nextState).
     * @param integer $mode  One of the Clip_Workflow modes.
     * @param string  $state State actions to retrieve, default = object's state.
     *
     * @return mixed First allowed actions or false on failure.
     */
    public function getFirstAction($field = null, $mode = self::ACTIONS_ALL, $state = null)
    {
        if ($field && !in_array($field, array('id', 'title', 'description', 'permission', 'state', 'nextState'))) {
            return false;
        }

        // eval what's the FIRST action allowed
        $actions = $this->getActions($mode, $state);

        if (empty($actions)) {
            return false;
        }

        $action = reset($actions);

        return $field ? $action[$field] : $action;
    }

    /**
     * Method to validate a specific state string inside the object schema.
     *
     * @param string $state State to validate.
     *
     * @return boolean True if valid, false otherwise.
     */
    public function isValidState($state)
    {
        $states = array_keys(Clip_Workflow_Util::getStatesMap($this->module, $this->schema));

        return in_array($state, $states);
    }

    /**
     * Method to validate a specific action string for the object state.
     *
     * @param string $action Action to validate.
     *
     * @return boolean True if valid, false otherwise.
     */
    public function isValidAction($action)
    {
        $actions = array_keys($this->getActions());

        return in_array($action, $actions);
    }

    /**
     * Item object setter.
     *
     * @param object &$obj Record object.
     *
     * @return void
     */
    public function setObj(&$obj)
    {
        $this->obj = $obj;
    }

    /**
     * Item object getter.
     *
     * @return object Record being processed.
     */
    public function getObj()
    {
        return $this->obj;
    }

    /**
     * Get workflow Module.
     *
     * @return string Module name.
     */
    public function getModule()
    {
        return $this->module;
    }
}
