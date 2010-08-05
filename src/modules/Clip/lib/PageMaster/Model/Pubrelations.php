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
class PageMaster_Model_Pubrelations extends Doctrine_Record
{
    /**
     * Set table definition.
     *
     * @return void
     */
    public function setTableDefinition()
    {
        $this->setTableName('pagemaster_relations');

        $this->hasColumn('pm_id as id', 'integer', 4, array(
            'primary' => true,
            'autoincrement' => true
        ));

        $this->hasColumn('pm_tid1 as tid1', 'integer', 4, array(
            'notnull' => true
        ));

        $this->hasColumn('pm_tid2 as tid2', 'integer', 4, array(
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
        
    }
}
