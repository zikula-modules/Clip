<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2008, Zikula Development Team
 * @link http://www.zikula.org
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_Generated_Modules
 * @subpackage Pagemaster
 * @author Axel Guckelsberger
 * @url http://modulestudio.de
 */

/*
 * generated at Sun Aug 03 14:43:13 CEST 2008 by ModuleStudio 0.4.10 (http://modulestudio.de)
 */


Loader::loadClass('PagemasterFilterUtil_Common', Pagemaster_FILTERUTIL_CLASS_PATH);

class PagemasterFilterUtil_Plugin extends PagemasterFilterUtil_Common
{
    /**
     * Loaded plugins
     */
    private $plg;

    /**
     * Loaded operators
     */
    private $ops;

    /**
     * Loaded replaces
     */
    private $replaces;


    /**
     * Constructor
     *
     * @access public
     * @param array $config Configuration array
     * @param array $plgs Plugins to load in form "plugin name => config array"
     * @return object PagemasterFilterUtil_Plugin object (optional) (default: null)
     */
    public function __construct($config = array(), $plgs = null)
    {
        parent::__construct($config);
        if ($plgs !== null && is_array($plgs) && count($plgs) > 0) {
             $ok = $this->loadPlugins($plgs);
        }
        return ($ok === false?false:$this);
    }

    /**
     * Load plugins
     *
     * @access public
     * @param array $plgs Array of plugin informations in form "plugin's name => config array"
     * @return bool true on success, false otherwise
     */
    public function loadPlugins($plgs)
    {
        $error = false;
        foreach ($plgs as $k => $v) {
            $error = ($this->loadPlugin($k, $v)?$error:true);
        }

        return $error;
    }

    /**
     * Load a single plugin
     *
     * @access public
     * @param string $name Plugin's name
     * @param array $config Plugin's config
     * @return bool True on success, false otherwise
     */
    public function loadPlugin($name, $config = array())
    {
        if ($this->isLoaded($name)) {
            return true;
        }
        $class = 'PagemasterFilterUtil_Plugin_' . $name;

        Loader::loadClass($class, Pagemaster_FILTERUTIL_CLASS_PATH . '/plugins');
        $obj = new $class($this->addCommon($config));

        $this->plg[] = $obj;
        $obj =& end($this->plg);
        $obj->setID(key($this->plg));
        $this->registerPlugin(key($this->plg));

        return key(end($this->plg));
    }

    private function registerPlugin($k)
    {
        $obj =& $this->plg[$k];
        if (is_subclass_of($obj, 'PagemasterFilterUtil_OpCommon')) {
            $ops = $obj->getOperators();
            if (isset($ops) && is_array($ops)) {
                foreach ($ops as $op => $fields) {
                    $flds = array();
                    foreach ($fields as $field) {
                        $flds[$field] = $k;
                    }
                    if (isset($this->ops[$op]) && is_array($this->ops[$op])) {
                        $this->ops[$op] = array_merge($this->ops[$op], $flds);
                    } else {
                        $this->ops[$op] = $flds;
                    }
                }
            }
        }
        if (is_subclass_of($obj, 'PagemasterFilterUtil_ReplaceCommon')) {
            $this->replaces[] = $k;
        }
    }

    /**
     * Get plugin's configuration object
     *
     * @access public
     * @param string $name Plugin's name
     * @return object Plugin's configuration object
     */
    public function getConfig($name)
    {
        if (!$this->PluginIsLoaded($name)) {
            return false;
        }
        return $this->plg[$name]->GetConfig();
    }

    /**
     * Check if a plugin is loaded
     *
     * @access public
     * @param string $name Plugin's name
     * @return bool true if the plugin is loaded, false otherwise
     */
    public function isLoaded($name)
    {
        if (isset($this->plg[$name]) && is_a($this->plg[$name], 'PagemasterFilterUtil_Plugin_'.$name)) {
            return true;
        }
        return false;
    }

    /**
     * run replace plugins and return condition set
     *
     * @access public
     * @param string $field Fieldname
     * @param string $op Operator
     * @param string $value Value
     * @return array condition set
     */
    public function replace($field, $op, $value)
    {
        foreach ($this->replaces as $k) {
            $obj =& $this->plg[$k];
            list($field, $op, $value) = $obj->replace($field, $op, $value);
        }

        return array(    'field' => $field,
                        'op'    => $op,
                        'value'    => $value);
    }


    /**
     * return SQL code
     *
     * @access public
     * @param string $field Field name
     * @param string $op Operator
     * @param string $value Test value
     * @return array sql set
     */
    public function getSQL($field, $op, $value)
    {
        if (!isset($this->ops[$op]) || !is_array($this->ops[$op]) || !$this->fieldExists($field)) {
            return '';
        } elseif (isset($this->ops[$op][$field])) {
            return $this->plg[$this->ops[$op][$field]]->getSQL($field, $op, $value);
        } elseif (isset($this->ops[$op]['-'])) {
            return $this->plg[$this->ops[$op]['-']]->getSQL($field, $op, $value);
        } else {
            return '';
        }
    }
}
