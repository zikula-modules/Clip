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
 * Clip_Workflow_Util Class.
 */
class Clip_Workflow_Util
{
    static $workflows = array();

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
            return LogUtil::registerError(__f('%1$s: The specified module [%2$s] does not exist.', array('Clip_Workflow_Util::loadSchema', $module)));
        }

        $file = "$schema.xml";
        $path = self::findPath($file, $module);

        if (!$path) {
            return LogUtil::registerError(__f('%1$s: Unable to find the workflow file [%2$s].', array('Clip_Workflow_Util::loadSchema', $file)));
        }

        // instanciate Workflow Parser
        $parser = new Clip_Workflow_Parser();

        // parse workflow and return workflow object
        $workflowXML = file_get_contents($path);
        $data = $parser->parse($workflowXML, $schema, $module);

        // destroy parser and XML contents
        unset($parser);
        unset($workflowXML);

        // only take the info we need
        $data = array(
            'workflow' => $data['workflow'],
            'states'   => $data['states'],
            'actions'  => $data['actions']
        );

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
        $data['workflow']['title']       = isset($data['workflow']['title']) ? __($data['workflow']['title'], $dom) : $data['workflow']['id'];
        $data['workflow']['description'] = isset($data['workflow']['description']) ? __($data['workflow']['description'], $dom) : '';

        // states translation
        foreach ($data['states'] as $id => &$state) {
            $state['title']       = isset($state['title']) ? __($state['title'], $dom) : $id;
            $state['description'] = isset($state['description']) ? __($state['description'], $dom) : '';
        }

        // actions translation
        foreach ($data['actions'] as $stateid => &$actions) {
            foreach ($actions as $id => &$action) {
                $action['title']       = isset($action['title']) ? __($action['title'], $dom) : $id;
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

        // cache workflow
        self::$workflows[$module][$schema] = $data;

        // return workflow object
        return self::$workflows[$module][$schema];
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
    public static function getActionsMap($module, $schema, $state = 'initial')
    {
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
        return (bool)DBUtil::deleteObjectByID('workflows', $module, 'module');
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
            return LogUtil::registerError(__f('%1$s: The specified module [%2$s] does not exist.', array('Clip_Workflow_Util::findPath', $module)));
        }

        $moduledir = $modinfo['directory'];

        // determine which folder to look in (system or modules)
        if ($modinfo['type'] == ModUtil::TYPE_SYSTEM) {
            // system module
            $modulepath = "system/$moduledir";

        } else if ($modinfo['type'] == ModUtil::TYPE_MODULE) {
            // non system module
            $modulepath = "modules/$moduledir";

        } else {
            return LogUtil::registerError(__f('%s: Unsupported module type.', 'Clip_Workflow_Util'));
        }

        // ensure module is active
        if (!$modinfo['state'] == ModUtil::STATE_ACTIVE) {
            return LogUtil::registerError(__f('%1$s: The module [%2$s] is not active.', array('Clip_Workflow_Util', $module)));
        }

        $themedir = ThemeUtil::getInfo(ThemeUtil::getIDFromName(UserUtil::getTheme()));

        $paths = array(
            'themepath'  => DataUtil::formatForOS("themes/$themedir/workflows/$moduledir/$file"),
            'configpath' => DataUtil::formatForOS("config/workflows/$moduledir/$file"),
            'modulepath' => DataUtil::formatForOS("$modulepath/workflows/$file")
        );

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
    public static function permissionCheck($obj, $module, $schema, $permLevel = 'overview', $actionID = null)
    {
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
            return LogUtil::registerError(__f("Workflow permission check file [%s] does not exist.", $file));
        }

        // load file and test if function exists
        include_once $path;

        if (!function_exists($function)) {
            return LogUtil::registerError(__f("Workflow permission check function [%s] is not defined.", $function));
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
        switch (strtolower($permission))
        {
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
