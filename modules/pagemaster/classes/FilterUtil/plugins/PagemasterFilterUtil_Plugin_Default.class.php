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


Loader::loadClass('PagemasterFilterUtil_OpCommon', Pagemaster_FILTERUTIL_CLASS_PATH);

class PagemasterFilterUtil_Plugin_Default extends PagemasterFilterUtil_OpCommon
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

    protected function availableOperators()
    {
        $ops = array('eq', 'ne', 'lt', 'le', 'gt', 'ge', 'like', 'null', 'notnull');

        return $ops;
    }

    /**
     * return SQL code
     *
     * @access public
     * @param string $field Field name
     * @param string $myfield SQL Field name
     * @param string $op Operator
     * @param string $value Test value
     * @return string SQL code
     */
    public function getSQL($field, $op, $value)
    {
        switch ($op) {
        case 'ne':
            return array('where' => $this->column[$field]." <> '" . $value . "'");
            break;
        case 'lt':
            return array('where' => $this->column[$field]." < '" . $value . "'");
            break;
        case 'le':
            return array('where' => $this->column[$field]." <= '" . $value . "'");
            break;
        case 'gt':
            return array('where' => $this->column[$field]." > '" . $value . "'");
            break;
        case 'ge':
            return array('where' => $this->column[$field]." >= '" . $value . "'");
            break;
        case 'like':
            return array('where' => $this->column[$field]." like '" . $value . "'");
            break;
        case 'null':
            return array('where' => $this->column[$field]." = '' OR ".$this->column[$field]." IS NULL");
            break;
        case 'notnull':
            return array('where' => $this->column[$field]." <> '' AND ".$this->column[$field]." IS NOT NULL");
            break;
        case 'eq':
            return array('where' => $this->column[$field]." = '" . $value . "'");
            break;
        default:
            return '';
        }
    }
}
