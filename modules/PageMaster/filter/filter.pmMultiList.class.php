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

if (version_compare(PN_VERSION_NUM, '1.3', '<')) {
    Loader::loadClass('filter.category', FILTERUTIL_CLASS_PATH.'/filter');
}

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

        $column = $this->column[$field];

        switch($op)
        {
            case 'eq':
                $where =  "$column = '$value'";
                break;

            case 'ne':
                $where =  "$column != '$value'";
                break;

            case 'sub':
                if (version_compare(PN_VERSION_NUM, '1.3', '<')) {
                    Loader::loadClass('CategoryUtil');
                }
                $where = "$column LIKE '%:$value:%'";
                $cats = CategoryUtil::getSubCategories($value);
                foreach ($cats as $item) {
                    $where .= " OR $column LIKE '%:$item[id]:%'";
                }
                break;
        }

        return array('where' => $where);
    }
}
