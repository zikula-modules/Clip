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

class Clip_Filter_MultiList extends Clip_Filter_List
{
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
                     'dis'
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
                $opr   = $op == 'sub' ? 'LIKE' : 'NOT LIKE';
                $where = "$column $opr ?";
                $params[] = '%:'.$value.':%';
                $cats = CategoryUtil::getSubCategories($value);
                foreach ($cats as $item) {
                    $where .= ($op == 'sub' ? ' OR' : ' AND')." $column $opr ?";
                    $params[] = '%:'.$item['id'].':%';
                }
                break;
        }

        return array('where' => $where, 'params' => $params);
    }
}
