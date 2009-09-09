<?php
/**
 * Zikula Application Framework
 *
 * @copyright  (c) Zikula Development Team
 * @link       http://www.zikula.org
 * @version    $Id: FilterUtil_Replace.class.php 25078 2008-12-17 08:39:04Z Guite $
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @author     Axel Guckelsberger <axel@zikula.org>
 * @category   Zikula_Core
 * @package    Object_Library
 * @subpackage FilterUtil
 */

/**
 * FilterUtil_Replace interface
 * 
 * @category   Zikula_Core
 * @package    Object_Library
 * @subpackage FilterUtil
 * @author     Philipp Niethammer <philipp@zikula.org>
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @link       http://www.zikula.org 
 */
interface FilterUtil_Replace {
    
    /**
     * Replace whatever the plugin has to replace
     *
     * @param string $field Field name
     * @param string $op Operator
     * @param string $value Value
     * @return array ($field, $op, $value)
     */
    public function Replace($field, $op, $value);
}
