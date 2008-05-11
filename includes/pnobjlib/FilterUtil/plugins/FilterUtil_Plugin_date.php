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

/**
 * Date plugin main class
 *
 * This plugin
 */
class FilterUtil_Plugin_Date extends FilterUtil_ReplaceCommon {



	/**
	 * Replace field's value
	 *
	 * @param string $field Field name
	 * @param string $op Filter operator
	 * @param string $value Filter value
	 * @return string New filter value
	 */
	public function replace($field, $op, $value)
	{
		// First check if this plugin have to work with this field
		if (!$this->IsValidField($field)) {
			return $value; //If not, return given value
		}

		// Check if plugin is configured correctly
		if (!$this->config->IsConfigured()) {
			throw new Exception(FilterUtil_Plugin_Date_Error::NotConfigured);
		}

		// Now, work!

		//convert to unix timestamp
		if (($timestamp = $this->DateToStamp($value)) === false) {
			throw new Exception(FilterUtil_Plugin_Date_Error::CantSolveDatestamp);
		}

		return date($this->config->getDateFormat(), $timestamp);
	}

	protected function DateToStamp($date)
	{
		switch(true) {
		case strtotime($date) !== false:
			$time = strtotime($date);
			break;
		case strptime($date, "%d.%m.%Y %H:%M:%S") !== false:
			$arr = strptime($date, "%d.%m.%Y %H:%M:%S");
			$time = mktime(	$arr['tm_hour'],
							$arr['tm_min'],
							$arr['tm_sec'],
							$arr['tm_mon'],
							$arr['tm_monday'],
							$arr['tm_year']);
			break;
		case is_numeric($date):
			$time = $date;
			break;
		default:
			$time = false;
			break;
		}

		return $time;
	}
}

/**
 * Plugin's config class
 */
class FilterUtil_Plugin_Date_Config extends FilterUtil_PluginConfig {

	/**
	 * Define standard date format
	 */
	const standardDateformat = 'U';

	/**
	 * Dateformat
	 */
	private $dateformat;

	/**
	 * Constructor
	 *
	 * @param array $args['fields'] Fields to work with
	 * @return FilterUtil_Plugin_Date_Config object
	 */
	public function FilterUtil_Plugin_Date_Config($args = array())
	{
		//Set own configurations
		if ($args['dateformat']) {
			if ($this->setDateformat($args['dateformat']) === false) {
				throw Exceptions(FilterUtil_Plugin_Date_Error::CantSetDateformat);
			}
		} else {
			// set standard dateformat
			$this->setDateformat(FilterUtil_Plugin_Date_Config::standardDateformat);
		}
		//Call base config constructor to set base config
		$this->FilterUtil_PluginConfig($args);

		return $this;
	}

	/**
	 * set date format
	 *
	 * @param string $format format string in date()'s syntax
	 * @return bool true on success, false otherwise
	 */
	public function setDateFormat($format)
	{
		if (is_numeric(date($format))) {
			$this->dateformat = $format;
		}
	}

	/**
	 * check if plugin is configured
	 *
	 * @return bool true if the configuration is ok, false otherwise
	 */
	public function IsConfigured()
	{
		// check if the configuration is ok
		if (empty($this->dateformat) || !is_numeric(date($this->dateformat))) {
			return false; //if not, return false
		}
		//else return true
		return true;
	}
}
?>
