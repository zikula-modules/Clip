<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Model
 */

/**
 * This is the model class that define the entity structure and behaviours.
 */
class Clip_Model_Pubfield extends Doctrine_Record
{
    /**
     * Set table definition.
     *
     * @return void
     */
    public function setTableDefinition()
    {
        $this->setTableName('clip_pubfields');

        $this->hasColumn('id as id', 'integer', 4, array(
            'primary' => true,
            'autoincrement' => true
        ));

        $this->hasColumn('tid as tid', 'integer', 4);

        $this->hasColumn('name as name', 'string', 255, array(
            'notnull' => true,
            'default' => ''
        ));

        $this->hasColumn('title as title', 'string', 255, array(
            'notnull' => true,
            'default' => ''
        ));

        $this->hasColumn('description as description', 'string', 255, array(
            'notnull' => true,
            'default' => ''
        ));

        $this->hasColumn('plugin as fieldplugin', 'string', 50, array(
            'notnull' => true,
            'default' => ''
        ));

        $this->hasColumn('config as typedata', 'string', 4000);

        $this->hasColumn('fielddbtype as fieldtype', 'string', 50, array(
            'notnull' => true,
            'default' => ''
        ));

        $this->hasColumn('fieldmaxlength as fieldmaxlength', 'integer', 4, array(
            'default' => null
        ));

        $this->hasColumn('weight as lineno', 'integer', 4);

        $this->hasColumn('is_title as istitle', 'boolean', null, array(
            'notnull' => true,
            'default' => 0
        ));

        $this->hasColumn('is_mandatory as ismandatory', 'boolean', null, array(
            'notnull' => true,
            'default' => 0
        ));

        $this->hasColumn('is_searchable as issearchable', 'boolean', null, array(
            'notnull' => true,
            'default' => 0
        ));

        $this->hasColumn('is_filterable as isfilterable', 'boolean', null, array(
            'notnull' => true,
            'default' => 0
        ));

        $this->hasColumn('is_pageable as ispageable', 'boolean', null, array(
            'notnull' => true,
            'default' => 0
        ));

        $this->hasColumn('is_counter as iscounter', 'boolean', null, array(
            'notnull' => true,
            'default' => 0
        ));
    }

    /**
     * Record setup.
     *
     * @return void
     */
    public function setUp()
    {
        $this->actAs('Zikula_Doctrine_Template_StandardFields');
    }

    /**
     * Clip utility TableName getter.
     *
     * @return string Zikula table name.
     */
    public function getTableName()
    {
        return 'clip_pubdata'.$this->tid;
    }

    /**
     * Insert hook.
     *
     * @return void
     */
    public function preInsert($event)
    {
        $pubfield = $event->getInvoker();

        $where = array(
            array('tid = ?', $pubfield->tid)
        );
        $max_lineno = (int)Doctrine_Core::getTable('Clip_Model_Pubfield')
                              ->selectFieldFunction('lineno', 'MAX', $where);

        $pubfield->lineno = $max_lineno + 1;
    }

    /**
     * Delete hook.
     *
     * @return void
     */
    public function postDelete($event)
    {
        $pubfield = $event->getInvoker();

        static $once = array();

        // avoid massive regeneration on pubtype clone/deletion
        if (!isset($once[$pubfield->tid])) {
            // FIXME detect if the field was updated and is a sort field
            // FIXME detect if the field was deleted and a sort field

            // update the pubtype's model file
            Clip_Generator::updateModel($pubfield->tid);

            // update the pubtype's table
            $classname = Clip_Generator::createTempModel($pubfield->tid);
            Doctrine_Core::getTable($classname)->changeTable(true);

            $once[$pubfield->tid] = true;
        }
    }
}
