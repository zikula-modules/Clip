<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2008, Zikula Development Team
 * @link http://www.zikula.org
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_Generated_Modules
 * @subpackage Pagemaster
 * @author Axel Guckelsberger
 * @url http://modulestudio.de
 */

/*
 * generated at Sun Aug 03 14:43:13 CEST 2008 by ModuleStudio 0.4.10 (http://modulestudio.de)
 */


Loader::loadClass('PagemasterFilterUtil_ReplaceCommon', Pagemaster_FILTERUTIL_CLASS_PATH);

/**
 * Date plugin main class
 *
 * This plugin
 */
class PagemasterFilterUtil_Plugin_Date extends PagemasterFilterUtil_ReplaceCommon {

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
            throw new Exception(PagemasterFilterUtil_Plugin_Date_Error::NotConfigured);
        }

        // Now, work!

        //convert to unix timestamp
        if (($timestamp = $this->DateToStamp($value)) === false) {
            throw new Exception(PagemasterFilterUtil_Plugin_Date_Error::CantSolveDatestamp);
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
            $time = mktime($arr['tm_hour'], $arr['tm_min'], $arr['tm_sec'],
                           $arr['tm_mon'], $arr['tm_monday'], $arr['tm_year']);
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
class PagemasterFilterUtil_Plugin_Date_Config extends PagemasterFilterUtil_PluginConfig {

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
     * @return PagemasterFilterUtil_Plugin_Date_Config object
     */
    public function PagemasterFilterUtil_Plugin_Date_Config($args = array())
    {
        //Set own configurations
        if ($args['dateformat']) {
            if ($this->setDateformat($args['dateformat']) === false) {
                throw Exceptions(PagemasterFilterUtil_Plugin_Date_Error::CantSetDateformat);
            }
        } else {
            // set standard dateformat
            $this->setDateformat(PagemasterFilterUtil_Plugin_Date_Config::standardDateformat);
        }
        //Call base config constructor to set base config
        $this->PagemasterFilterUtil_PluginConfig($args);

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
