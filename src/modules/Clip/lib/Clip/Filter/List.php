<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Filter
 */

class Clip_Filter_List extends FilterUtil_Filter_Category
{
    /**
     * Adds fields to list in the clip way.
     *
     * @param mixed $fields Fields to add.
     *
     * @return void
     */
    public function addFields($fields)
    {
        if (is_array($fields)) {
            foreach ($fields as $fld) {
                $this->addFields($fld);
            }
        } elseif (!empty($fields) && $this->fieldExists($fields) && array_search($fields, (array)$this->fields) === false) {
            $this->fields[] = $fields;
        }
    }

    /**
     * Returns the operators the plugin can handle.
     *
     * @return array Operators.
     */
    public function availableOperators()
    {
        return array(
                     'eq',
                     'ne',
                     'sub',
                     'dis',
                     'null',
                     'notnull'
                    );
    }

    /**
     * Returns DQL code.
     *
     * @param string $field Field name.
     * @param string $op    Operator.
     * @param string $value Test value.
     *
     * @return array Doctrine Query where clause and parameters.
     */
    public function getDql($field, $op, $value)
    {
        if (array_search($op, $this->availableOperators()) === false || array_search($field, $this->getFields()) === false) {
            return '';
        }

        $where  = '';
        $params = array();
        $column = $this->getColumn($field);

        switch ($op)
        {
            case 'eq':
                $where = "$column = ?";
                $params[] = $value;
                break;

            case 'ne':
                $where = "$column <> ?";
                $params[] = $value;
                break;

            case 'sub':
            case 'dis':
                $cats = CategoryUtil::getSubCategories($value);
                $items = array($value);
                foreach ($cats as $item) {
                    $items[] = $item['id'];
                }
                if (count($items) == 1) {
                    $opr   = $op == 'sub' ? '=' : '!=';
                    $where = "$column $opr ?";
                    $params[] = $value;
                } else {
                    $opr   = $op == 'sub' ? 'IN' : 'NOT IN';
                    $where = "$column $opr (".implode(',', $items).")";
                }
                break;

            case 'null':
                $where = "($column = '' OR $column IS NULL)";
                break;

            case 'notnull':
                $where = "($column <> '' OR $column IS NOT NULL)";
                break;
        }

        return array('where' => $where, 'params' => $params);
    }
}
