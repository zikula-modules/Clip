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
class Clip_Model_Pubrelation extends Doctrine_Record
{
    /**
     * Set table definition.
     *
     * @return void
     */
    public function setTableDefinition()
    {
        $this->setTableName('clip_relations');

        $this->hasColumn('pm_id as id', 'integer', 4, array(
            'primary' => true,
            'autoincrement' => true
        ));

        $this->hasColumn('pm_type as type', 'integer', 2, array(
            'notnull' => true
        ));

        $this->hasColumn('pm_tid1 as tid1', 'integer', 4, array(
            'notnull' => true
        ));

        $this->hasColumn('pm_alias1 as alias1', 'string', 100, array(
            'notnull' => true
        ));

        $this->hasColumn('pm_tid2 as tid2', 'integer', 4, array(
            'notnull' => true
        ));

        $this->hasColumn('pm_alias2 as alias2', 'string', 100, array(
            'notnull' => true
        ));

        $this->hasColumn('pm_config as config', 'clob');
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
