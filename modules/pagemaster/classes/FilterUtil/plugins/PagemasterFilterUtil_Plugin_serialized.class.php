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

class PagemasterFilterUtil_Plugin_serialized extends PagemasterFilterUtil_OpCommon
{

    /**
     * Constructor
     *
     * @access public
     * @param array $config Configuration
     * @return object PagemasterFilterUtil_Plugin_Default
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
        case ""
        }
    }
}
