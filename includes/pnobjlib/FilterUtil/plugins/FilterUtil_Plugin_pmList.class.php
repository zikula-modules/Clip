<?php


Loader::loadClass('FilterUtil_OpCommon', FILTERUTIL_CLASS_PATH);

class FilterUtil_Plugin_pmList extends FilterUtil_OpCommon
{
	/**
	 * Constructor
	 *
	 * @access public
	 * @param array $config Configuration
	 * @return object FilterUtil_Plugin_pgList
	 */
	public function __construct($config)
	{
		if (isset($config['fields']) && is_array($config['fields']))
			$this->addFields($config['fields']);

		parent::__construct($config);

		return $this;
	}

	public function availableOperators()
	{
		return array('sub');
	}

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

		if ($op != 'sub' || array_search($field,$this->fields) === false) {
			return '';
		}
		Loader :: loadClass('CategoryUtil');
		
		$cats = CategoryUtil :: getSubCategories($value);
		$items = array();
		$items[] = $value;
		foreach ($cats as $item) {
			$items[] = $item['id'];
		}
		if (count($items) == 1)
			$where = $this->column[$field]." = " . implode("", $items);
		else
			$where = $this->column[$field]." IN (" . implode(",", $items) . ")";
		return array('where' => $where);
	}
}