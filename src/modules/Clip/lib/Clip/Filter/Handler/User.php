<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://github.com/zikula-modules/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Filter
 */

class Clip_Filter_Handler_User extends FilterUtil_AbstractPlugin implements FilterUtil_BuildInterface
{
    /**
     * Enabled operators.
     *
     * @var array
     */
    protected $ops = array();

    /**
     * Fields to use the plugin for.
     *
     * @var array
     */
    protected $fields = array();

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
        return array('me', 'user', 'users', 'in', 'ins');
    }

    /**
     * Adds operators.
     *
     * @param mixed $op Operators to activate.
     *
     * @return void
     */
    public function activateOperators($op)
    {
        if (is_array($op)) {
            foreach ($op as $v) {
                $this->activateOperators($v);
            }
        } elseif (!empty($op) && array_search($op, $this->ops) === false && array_search($op, $this->availableOperators()) !== false) {
            $this->ops[] = $op;
        }
    }

    /**
     * Adds fields to list in common way.
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
        } elseif (!empty($fields) && $this->fieldExists($fields) && array_search($fields, $this->fields) === false) {
            $this->_fields[] = $fields;
        }
    }

    /**
     * Returns the fields.
     *
     * @return array List of fields.
     */
    public function getFields()
    {
        return $this->_fields;
    }

    /**
     * Get activated operators.
     *
     * @return array Set of Operators and Arrays.
     */
    public function getOperators()
    {
        $fields = $this->getFields();

        $ops = array();
        foreach ($this->ops as $op) {
            $ops[$op] = $fields;
        }

        return $ops;
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
            case 'me':
                $where = "$column LIKE ?";
                $params[] = '%:'.UserUtil::getVar('uid').':%';
                break;

            case 'user':
                $where = "$column = ?";
                $params[] = ':'.($value ? (int)$value : UserUtil::getVar('uid')).':';
                break;

            case 'users':
                $where = "$column LIKE ?";
                $params[] = '%:'.($value ? (int)$value : UserUtil::getVar('uid')).':%';
                break;

            case 'in':
                $where = array();
                foreach (explode('-', $value) as $uid) {
                    if ($uid) {
                        $where[]  = '?';
                        $params[] = ':'.(int)$uid.':';
                    }
                }
                $where = !empty($where) ? "$column IN (".implode(',', $where).")" : '';
                break;

            case 'ins':
                $where = array();
                foreach (explode('-', $value) as $uid) {
                    if ($uid) {
                        $where[]  = "$column LIKE ?";
                        $params[] = '%:'.(int)$uid.':%';
                    }
                }
                $where = implode(' OR ', $where);
                break;
        }

        return array('where' => $where, 'params' => $params);
    }
}
