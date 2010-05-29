<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id: $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula
 * @subpackage FilterUtil
*/

class FilterUtil_Filter_pmMultiList extends FilterUtil_Filter_category
{
    /**
     * return SQL code
     *
     * @access public
     * @param string $field Field name
     * @param string $op Operator
     * @param string $value Test value
     * @return string SQL code
     */
    function getSQL($field, $op, $value)
    {
        if (array_search($op, $this->availableOperators()) === false || array_search($field,$this->fields) === false) {
            return '';
        }

        Loader::loadClass('CategoryUtil');

        switch($op)
        {
            case 'eq':
                $where = $this->column[$field] . ' = ' . $value;
                break;

            case 'ne':
                $where = $this->column[$field] . ' != ' . $value;
                break;

            case 'sub':
                $where .= $this->column[$field]." LIKE '%:" . $value . ":%' OR ";
                $cats = CategoryUtil::getSubCategories($value);
                foreach ($cats as $item) {
                    $where .= $this->column[$field]." LIKE '%:" . $item['id'] . ":%' OR ";
                }
                $where = substr($where,0,-3);
                break;

            default:
                $where = '';
        }

        return array('where' => $where);
    }
}
