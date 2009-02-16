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


Loader::loadClass('PagemasterFilterUtil_Common', Pagemaster_FILTERUTIL_CLASS_PATH);

class PagemasterFilterUtil_OpCommon extends PagemasterFilterUtil_Common {

    /**
     * Activated operators
     */
    protected $ops = array();

    /**
     * Activated fields
     */
    protected $fields;

    /**
     * default handler
     */
    protected $default = false;

    /**
     * ID of the plugin
     */
    protected $id;

    /**
     * Constructor
     *
     * @access public
     * @param array $config Configuration array
     * @return object PagemasterFilterUtil_Plugin_* object
     */
    public function __construct($config = array())
    {
        parent::__construct($config);

        if (isset($config['fields']) && (!isset($this->fields) || !is_array($this->fields))) {
            $this->addFields($config['fields']);
        }

        if ((isset($config['default']) && $config['default'] == true) || !isset($this->fields) || !is_array($this->fields)) {
            $this->default = true;
        }

        if (isset($config['ops']) && (!isset($this->ops) || !is_array($this->ops))) {
            $this->activateOperators($config['ops']);
        } else {
            $this->activateOperators($this->availableOperators());
        }
    }

    /**
     * set the plugin id
     *
     * @access public
     * @param int $id Plugin ID
     */
    public function setID($id)
    {
        $this->id = $id;
    }

    /**
     * Adds fields to list in common way
     *
     * @access public
     * @param mixed $fields Fields to add
     */
    public function addFields($fields)
    {
        if (is_array($fields)) {
            foreach($fields as $fld) {
                $this->addFields($fld);
            }
        } elseif (!empty($fields) && $this->fieldExists($fields) && (is_array($this->fields) && array_search($fields, $this->fields) === false)) {
            $this->fields[] = $fields;
        }
    }

    /**
     * Get fields in list in common way
     *
     * @access public
     * @return mixed Fields in list
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Adds fields to list in common way
     *
     * @access public
     * @param mixed $op Operators to activate
     */
    public function activateOperators($op)
    {
        if (is_array($op)) {
            foreach($op as $v)
                $this->activateOperators($v);
        } elseif (!empty($op) && array_search($op, $this->ops) === false && array_search($op, $this->availableOperators()) !== false) {
            $this->ops[] = $op;
        }
    }

    /**
     * Get operators
     *
     * @access public
     * @return array Set of Operators and Arrays
     */
    public function getOperators()
    {
        $fields = $this->getFields();
        if ($this->default == true)
            $fields[] = '-';

        $ops = array();
        foreach ($this->ops as $op) {
            $ops[$op] = $fields;
        }

        return $ops;
    }
}
