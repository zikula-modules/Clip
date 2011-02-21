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

class Clip_Filter_User extends FilterUtil_Filter_Default
{
    /**
     * Constructor.
     *
     * Argument $config may contain
     *  fields: Set of fields to use, see setFields().
     *  ops:    Operators to enable, see activateOperators().
     *
     * @param array $config Configuration.
     */
    public function __construct($config)
    {
        parent::__construct($config);

        if (isset($config['fields']) && is_array($config['fields'])) {
            $this->addFields($config['fields']);
        }

        if (isset($config['ops']) && (!isset($this->ops) || !is_array($this->ops))) {
            $this->activateOperators($config['ops']);
        } else {
            $this->activateOperators($this->availableOperators());
        }
    }

    /**
     * Returns the operators the plugin can handle.
     *
     * @return array Operators.
     */
    public function availableOperators()
    {
        return array('user');
    }

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
     * Returns DQL code.
     *
     * @param string $field Field name.
     * @param string $op    Operator.
     * @param string $value Test value.
     *
     * @return array Doctrine Query where clause and parameters.
     */
    function getDql($field, $op, $value)
    {
        if (array_search($op, $this->availableOperators()) === false || array_search($field, $this->getFields()) === false) {
            return '';
        }

        $where  = '';
        $params = array();
        $column = $this->getColumn($field);

        switch ($op)
        {
            case 'user':
                $where = "$column = ?";
                $params[] = UserUtil::getVar('uid');
                break;
        }

        return array('where' => $where, 'params' => $params);
    }
}
