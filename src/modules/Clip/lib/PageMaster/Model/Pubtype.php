<?php
/**
 * Clip
 *
 * @copyright   (c) Clip Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package     Zikula_3rdParty_Modules
 * @subpackage  pagemaster
 */

/**
 * This is the model class that define the entity structure and behaviours.
 */
class Clip_Model_Pubtype extends Doctrine_Record
{
    /**
     * Set table definition.
     *
     * @return void
     */
    public function setTableDefinition()
    {
        $this->setTableName('pagemaster_pubtypes');

        $this->hasColumn('pm_tid as tid', 'integer', 4, array(
            'primary' => true,
            'autoincrement' => true
        ));

        $this->hasColumn('pm_title as title', 'string', 255, array(
            'notnull' => true,
            'default' => ''
        ));

        $this->hasColumn('pm_urltitle as urltitle', 'string', 255, array(
            'notnull' => true,
            'default' => ''
        ));

        $this->hasColumn('pm_filename as outputset', 'string', 255, array(
            'notnull' => true,
            'default' => ''
        ));

        $this->hasColumn('pm_formname as inputset', 'string', 255, array(
            'notnull' => true,
            'default' => ''
        ));

        $this->hasColumn('pm_description as description', 'string', 255, array(
            'notnull' => true,
            'default' => ''
        ));

        $this->hasColumn('pm_itemsperpage as itemsperpage', 'integer', 4, array(
            'notnull' => true
        ));

        $this->hasColumn('pm_sortfield1 as sortfield1', 'string', 255);

        $this->hasColumn('pm_sortdesc1 as sortdesc1', 'boolean');

        $this->hasColumn('pm_sortfield2 as sortfield2', 'string', 255);

        $this->hasColumn('pm_sortdesc2 as sortdesc2', 'boolean');

        $this->hasColumn('pm_sortfield3 as sortfield3', 'string', 255);

        $this->hasColumn('pm_sortdesc3 as sortdesc3', 'boolean');

        $this->hasColumn('pm_workflow as workflow', 'string', 255, array(
            'notnull' => true
        ));

        $this->hasColumn('pm_defaultfilter as defaultfilter', 'string', 255);

        $this->hasColumn('pm_enablerevisions as enablerevisions', 'boolean', null, array(
            'notnull' => true
        ));

        $this->hasColumn('pm_enableeditown as enableeditown', 'boolean', null, array(
            'notnull' => true
        ));

        $this->hasColumn('pm_cachelifetime as cachelifetime', 'integer', 8, array(
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
        $this->actAs('Zikula_Doctrine_Template_StandardFields', array('oldColumnPrefix' => 'pm_'));
    }

    /**
     * Clip utility methods
     */
    public function getTableName()
    {
        return 'pagemaster_pubdata'.$this->tid;
    }
}
