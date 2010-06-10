<?php
/**
 * Zikula Application Framework
 *
 * @copyright  (c) Zikula Development Team
 * @link       http://www.zikula.org
 * @version    $Id: FilterUtil.class.php 25078 2008-12-17 08:39:04Z Guite $
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @author     Philipp Niethammer <philipp@zikula.org>
 * @category   Zikula_Core
 * @package    Object_Library
 * @subpackage FilterUtil
 */

/**
 * Define Class path
 */
define('FILTERUTIL_CLASS_PATH', 'modules/PageMaster/classes/FilterUtil');

Loader::loadClass('FilterUtil_Plugin', FILTERUTIL_CLASS_PATH);
Loader::loadClass('FilterUtil_PluginCommon', FILTERUTIL_CLASS_PATH);
Loader::loadClass('FilterUtil_Common', FILTERUTIL_CLASS_PATH);

/**
 * The FilterUtil class adds a Pagesetter-like filter feature to Zikula
 *
 * @category   Zikula_Core
 * @package    Object_Library
 * @subpackage FilterUtil
 * @author     Philipp Niethammer <philipp@zikula.org>
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @link       http://www.zikula.org 
 */
class FilterUtil extends FilterUtil_Common
{

    /**
     * The Input variable name
     */
    private $varname;

    /**
     * Plugin object
     */
    private $plugin;

    /**
     * Filter object holder
     */
    private $obj;

    /**
     * Filter string holder
     */
    private $filter;

    /**
     * Filter SQL holder
     */
    private $sql;

    /**
     * Constructor
     * 
     * @param string $module Module name
     * @param string $table    Table name
     * @param array $args Mixed arguments
     * @access public
     */
    public function __construct($module, $table, $args = array())
    {
        $this->setVarName('filter');
        $args['module'] = $module;
        $args['table'] = $table;
        parent::__construct($args);

        $this->addCommon($args);
        $this->plugin = new FilterUtil_Plugin($args, array('default' => array()));

        if (isset($args['plugins'])) {
            $this->plugin->loadPlugins($args['plugins']);
        } if (isset($args['varname'])) {
            $this->setVarName($args['varname']);
        }

        return $this;
    }

    /**
     * Set name of input variable of filter
     *
     * @access public
     * @param string $name name of input variable
     * @return bool true on success, false otherwise
     */
    public function setVarName($name)
    {
        if (!is_string($name))
            return false;

        $this->varname = $name;
    }

//++++++++++++++++ Object handling +++++++++++++++++++

    /**
     * strip brackets around a filterstring
     *
     * @access private
     * @param string $filter Filterstring
     * @return string edited filterstring
     */
    private function stripBrackets($filter)
    {
        if (substr($filter, 0, 1) == '(' && substr($filter, -1) == ')') {
            return substr($filter, 1, -1);
        }

        return $filter;
    }

    /**
     * Create a condition object out of a string
     *
     * @access private
     * @param string $filter Condition string
     * @return array condition object
     */
    private function makeCondition($filter)
    {
        if (strpos($filter, ':'))
            $parts = explode(':', $filter, 3);
        elseif (strpos($filter, '^'))
            $parts = explode('^', $filter, 3);

        if (isset($parts[2]) && substr($parts[2], 0, 1) == '$' && ($value = FormUtil::getPassedValue(substr($parts[2], 1), null)) != null && !empty($value)) {
            $obj['value'] = $value;
        } else {
            $obj['value'] = $parts[2];
        }

        $obj['field'] = $parts[0];
        $obj['op'] = $parts[1];

        $obj = $this->plugin->replace($obj['field'], $obj['op'], $obj['value']);

        return $obj;
    }

    /**
     * Help function to generate an object out of a string
     *
     * @access private
     * @param string $filter    Filterstring
     */
    private function GenObjectRecursive($filter)
    {
        $obj = array();
        $regex = '~^([,\*])?(\(.*\)|[^\(\),\*]+)(?:[,\*](?:\(.*\)|[^\(\),\*]+))*$~U';
        $cycle = 0;
        while (!empty($filter)) {
            preg_match($regex, $filter, $match);
            $op = str_replace(array(',', '*'),array('and', 'or'), $match['1']);
            if (!$op) $op = 0;
            else $op .= $cycle++;
            $string = $this->stripBrackets($match[2]);
            if (strpos($string, ',') || strpos($string, '*')) {
                $sub = $this->GenObjectRecursive($string);
                if (!empty($sub) && is_array($sub)) {
                    $obj[$op] = $sub;
                }
            } elseif (($cond = $this->makeCondition($string)) !== false) {
                    $obj[$op] = $cond;
            }
            $filter = substr($filter, strlen($match[2])+strlen($match[1]));
        }

        return $obj;
    }

    /**
     * Generate the filter object from a string
     *
     * @access public
     */
    public function GenObject()
    {
        $this->obj = $this->GenObjectRecursive($this->getFilter());
    }

    /**
     * Get the filter object
     *
     * @access public
     * @return array filter object
     */
    public function GetObject()
    {
        if (!isset($this->obj) || !is_array($this->obj)) {
            $this->GenObject();
        }
        return $this->obj;
    }

//---------------- Object handling ---------------------
//++++++++++++++++ Filter handling +++++++++++++++++++++
    /**
     * Get all filters from Input
     *
     * @return array Array of filters
     */
    public function GetFiltersFromInput ()
    {
        $i = 1;
          $filter = array();

        // Get unnumbered filter string
        $filterStr = FormUtil::getPassedValue($this->varname, '');
        if (!empty($filterStr))
        {
            $filter[] = $filterStr;
        }

        // Get filter1 ... filterN
        while (true)
        {
            $filterURLName = $this->varname . "$i";
            $filterStr     = FormUtil::getPassedValue($filterURLName, '');

            if (empty($filterStr))
                break;

            $filter[] = $filterStr;
            ++$i;
        }

        return $filter;
    }


    /**
     * Get filterstring
     *
     * @access public
     * @return string $filter Filterstring
     */
    public function getFilter()
    {
        if (!isset($this->filter) || empty($this->filter)) {
            $filter = $this->GetFiltersFromInput();
            if (is_array($filter) && count($filter) > 0) {
                $this->filter = "(".implode(')*(', $filter).")";
            }
        }

        if ($this->filter == '()')
            $this->filter = '';

        return $this->filter;
    }

    /**
     * Set filterstring
     *
     * @access public
     * @param mixed $filter Filter string or array
     */
    public function setFilter($filter)
    {
        if (is_array($filter)) {
            $this->filter = "(".implode(')*(', $filter).")";
        } else {
            $this->filter = $filter;
        }
        $this->obj = false;
        $this->sql = false;

    }

//--------------- Filter handling ----------------------
//+++++++++++++++ SQL Handling +++++++++++++++++++++++++

    /**
     * Help function for generate the filter SQL from a Filter-object
     *
     * @access private
     * @param array $obj Object array
     * @return array Where and Join sql
     */
    private function GenSqlRecursive($obj)
    {
        if (!is_array($obj) || count($obj) == 0) {
            return '';
        }
        
        if (isset($obj['field']) && !empty($obj['field'])) {
            $obj['value'] = DataUtil::formatForStore($obj['value']);
            $res = $this->plugin->getSQL($obj['field'], $obj['op'], $obj['value']);
            $res['join'] =& $this->join;
            return $res;
        } else {
            if (isset($obj[0]) && is_array($obj[0])) {
                $sub = $this->GenSqlRecursive($obj[0]);
                if (!empty($sub['where'])) {
                    $where .= $sub['where'];
                }
                unset($obj[0]);
            }
            foreach ($obj as $op => $tmp) {
                $op = strtoupper(substr($op, 0, 3)) == 'AND' ? 'AND' : 'OR';
                if (strtoupper($op) == 'AND' || strtoupper($op) == 'OR') {
                    $sub = $this->GenSqlRecursive($tmp);
                    if (!empty($sub['where']))
                        $where .= ' ' . strtoupper($op) . ' ' . $sub['where'];
                }
            }
        }
        return array('where' => (empty($where) ? '' : "(\n $where \n)"),
                    'join' => &$this->join);
    }

    /**
     * Generate where/join SQL
     *
     * access public
     */
    public function GenSql()
    {
        $object = $this->GetObject();
        $this->sql = $this->GenSqlRecursive($object);
    }

    /**
     * Get where/join SQL
     *
     * @access public
     * @return array Array with where and join
     */
    public function GetSQL()
    {
        if (!isset($this->sql) || !is_array($this->sql)) {
            $this->GenSQL();
        }
        return $this->sql;
    }
}
