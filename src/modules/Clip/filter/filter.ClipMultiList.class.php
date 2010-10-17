<?php
/**
 * Zikula Application Framework
 *
 * @copyright  Zikula Foundation 2009 - Zikula Application Framework
 * @link       http://www.zikula.org
 * @version    $Id: $
 * @license    GNU/LGPLv3 - http://www.gnu.org/copyleft/gpl.html
 * @package    Zikula
 * @subpackage FilterUtil
*/

class FilterUtil_Filter_ClipMultiList extends FilterUtil_Filter_category
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
        if (array_search($op, $this->availableOperators()) === false || array_search($field, $this->getFields()) === false) {
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
