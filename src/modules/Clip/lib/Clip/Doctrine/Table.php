<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Doctrine
 */

/**
 * Clip extension of Doctrine_Table with utility methods.
 */
class Clip_Doctrine_Table extends Doctrine_Table
{
    /**
     * Get Filter Columns.
     *
     * @param array $dynaMap Array of dynamic aliasses.
     *
     * @return array Indexed array with aliases => columns.
     */
    public function getFilterColumns($dynaMap = array())
    {
        $fields  = $this->getFieldNames();
        $columns = array_combine($fields, $fields);

        foreach ($this->getRelations() as $alias => $relation) {
            if (strpos($alias, 'ClipModels_Relation') === 0) {
                continue;
            }

            $prefix = isset($dynaMap[$alias]) ? $dynaMap[$alias].'.' : $alias.':';

            // checks if this relation is not owned
            if (preg_match('/^rel\_\d+$/', $relation['local'])) {
                $columns[$alias] = $relation['local'];
            } else {
                // when owned the foreign field is in the other table
                $columns[$alias] = "{$prefix}{$relation['local']}";
            }

            foreach ($relation->getTable()->getFieldNames() as $field) {
                $columns["$alias.$field"] = "{$prefix}{$field}";
            }
        }

        // add the core_title as another filter field
        $tid = Clip_Util::getTidFromString($this->getTableName());
        $columns['core_title'] = Clip_Util::getPubType($tid)->getTitleField();

        return $columns;
    }

    /**
     * Getter of the internal tablename.
     *
     * @return string Internal table name.
     */
    public function getInternalTableName()
    {
        $format = $this->_conn->getAttribute(Doctrine_Core::ATTR_TBLNAME_FORMAT);
        $format = str_replace('%s', '', $format);

        $tableName = $this->getTableName();
        return str_replace($format, '', $tableName);
    }

    /**
     * Select and return a field value.
     *
     * @param string $field   The name of the field we wish to marshall.
     * @param arry   $where   The where clause (optional) (default=array()).
     * @param string $orderBy The orderby clause (optional) (default='').
     *
     * @return string The resulting field value.
     */
    public function selectField($field, $where = array(), $orderBy = '')
    {
        // creates the query instance
        $q = $this->selectFieldQuery($field, $where, $orderBy);

        return $q->fetchOne(array(), Doctrine_Core::HYDRATE_SINGLE_SCALAR);
    }

    /**
     * Select and return a field by a column value.
     *
     * @param string  $field  The field we wish to select.
     * @param integer $value  The value we wish to select with.
     * @param string  $column The column to use (optional) (default='id').
     * @param string  $orderBy  The orderby clause (optional) (default='').
     *
     * @return mixed The resulting field value.
     */
    public function selectFieldBy($field, $value, $column = 'id', $orderBy = '')
    {
        // creates the query instance
        $q = $this->selectFieldQuery($field, array(), $orderBy);

        $q->where($this->buildFindByWhere($column), (array)$value);

        return $q->fetchOne(array(), Doctrine_Core::HYDRATE_SINGLE_SCALAR);
    }

    /**
     * Select and return an array of field values.
     *
     * @param string  $field    The name of the field we wish to marshall.
     * @param array   $where    The where clause (optional) (default=array()).
     * @param string  $orderBy  The orderby clause (optional) (default='').
     * @param boolean $distinct Whether or not to add a 'DISTINCT' clause (optional) (works without a assocKey) (default=false).
     * @param string  $assocKey The key field to use to build the associative index (optional) (default='').
     *
     * @return array The resulting array of field values.
     */
    public function selectFieldArray($field, $where = array(), $orderBy = '', $distinct = false, $assocKey = '')
    {
        if (!empty($assocKey) && !$this->hasField($assocKey)) {
            $assocKey = '';
        }

        // creates the query instance
        $q = $this->selectFieldQuery($field, $where, $orderBy, $distinct, $assocKey);

        if (!$assocKey) {
            $result = $q->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
            foreach ($result as $k => $v) {
                $result[$k] = $v[$field];
            }
        } else {
            $result = $q->execute()->toKeyValueArray($assocKey, $field);
        }

        return $result;
    }

    /**
     * Select and return an array of field values by a column value.
     *
     * @param string $field   The field we wish to select.
     * @param string $value   The value we wish to select with.
     * @param string $column  The column to use (optional) (default='id').
     * @param string $orderBy The orderby clause (optional) (default='').
     *
     * @return array The resulting field array.
     */
    public function selectFieldArrayBy($field, $value, $column = 'id', $orderBy = '')
    {
        // creates the query instance
        $q = $this->selectFieldQuery($field, '', $orderBy);

        $q->where($this->buildFindByWhere($column), (array)$value);

        $result = $q->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
        foreach ($result as $k => $v) {
            $result[$k] = $v[$field];
        }

        return $result;
    }

    /**
     * Field Query creation.
     *
     * @param string  $field    The name of the field we wish to marshall.
     * @param array   $where    The where clause (optional) (default=array()).
     * @param string  $orderBy  The orderby clause (optional) (default='').
     * @param boolean $distinct Whether or not to add a 'DISTINCT' clause (optional) (default=false).
     * @param string  $assocKey The key field to use to build the associative index (optional) (default='').
     *
     * @return Doctrine_Query The resulting query object.
     */
    public function selectFieldQuery($field, $where = array(), $orderBy = '', $distinct = false, $assocKey = '')
    {
        $queryAlias = 'dctrn_find';

        // validate the associative index
        if (!empty($assocKey) && $this->hasField($assocKey)) {
            $queryAlias .= ($assocKey ? " INDEXBY $assocKey" : '');
        } else {
            $assocKey = '';
        }

        // adds the distinct clause id needed
        $field = ($distinct ? "DISTINCT $field as $field" : "$field");

        // creates the query instance
        $q = $this->createQuery($queryAlias)
                  ->select($field);

        // adds the assockey if needed
        if (!empty($assocKey)) {
            $q->addSelect($assocKey);
        }

        // adds the where clause if present
        if (!empty($where)) {
            $i = 0;
            foreach ((array)$where as $method => $condition) {
                if (is_numeric($method)) {
                    $method = ($i == 0) ? 'where' : 'andWhere';
                }
                if (is_array($condition)) {
                    $q->$method($condition[0], $condition[1]);
                } else {
                    $q->$method($condition);
                }
                $i++;
            }
        }

        // adds the orderby if present
        if (!empty($orderBy)) {
            $q->orderBy($orderBy);
        }

        return $q;
    }

    /**
     * Select and return the max/min/sum/count value of a field.
     *
     * @param string $field  The name of the field we wish to marshall.
     * @param string $option MIN, MAX, SUM or COUNT (optional) (default='MAX').
     * @param string $where  The where clause (optional) (default=array()).
     *
     * @return mixed The resulting min/max/sum/count value.
     */
    public function selectFieldFunction($field, $option = 'MAX', $where = array())
    {
        // creates the query instance
        $q = $this->selectFieldFunctionQuery($field, $option, $where);

        return $q->fetchOne(array(), Doctrine_Core::HYDRATE_SINGLE_SCALAR);
    }

    /**
     * Select and return the max/min/sum/count array values of a field grouped by the associated key.
     *
     * @param string $field    The name of the field we wish to marshall.
     * @param string $option   MIN, MAX, SUM or COUNT (optional) (default='MAX').
     * @param array  $where    The where clause (optional) (default=array()).
     * @param string $assocKey The key field to use to build the associative index (optional) (default='' which defaults to the primary key).
     *
     * @return array The resulting min/max/sum/count array.
     */
    public function selectFieldFunctionArray($field, $option = 'MAX', $where = array(), $assocKey = '')
    {
        // validate the associatibe index
        if (empty($assocKey) || !$this->hasField($assocKey)) {
            $assocKey = $this->getIdentifier();
        }

        // validate the option
        $option = strtoupper($option);
        if (!in_array($option, array('MIN', 'MAX', 'SUM', 'COUNT'))) {
            $option = 'MAX';
        }

        // creates the query instance
        $q = $this->selectFieldFunctionQuery($field, $option, $where, $assocKey);

        return $q->execute()->toKeyValueArray($assocKey, $option);
    }

    /**
     * Field Function Query creation.
     *
     * @param string  $field    The name of the field we wish to marshall.
     * @param string  $option   MIN, MAX, SUM or COUNT (optional) (default='MAX').
     * @param array   $where    The where clause (optional) (default=array()).
     * @param string  $assocKey The key field to use to build the associative index (optional) (default='' which defaults to the primary key).
     * @param boolean $distinct Whether or not to count distinct entries (optional) (default='false').
     *
     * @return Doctrine_Query The resulting query object.
     */
    public function selectFieldFunctionQuery($field = '1', $option = 'COUNT', $where = array(), $assocKey = '', $distinct = false)
    {
        $queryAlias = 'dctrn_find';

        // validate the associative index
        if (!empty($assocKey) && $this->hasField($assocKey)) {
            $queryAlias .= ($assocKey ? " INDEXBY $assocKey" : '');
        } else {
            $assocKey = '';
        }

        // validate the option
        $ucOption = strtoupper($option);
        if (!in_array($ucOption, array('MIN', 'MAX', 'SUM', 'COUNT'))) {
            $ucOption = 'COUNT';
        }

        $hasField = $this->hasField($field);
        $distinct = ($hasField && $ucOption == 'COUNT' && $distinct) ? 'DISTINCT ' : '';
        if (!$hasField) {
            if ($ucOption == 'COUNT') {
                $field = '1';
            } else {
                $field = $this->getIdentifier();
            }
        }

        $q = $this->createQuery($queryAlias)
                  ->select("$ucOption({$distinct}{$field}) AS $option");

        // adds the assockey if needed
        if (!empty($assocKey)) {
            $q->addSelect($assocKey);
            $q->addGroupBy($assocKey);
        }

        // adds the where clause if present
        if (!empty($where)) {
            $i = 0;
            foreach ((array)$where as $method => $condition) {
                if (is_numeric($method)) {
                    $method = ($i == 0) ? 'where' : 'andWhere';
                }
                if (is_array($condition)) {
                    $q->$method($condition[0], $condition[1]);
                } else {
                    $q->$method($condition);
                }
                $i++;
            }
        }

        return $q;
    }

    /**
     * Return a number of rows.
     *
     * @param array   $where    The where clause (optional) (default=array()).
     * @param string  $column   The column to place in the count phrase (optional) (default='1').
     * @param boolean $distinct Whether or not to count distinct entries (optional) (default='false').
     *
     * @return integer The resulting object count.
     */
    public function selectCount($where = array(), $column = '1', $distinct = false)
    {
        // creates the query instance
        $q = $this->selectFieldFunctionQuery($column, 'COUNT', $where, '', $distinct);

        return $q->fetchOne(array(), Doctrine_Core::HYDRATE_SINGLE_SCALAR);
    }

    /**
     * Select an object count by ID.
     *
     * @param integer $value         The value to match.
     * @param string  $column        The column to match the value against (optional) (default='id').
     * @param string  $transformFunc Transformation function to apply to $id (optional) (default=null).
     *
     * @return integer The resulting object count.
     * @throws Exception If id paramerter is empty.
     */
    public function selectCountBy($value, $column = 'id', $transformFunc = '')
    {
        if (!$value) {
            throw new Exception(__f('The parameter %s must not be empty', 'value'));
        }

        // creates the query instance
        $q = $this->selectFieldFunctionQuery();

        if ($transformFunc) {
            $q->where($transformFunc.'(dctrn_find.'.$column.') = ?', (array)$value);
        } else {
            $q->where($this->buildFindByWhere($column), (array)$value);
        }

        return $q->fetchOne(array(), Doctrine_Core::HYDRATE_SINGLE_SCALAR);
    }

    /**
     * Select and return a collection.
     *
     * @param array   $where        The where clause (optional) (default=array()).
     * @param string  $orderBy      The order by clause (optional) (default='').
     * @param integer $limitOffset  The lower limit bound (optional) (default=-1).
     * @param integer $limitNumRows The upper limit bound (optional) (default=-1).
     * @param string  $assocKey     The key field to use to build the associative index (optional) (default='').
     *
     * @return Doctrine_Collection The resulting collection.
     */
    public function selectCollection($where = array(), $orderBy = '', $limitOffset = -1, $limitNumRows = -1, $assocKey = '')
    {
        // creates the query instance
        $q = $this->selectQuery($where, $orderBy, $limitOffset, $limitNumRows, $assocKey);

        return $q->execute();
    }

    /**
     * Select Query creation.
     *
     * @param array   $where        The where clause (optional) (default=array()).
     * @param string  $orderBy      The order by clause (optional) (default='').
     * @param integer $limitOffset  The lower limit bound (optional) (default=-1).
     * @param integer $limitNumRows The upper limit bound (optional) (default=-1).
     * @param string  $assocKey     The key field to use to build the associative index (optional) (default='').
     *
     * @return Doctrine_Query The resulting query object.
     */
    public function selectQuery($where = array(), $orderBy = '', $limitOffset = -1, $limitNumRows = -1, $assocKey = '')
    {
        $queryAlias = 'dctrn_find';

        // validate the associative index
        if (!empty($assocKey) && $this->hasField($assocKey)) {
            $queryAlias .= ($assocKey ? " INDEXBY $assocKey" : '');
        } else {
            $assocKey = '';
        }

        // creates the query instance
        $q = $this->createQuery($queryAlias);

        // adds the where clause if present
        if (!empty($where)) {
            $i = 0;
            foreach ((array)$where as $method => $condition) {
                if (is_numeric($method)) {
                    $method = ($i == 0) ? 'where' : 'andWhere';
                }
                if (is_array($condition)) {
                    $q->$method($condition[0], $condition[1]);
                } else {
                    $q->$method($condition);
                }
                $i++;
            }
        }

        // adds the orderby if present
        if (!empty($orderBy)) {
            $q->orderBy($orderBy);
        }

        // adds the offset if present
        if ($limitOffset > 0) {
            $q->offset($limitOffset);
        }

        // adds the limit if present
        if ($limitNumRows > 0) {
            $q->limit($limitNumRows);
        }

        return $q;
    }

    /**
     * Delete a collection of objects.
     *
     * @param array  $keyarray The KeyArray to delete.
     * @param string $idfield  The field to use.
     *
     * @return mixed The result from the delete operation.
     */
    public function deleteObjectsFromKeyArray(array $keyarray, $idfield = 'id')
    {
        // creates a query instance
        $q = $this->createQuery('dctrn_del');

        return $q->delete()
                 ->whereIn($idfield, array_keys($keyarray))
                 ->execute();
    }

    /**
     * Execute a record deletion by its ID.
     *
     * @param integer $id      The ID of the object to delete.
     * @param string  $idfield The column which contains the ID field (optional) (default='id').
     *
     * @return mixed The result from the delete operation.
     */
    public function deleteByID($id, $idfield = 'id')
    {
        $where = array(array("$idfield = ?", $id));

        return $this->deleteWhere($where);
    }

    /**
     * Executes a delete query.
     *
     * @param array $where The where clause to use (optional) (default=array()).
     *
     * @return mixed The result from the delete operation.
     */
    public function deleteWhere($where = array())
    {
        // creates a query instance
        $q = $this->createQuery('dctrn_del');

        // adds the where clause if present
        if (!empty($where)) {
            $method = '';
            foreach ((array)$where as $condition) {
                $method = empty($method) ? 'where' : 'andWhere';
                if (is_array($condition)) {
                    $q->$method($condition[0], $condition[1]);
                } else {
                    $q->$method($condition);
                }
            }
        }

        return $q->delete()
                 ->execute();
    }

    /**
     * Increment a field by the given increment.
     *
     * @param string  $incfield The column which stores the field to increment.
     * @param integer $value    The value of the object holding the field we wish to increment.
     * @param string  $column   The column to match the value against (optional) (default='id').
     * @param integer $inccount The amount by which to increment the field (optional) (default=1).
     *
     * @return integer The result from the increment operation.
     */
    public function incrementFieldBy($incfield, $value, $column = 'id', $inccount = 1)
    {
        $sign = ($inccount > 0) ? '+' : '-';

        return $this->createQuery('dctrn_find')
                    ->update()
                    ->set("$incfield", "$incfield $sign $inccount")
                    ->where("$column = ?", array($value))
                    ->execute();
    }

    /**
     * Decrement a field by the given decrement.
     *
     * @param string  $decfield The column which stores the field to decrement.
     * @param integer $value    The value of the object holding the field we wish to increment.
     * @param string  $column   The column to match the value against (optional) (default='id').
     * @param integer $deccount The amount by which to decrement the field (optional) (default=1).
     *
     * @return integer The result from the decrement operation.
     */
    public function decrementFieldBy($decfield, $value, $column = 'id', $deccount = 1)
    {
        return $this->incrementFieldBy($decfield, $value, $column, 0 - $deccount);
    }

    /**
     * Create table.
     *
     * @return boolean True on success, false otherwise.
     */
    public function createTable()
    {
        $data = $this->getExportableFormat(false);

        try {
            Doctrine_Manager::connection()->export->createTable($data['tableName'], $data['columns'], $data['options']);
        } catch (Exception $e) {
            // omit the already exists error
            $msg = $e->getMessage();
            if (get_class($e) == 'Doctrine_Connection_Mysql_Exception' && strpos($msg, 'SQLSTATE[42S01]') !== 0) {
                return LogUtil::registerError($msg);
            }
        }

        return true;
    }

    /**
     * Drop table.
     *
     * @return boolean True on success, false otherwise.
     */
    public function dropTable()
    {
        $tableName = $this->getOption('tableName');

        try {
            Doctrine_Manager::connection()->export->dropTable($tableName);
        } catch (Exception $e) {
            return LogUtil::registerError($e->getMessage());
        }

        return true;
    }

    /**
     * Change database table using Doctrine dictionary method.
     *
     * @return boolean True on success, false otherwise.
     */
    public function changeTable($dropColumns=false)
    {
        $className = get_class($this->getRecordInstance());

        return DoctrineUtil::changeTable($className, $dropColumns);
    }
}
