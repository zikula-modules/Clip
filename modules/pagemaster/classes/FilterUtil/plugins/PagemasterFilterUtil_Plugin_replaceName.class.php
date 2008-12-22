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

class PagemasterFilterUtil_Plugin_replaceName extends PagemasterFilterUtil_ReplaceCommon
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
         if (isset($this->pair[$field]) && !empty($this->pair[$field])) {
             $field = $this->pair[$field];
         }

         return array($field, $op, $value);
     }
}
