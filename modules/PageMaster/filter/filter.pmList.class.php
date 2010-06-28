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

class FilterUtil_Filter_pmList extends FilterUtil_Filter_category
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

        switch ($op)
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
                $cats = CategoryUtil::getSubCategories($value);
                $items = array($value);
                foreach ($cats as $item) {
                    $items[] = $item['id'];
                }
                if (count($items) == 1) {
                    $where = "$column = '$value'";
                } else {
                    $where = "$column IN (".implode(',', $items).")";
                }
                break;
        }

        return array('where' => $where);
    }
}
