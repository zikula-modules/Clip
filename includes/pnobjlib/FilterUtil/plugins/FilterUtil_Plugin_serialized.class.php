<?php
/**
 * PostNuke Application Framework
 *
 * @copyright (c) 2007, Philipp Niethammer
 * @link http://www.guite.de
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @author	Philipp Niethammer <webmaster@nochwer.de>
 * @package PostNuke
 * @subpackage FilterUtil
 */

Loader::loadClass('FilterUtil_ReplaceCommon', FILTERUTIL_CLASS_PATH);

class FilterUtil_Plugin_serialized extends FilterUtil_OpCommon
{

	/**
	 * Constructor
	 *
	 * @access public
	 * @param array $config Configuration
	 * @return object FilterUtil_Plugin_Default
	 */
	public function __construct($config)
	{
		parent::__construct($config);

		return $this;
	}

	/**
	 * Add fields
	 *
	 * @access public
	 * @param mixed $field Array of field or single field name
	 */
	public function addFields($field)
	{
		if (is_array($field)) {
			foreach ($field as $v) {
				$this->addFields($v);
			}
		} else {
			$this->fields[] = $field;
		}
	}

	/**
	 * Replace operator
	 *
	 * @access public
	 * @param string $field Fieldname
	 * @param string $op Operator
	 * @param string $value Value
	 * @return array array(field, op, value)
	 */
	public function replace($field, $op, $value)
	{
		if (!isset($this->pair[$field]) || empty($this->pair[$field])) {
			return array($field, $op, $value);
		}
		
		$newfield = $this->pair[$field]; 
		
		switch ($op) {
		case "eq":
			$op = "";
			$value = "%".serialize($field) . serialize($value)."%";
			break;
		case "":
		}
		
	}
}
?>