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


class PagemasterFilterUtil_Common {

    /**
     * Table name in pntable.php
     */
    protected $pntable;

    /**
     * Table name
     */
    protected $table;

    /**
     * Table columns
     */
    protected $column;

    /**
     * Constructor
     * Set parameters each Class could need
     *
     * @param string $args['table'] Tablename
     */
    public function __construct($args = array()) {
        if (isset($args['table'])) {
            $this->setTable($args['table']);
        }
    }

    /**
     * Set table
     *
     * @access public
     * @param string $table Table name
     * @return bool true on success, false otherwise
     */
    public function setTable($table)
    {
        $pntable =& pnDBGetTables();

        if (!isset($pntable[$table]) || !isset($pntable[$table . '_column'])) {
            return false;
        }

        $this->pntable = $table;
        $this->table = $pntable[$table];
        $this->column =& $pntable[$table . '_column'];

        return true;
    }

    /**
     * Field exists?
     *
     * @access private
     * @param string $field Field name
     * @return bool true if the field exists, else if not
     */
    protected function fieldExists($field)
    {
        if (!isset($this->column[$field]) || empty($this->column[$field])) {
            return false;
        }

        return true;
    }

    /**
     * Add common config variables to config array
     *
     * @access protected
     * @param array $config Config array
     * @return array Config array including common config
     */
    protected function addCommon($config = array())
    {
        $config['table'] = $this->pntable;
        return $config;
    }
}
