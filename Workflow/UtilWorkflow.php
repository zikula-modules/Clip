<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://github.com/zikula-modules/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Workflow
 */

namespace Matheo\Clip\Workflow;

use ModUtil;
use LogUtil;
use ZLanguage;
use DataUtil;
use Matheo\Clip\Util;
use Matheo_Clip_Model_Pubtype;
use System;
use Doctrine_Core;
use Matheo\Clip\Util\PluginsUtil;
use Matheo_Clip_Model_WorkflowVars;
use DBUtil;
use UserUtil;
use ThemeUtil;

/**
 * UtilWorkflow Class.
 */
class UtilWorkflow
{
    static $workflows = array();
    static $variables = array();
    static $varvalues = array();
    /**
     * Load XML workflow.
     *
     * @param string $schema Name of workflow scheme.
     * @param string $module Name of module.
     *
     * @return mixed Workflow array, or false on failure.
     */
    public static function loadSchema($module, $schema = 'standard')
    {
        // workflow caching
        if (isset(self::$workflows[$module][$schema])) {
            return self::$workflows[$module][$schema];
        }
        // Get module info
        $modinfo = ModUtil::getInfoFromName($module);
        if (!$modinfo) {
            return LogUtil::registerError(__f('%1$s: The specified module [%2$s] does not exist.', array('Workflow_Util::loadSchema', $module)));
        }
        $file = "{$schema}.xml";
        $path = self::findPath($file, $module);
        if (!$path) {
            return LogUtil::registerError(__f('%1$s: Unable to find the workflow file [%2$s].', array('Workflow_Util::loadSchema', $file)));
        }
        // instanciate Workflow Parser
        $parser = new ParserWorkflow();
        // parse workflow and return workflow object
        $workflowXML = file_get_contents($path);
        $data = $parser->parse(
            $workflowXML,
            $schema,
            $module
        );
        // destroy parser and XML contents
        unset($parser);
        unset($workflowXML);
        // only take the info we need
        $data = array('workflow' => $data['workflow'], 'states' => $data['states'], 'actions' => $data['actions'], 'variables' => isset($data['variables']) ? $data['variables'] : array());
        // translate the action permissions to system number values
        foreach ($data['actions'] as $state => &$actions) {
            foreach ($actions as $id => &$action) {
                $action['permission'] = self::translatePermission($action['permission']);
                // discard a bad defined action permission
                if (!$action['permission']) {
                    unset($actions[$id]);
                }
            }
        }
        // translate workflow texts according the especified or module gettext domain
        $dom = isset($data['workflow']['domain']) ? $data['workflow']['domain'] : ZLanguage::getModuleDomain($module);
        // workflow translation
        $data['workflow']['title'] = isset($data['workflow']['title']) ? __($data['workflow']['title'], $dom) : $data['workflow']['id'];
        $data['workflow']['description'] = isset($data['workflow']['description']) ? __($data['workflow']['description'], $dom) : '';
        // states translation
        foreach ($data['states'] as $id => &$state) {
            $state['title'] = isset($state['title']) ? __($state['title'], $dom) : $id;
            $state['description'] = isset($state['description']) ? __($state['description'], $dom) : '';
        }
        // actions translation
        foreach ($data['actions'] as $stateid => &$actions) {
            foreach ($actions as $id => &$action) {
                $action['title'] = isset($action['title']) ? __($action['title'], $dom) : $id;
                $action['description'] = isset($action['description']) ? __($action['description'], $dom) : '';
                // translate action parameters
                if (isset($action['parameters'])) {
                    // check if the action parameter is translatable
                    foreach (array_keys($action['parameters']) as $pname) {
                        foreach ($action['parameters'][$pname] as $k => $v) {
                            if (strpos($k, '__') === 0) {
                                unset($action['parameters'][$pname][$k]);
                                $k = substr($k, 2);
                                $action['parameters'][$pname][$k] = __($v, $dom);
                            }
                        }
                    }
                    // set the button title with the description if not set
                    if (isset($action['parameters']['button']) && !isset($action['parameters']['button']['title'])) {
                        $action['parameters']['button']['title'] = $action['description'];
                    }
                }
            }
        }
        // variables translation
        foreach ($data['variables'] as &$variable) {
            foreach ($variable as $k => $v) {
                if (strpos($k, '__') === 0) {
                    unset($variable[$k]);
                    $k = substr($k, 2);
                    $variable[$k] = __($v, $dom);
                }
            }
        }
        // cache workflow
        self::$workflows[$module][$schema] = $data;
        // return workflow object
        return self::$workflows[$module][$schema];
    }
    
    /**
     * Checks to see if a pubtype variable is set.
     *
     * @param mixed  $pubtype The pubtype ID or instance.
     * @param string $name    The name of the variable.
     *
     * @return boolean True if the variable exists in the database, false if not.
     */
    public static function hasVar($pubtype, $name)
    {
        $dom = ZLanguage::getModuleDomain('Clip');
        // validate the passed pubtype
        if (!$pubtype instanceof PubtypeModel) {
            if (!Util::validateTid($pubtype)) {
                return LogUtil::registerError(__f('%1$s: Invalid publication type ID passed [%2$s].', array('Workflow_Util::getVar', DataUtil::formatForDisplay($pubtype)), $dom));
            }
            $pubtype = Util::getPubType($pubtype);
        }
        $name = isset($name) ? (string) $name : '';
        // validate the varname
        if (!System::varValidate($name, 'modvar')) {
            return false;
        }
        if (!isset(self::$variables[$pubtype->tid])) {
            self::getVar($pubtype);
        }
        return array_key_exists($name, self::$variables[$pubtype->tid]);
    }
    
    /**
     * The getVar method gets a pubtype workflow variable.
     *
     * If the name parameter is included then method returns the
     * pubtype variable value.
     * if the name parameter is ommitted then method returns a multi
     * dimentional array of the keys and values for the pubtype vars.
     *
     * @param mixed   $pubtype The pubtype ID or instance.
     * @param string  $name    The name of the variable.
     * @param boolean $default The value to return if the requested variable is not set.
     *
     * @return mixed
     */
    public static function getVar(
        $pubtype,
        $name = '',
        $default = false
    ) {
        $dom = ZLanguage::getModuleDomain('Clip');
        // validate the passed pubtype
        if (!$pubtype instanceof PubtypeModel) {
            if (!Util::validateTid($pubtype)) {
                return LogUtil::registerError(__f('%1$s: Invalid publication type ID passed [%2$s].', array('Workflow_Util::getVar', DataUtil::formatForDisplay($pubtype)), $dom));
            }
            $pubtype = Util::getPubType($pubtype);
        }
        // if we haven't got vars for this pubtype yet then lets get them
        if (!array_key_exists($pubtype->tid, self::$variables)) {
            self::$variables[$pubtype->tid] = array();
            $where = array(array('tid = ?', $pubtype->tid), array('workflow = ?', $pubtype->workflow));
            $vars = Doctrine_Core::getTable('Clip_Model_WorkflowVars')->selectFieldArray(
                'value',
                $where,
                '',
                false,
                'setting'
            );
            foreach ($vars as $k => $v) {
                // ref #2045 vars are being stored with 0/1 unserialised.
                if ($v == '0' || $v == '1') {
                    self::$variables[$pubtype->tid][$k] = $v;
                } else {
                    self::$variables[$pubtype->tid][$k] = unserialize($v);
                }
            }
        }
        // if they didn't pass a variable name then return every variable
        // for the specified pubtype as an associative array.
        // array('var1' => value1, 'var2' => value2)
        if (empty($name)) {
            return self::$variables[$pubtype->tid];
        }
        // since they passed a variable name then only return the value for
        // that variable
        if (array_key_exists($name, self::$variables[$pubtype->tid])) {
            return self::$variables[$pubtype->tid][$name];
        }
        // we don't know the required pubtype var but we established all known
        // variables for this pubtype so the requested one can't exist.
        // we return the default (which itself defaults to false)
        return $default;
    }
    
    /**
     * The getVarValue method gets the processed variable value.
     *
     * If the name parameter is included then method returns the
     * pubtype variable value.
     * if the name parameter is ommitted then method returns a multi
     * dimentional array of the keys and values for the pubtype vars.
     *
     * @param mixed   $pubtype The pubtype ID or instance.
     * @param string  $name    The name of the variable.
     * @param boolean $default The value to return if the requested variable is not set.
     *
     * @return mixed
     */
    public static function getVarValue(
        $pubtype,
        $name = '',
        $default = false
    ) {
        // if we haven't got var values for this pubtype yet then lets get them
        if (!array_key_exists($pubtype->tid, self::$varvalues)) {
            if (($vars = self::getVar($pubtype)) === false) {
                return false;
            }
            self::$varvalues[$pubtype->tid] = array();
            $wfvars = UtilWorkflow::getSchemaVar($pubtype->getSchema());
            foreach ($wfvars as $k => $var) {
                $classname = PluginsUtil::getAdminClassname($var['plugin']);
                if (isset($vars[$k]) && $classname && method_exists($classname, 'postRead')) {
                    self::$varvalues[$pubtype->tid][$k] = $classname::postRead($vars[$k]);
                } else {
                    self::$varvalues[$pubtype->tid][$k] = isset($vars[$k]) ? $vars[$k] : null;
                }
            }
        }
        // if they didn't pass a variable name then return every variable
        // for the specified pubtype as an associative array.
        // array('var1' => value1, 'var2' => value2)
        if (empty($name)) {
            return self::$varvalues[$pubtype->tid];
        }
        // since they passed a variable name then only return the value for
        // that variable
        if (array_key_exists($name, self::$varvalues[$pubtype->tid])) {
            return self::$varvalues[$pubtype->tid][$name];
        }
        // we don't know the required pubtype var but we established all known
        // variables for this pubtype so the requested one can't exist.
        // we return the default (which itself defaults to false)
        return $default;
    }
    
    /**
     * The setVar method sets a pubtype variable.
     *
     * @param mixed  $pubtype The pubtype ID or instance.
     * @param string $name    The name of the variable.
     * @param string $value   The value of the variable.
     *
     * @return boolean True if successful, false otherwise.
     */
    public static function setVar(
        $pubtype,
        $name,
        $value = ''
    ) {
        $dom = ZLanguage::getModuleDomain('Clip');
        // validate the passed pubtype
        if (!$pubtype instanceof PubtypeModel) {
            if (!Util::validateTid($pubtype)) {
                return LogUtil::registerError(__f('%1$s: Invalid publication type ID passed [%2$s].', array('Workflow_Util::setVar', DataUtil::formatForDisplay($pubtype)), $dom));
            }
            $pubtype = Util::getPubType($pubtype);
        }
        if (self::hasVar($pubtype, $name)) {
            Doctrine_Core::getTable('Matheo_Clip_Model_WorkflowVars')->createQuery()->update()->set('value', serialize($value))->where('setting = ?', $name)->andWhere('tid = ?', $pubtype->tid)->andWhere('workflow = ?', $pubtype->workflow)->execute();
        } else {
            $var = new WorkflowVarsModel();
            $var->fromArray(array('tid' => $pubtype->tid, 'workflow' => $pubtype->workflow, 'setting' => $name, 'value' => serialize($value)));
            $var->save();
        }
        self::$variables[$pubtype->tid][$name] = $value;
        return true;
    }
    
    /**
     * The setVars method sets multiple pubtype variables.
     *
     * @param mixed $pubtype The pubtype ID or instance.
     * @param array $vars    An associative array of varnames/varvalues.
     *
     * @return boolean True if successful, false otherwise.
     */
    public static function setVars($pubtype, array $vars)
    {
        $dom = ZLanguage::getModuleDomain('Clip');
        // validate the passed pubtype
        if (!$pubtype instanceof PubtypeModel) {
            if (!Util::validateTid($pubtype)) {
                return LogUtil::registerError(__f('%1$s: Invalid publication type ID passed [%2$s].', array('Workflow_Util::setVar', DataUtil::formatForDisplay($pubtype)), $dom));
            }
            $pubtype = Util::getPubType($pubtype);
        }
        // clean the old values
        $where = array(array('tid = ?', $pubtype->tid), array('workflow = ?', $pubtype->workflow));
        Doctrine_Core::getTable('Matheo_Clip_Model_WorkflowVars')->deleteWhere($where);
        // set the passed values
        $ok = true;
        foreach ($vars as $k => $v) {
            $ok = $ok && self::setVar($pubtype, $k, $v);
        }
        return $ok;
    }
    
    /**
     * Get workflow schema variables.
     *
     * @param string $schema Schema name.
     * @param string $name   Variable name.
     * @param string $field  Variable data field.
     *
     * @return mixed
     */
    public static function getSchemaVar(
        $schema,
        $name = '',
        $field = ''
    ) {
        // load up schema
        $schema = self::loadSchema('Clip', $schema);
        if (!$schema || !isset($schema['variables'])) {
            return false;
        }
        if (empty($name)) {
            return $schema['variables'];
        } elseif (!isset($schema['variables'][$name])) {
            return false;
        }
        if (!empty($field)) {
            return array_key_exists($field, $schema['variables'][$name]) ? $schema['variables'][$name][$field] : false;
        }
        return $schema['variables'][$name];
    }
    
    /**
     * Get workflow states.
     *
     * @param string $module Module name.
     * @param string $schema Schema name.
     *
     * @return mixed Array $action.id => $action or bool false.
     */
    public static function getStatesMap($module, $schema)
    {
        // load up schema
        $schema = self::loadSchema($module, $schema);
        if (!$schema || !isset($schema['states'])) {
            return false;
        }
        return $schema['states'];
    }
    
    /**
     * Get actions by state.
     *
     * @param string $module Module name.
     * @param string $schema Schema name.
     * @param string $state  State name, default = 'initial'.
     *
     * @return mixed Array $action.id => $action or bool false.
     */
    public static function getActionsMap(
        $module,
        $schema,
        $state = 'initial'
    ) {
        // load up schema
        $schema = self::loadSchema($module, $schema);
        if (!$schema || !isset($schema['actions'][$state])) {
            return false;
        }
        return $schema['actions'][$state];
    }
    
    /**
     * Delete workflows for module (used module uninstall time).
     *
     * @param string $module Module name.
     *
     * @return boolean
     */
    public static function deleteWorkflowsForModule($module)
    {
        if (!isset($module)) {
            $module = ModUtil::getName();
        }
        // this is a cheat to delete all items in table with value $module
        return (bool) DBUtil::deleteObjectByID('workflows', $module, 'module');
    }
    
    /**
     * Find the path of the file by searching overrides and the module location.
     *
     * @param string $file   Name of file to find (can include relative path).
     * @param string $module Module name.
     *
     * @return mixed string of path or bool false
     */
    public static function findPath($file, $module = null)
    {
        // if no module specified, default to calling module
        if (empty($module)) {
            $module = ModUtil::getName();
        }
        // Get module info
        $modinfo = ModUtil::getInfoFromName($module);
        if (!$modinfo) {
            return LogUtil::registerError(__f('%1$s: The specified module [%2$s] does not exist.', array('Workflow_Util::findPath', $module)));
        }
        $moduledir = $modinfo['directory'];
        // determine which folder to look in (system or modules)
        if ($modinfo['type'] == ModUtil::TYPE_SYSTEM) {
            // system module
            $modulepath = "system/{$moduledir}";
        } else {
            if ($modinfo['type'] == ModUtil::TYPE_MODULE) {
                // non system module
                $modulepath = "modules/{$moduledir}";
            } else {
                return LogUtil::registerError(__f('%s: Unsupported module type.', 'Workflow_Util'));
            }
        }
        // ensure module is active
        if (!$modinfo['state'] == ModUtil::STATE_ACTIVE) {
            return LogUtil::registerError(__f('%1$s: The module [%2$s] is not active.', array('Workflow_Util', $module)));
        }
        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName(UserUtil::getTheme()));
        $paths = array('themepath' => DataUtil::formatForOS("themes/{$themeinfo['directory']}/workflows/{$moduledir}/{$file}"), 'configpath' => DataUtil::formatForOS("config/workflows/{$moduledir}/{$file}"), 'modulepath' => DataUtil::formatForOS("{$modulepath}/workflows/{$file}"));
        // find the file in themes or config (for overrides), else module dir
        foreach ($paths as $path) {
            if (is_readable($path)) {
                return $path;
            }
        }
        return false;
    }
    
    /**
     * Check the permission to execute an action.
     *
     * @param object  $obj       Record object.
     * @param string  $module    Module name.
     * @param string  $schema    Schema name.
     * @param string  $permLevel Permission level.
     * @param integer $actionID  Action Id.
     *
     * @return boolean
     */
    public static function permissionCheck(
        $obj,
        $module,
        $schema,
        $permLevel = 'overview',
        $actionID = null
    ) {
        // get current user
        $currentUser = UserUtil::getVar('uid');
        // no user then assume anonnymous
        if (!$currentUser) {
            $currentUser = -1;
        }
        $function = "{$module}_workflow_{$schema}_permissioncheck";
        if (function_exists($function)) {
            // function already exists
            return $function($obj, $permLevel, $currentUser, $actionID);
        }
        // test operation file exists
        $file = "function.{$schema}_permissioncheck.php";
        $path = self::findPath($file, $module);
        if (!$path) {
            return LogUtil::registerError(__f('Workflow permission check file [%s] does not exist.', $file));
        }
        // load file and test if function exists
        include_once $path;
        if (!function_exists($function)) {
            return LogUtil::registerError(__f('Workflow permission check function [%s] is not defined.', $function));
        }
        // function must be loaded so now we can execute the function
        return $function($obj, $permLevel, $currentUser, $actionID);
    }
    
    /**
     * translates workflow permission to pn permission define
     *
     * @param string $permission Permission string.
     *
     * @return mixed Permission constant or false.
     */
    public static function translatePermission($permission)
    {
        switch (strtolower($permission)) {
            case 'invalid':
                return ACCESS_INVALID;
            case 'overview':
                return ACCESS_OVERVIEW;
            case 'read':
                return ACCESS_READ;
            case 'comment':
                return ACCESS_COMMENT;
            case 'moderate':
            case 'moderator':
                return ACCESS_MODERATE;
            case 'edit':
            case 'editor':
                return ACCESS_EDIT;
            case 'add':
            case 'author':
                return ACCESS_ADD;
            case 'delete':
                return ACCESS_DELETE;
            case 'admin':
                return ACCESS_ADMIN;
            default:
                return false;
        }
    }

}
