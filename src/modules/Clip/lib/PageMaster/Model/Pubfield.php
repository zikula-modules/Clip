<?php
/**
 * PageMaster
 *
 * @copyright   (c) PageMaster Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package     Zikula_3rdParty_Modules
 * @subpackage  pagemaster
 */

/**
 * This is the model class that define the entity structure and behaviours.
 */
class PageMaster_Model_Pubfield extends Doctrine_Record
{
    /**
     * Set table definition.
     *
     * @return void
     */
    public function setTableDefinition()
    {
        $this->setTableName('pagemaster_pubfields');

        $this->hasColumn('pm_id as id', 'integer', 4, array(
            'primary' => true,
            'autoincrement' => true
        ));

        $this->hasColumn('pm_tid as tid', 'integer', 4, array(
            'notnull' => true
        ));

        $this->hasColumn('pm_name as name', 'string', 255, array(
            'notnull' => true,
            'default' => ''
        ));

        $this->hasColumn('pm_title as title', 'string', 255, array(
            'notnull' => true,
            'default' => ''
        ));

        $this->hasColumn('pm_description as description', 'string', 255, array(
            'notnull' => true,
            'default' => ''
        ));

        $this->hasColumn('pm_fieldtype as fieldtype', 'string', 50, array(
            'notnull' => true,
            'default' => ''
        ));

        $this->hasColumn('pm_fieldplugin as fieldplugin', 'string', 50, array(
            'notnull' => true,
            'default' => ''
        ));

        $this->hasColumn('pm_fieldmaxlength as fieldmaxlength', 'integer', 4, array(
            'default' => null
        ));

        $this->hasColumn('pm_typedata as typedata', 'string', 4000);

        $this->hasColumn('pm_istitle as istitle', 'boolean', null, array(
            'notnull' => true
        ));

        $this->hasColumn('pm_ispageable as ispageable', 'boolean', null, array(
            'notnull' => true
        ));

        $this->hasColumn('pm_issearchable as issearchable', 'boolean', null, array(
            'notnull' => true
        ));

        $this->hasColumn('pm_ismandatory as ismandatory', 'boolean', null, array(
            'notnull' => true
        ));

        $this->hasColumn('pm_lineno as lineno', 'integer', 4, array(
            'notnull' => true
        ));
    }

    /**
     * Record setup.
     *
     * @return void
     */
    public function setUp()
    {
        $this->actAs('Zikula_Doctrine_Template_StandardFields', array('oldColumnPrefix' => 'pm_'));
    }

    /**
     * PageMaster utility TableName getter.
     *
     * @return string Zikula table name.
     */
    public function getTableName()
    {
        return 'pagemaster_pubdata'.$this->tid;
    }
}
