<?php
/**
 * Zikula Application Framework
 *
 * @copyright  (c) Zikula Development Team
 * @link       http://www.zikula.org
 * @version    $Id: filter.replaceName.class.php 25078 2008-12-17 08:39:04Z Guite $
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @author     Axel Guckelsberger <axel@zikula.org>
 * @category   Zikula_Core
 * @package    Object_Library
 * @subpackage FilterUtil
 */

Loader::loadClass('FilterUtil_Replace', FILTERUTIL_CLASS_PATH);

/**
 * Replace plugin main class
 *
 * @category   Zikula_Core
 * @package    Object_Library
 * @subpackage FilterUtil
 * @author     Philipp Niethammer <philipp@zikula.org>
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @link       http://www.zikula.org 
 */
class FilterUtil_Filter_replaceName extends FilterUtil_PluginCommon implements FilterUtil_Replace
{
    protected $pair = array();

    /**
     * Constructor
     *
     * @access public
     * @param array $config Configuration
     * @return object FilterUtil_Plugin_Default
     */
    public function __construct($config)
    {
        parent::__construct($config);

        if (isset($config['pair']) && is_array($config['pair'])) {
            $this->addPair($config['pair']);
        }
    }
    
    /**
     * Add new replace pair (fieldname => replace with)
     * 
     * @param mixed $pair Replace Pair
     * @access public
     */
    public function addPair($pair)
    {
        foreach ($pair as $f => $r) {
            if (is_array($r)) {
                $this->addPair($r);
            } else {
                $this->pair[$f] = $r;
            }
        }
    }

    /**
     * Replace operator
     *
     * @access public
     * @param string $field Fieldname
     * @param string $op Operator
     * @param string $value Value
     * @return array array(field, op, value)
     */
     public function replace($field, $op, $value)
     {
         if (isset($this->pair[$field]) && !empty($this->pair[$field])) {
             $field = $this->pair[$field];
         }

         return array($field, $op, $value);
     }
}
