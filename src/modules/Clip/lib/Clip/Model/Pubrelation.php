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

        $this->hasColumn('id as id', 'integer', 4, array(
            'primary' => true,
            'autoincrement' => true
        ));

        $this->hasColumn('type as type', 'integer', 2, array(
            'notnull' => true,
            'default' => 1
        ));

        $this->hasColumn('tid1 as tid1', 'integer', 4);

        $this->hasColumn('alias1 as alias1', 'string', 100);

        $this->hasColumn('title1 as title1', 'string', 100);

        $this->hasColumn('desc1 as descr1', 'string', 4000);

        $this->hasColumn('tid2 as tid2', 'integer', 4);

        $this->hasColumn('alias2 as alias2', 'string', 100);

        $this->hasColumn('title2 as title2', 'string', 100);

        $this->hasColumn('desc2 as descr2', 'string', 4000);
    }

    /**
     * Record setup.
     *
     * @return void
     */
    public function setUp()
    {
        
    }

    /**
     * Create hook.
     *
     * @return void
     */
    public function postInsert($event)
    {
        $relation = $event->getInvoker();

        // create the relation table
        Clip_Generator::createRelationsModels();
        if ($relation->type == 3) {
            Doctrine_Core::getTable('ClipModels_Relation'.$relation->id)->createTable();
        }
    }
}
