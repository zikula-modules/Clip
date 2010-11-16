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

class Clip_Filter_List extends FilterUtil_Filter_Category
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
    function getDql($field, $op, $value)
    {
        if (array_search($op, $this->availableOperators()) === false || array_search($field, $this->getFields()) === false) {
            return '';
        }

        $column = $this->getColumn($field);

        switch ($op)
        {
            case 'eq':
                $where =  "$column = '$value'";
                break;

            case 'ne':
                $where =  "$column != '$value'";
                break;

            case 'sub':
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
