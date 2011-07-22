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
class Clip_Model_Grouptype extends Doctrine_Record
{
    /**
     * Set table definition.
     *
     * @return void
     */
    public function setTableDefinition()
    {
        $this->setTableName('clip_grouptypes');

        $this->hasColumn('gid as gid', 'integer', 4, array(
            'primary' => true,
            'autoincrement' => true
        ));

        $this->hasColumn('name as name', 'string', 65535, array(
            'notnull' => true,
            'default' => ''
        ));

        $this->hasColumn('description as description', 'string', 65535, array(
            'notnull' => true,
            'default' => ''
        ));

        $this->hasColumn('sortorder as order', 'string', 65535, array(
            'notnull' => true,
            'default' => ''
        ));
    }

    /**
     * Record setup.
     *
     * @return void
     */
    public function setUp()
    {
        $this->actAs('NestedSet');

        $this->hasMany('Clip_Model_Pubtype as pubtypes', array(
              'local' => 'gid',
              'foreign' => 'grouptype'
        ));
    }

    /**
     * Save hooks.
     *
     * @return void
     */
    public function preSave($event)
    {
        $invoker = $event->getInvoker();

        $invoker->name        = isset($invoker->name) && !empty($invoker->name)
                                    ? (DataUtil::is_serialized($invoker->name) ? $invoker->name : serialize($invoker->name))
                                    : serialize(array());

        $invoker->description = isset($invoker->description) && !empty($invoker->description)
                                    ? (DataUtil::is_serialized($invoker->description) ? $invoker->description : serialize($invoker->description))
                                    : serialize(array());

        $invoker->order       = isset($invoker->order) && !empty($invoker->order)
                                    ? (DataUtil::is_serialized($invoker->order) ? $invoker->order : serialize($invoker->order))
                                    : serialize(array());
    }

    public function postSave($event)
    {
        $data = $event->getInvoker();

        $data['name']        = isset($data['name']) && !empty($data['name'])
                                   ? (DataUtil::is_serialized($data['name']) ? unserialize($data['name']) : $data['name'])
                                   : array();

        $data['description'] = isset($data['description']) && !empty($data['description'])
                                   ? (DataUtil::is_serialized($data['description']) ? unserialize($data['description']) : $data['description'])
                                   : array();

        $data['order']       = isset($data['order']) && !empty($data['order'])
                                   ? (DataUtil::is_serialized($data['order']) ? unserialize($data['order']) : $data['order'])
                                   : array();
    }

    /**
     * Hydration hook.
     *
     * @return void
     */
    public function postHydrate($event)
    {
        $data = $event->data;

        $data['name']        = isset($data['name']) && !empty($data['name'])
                                   ? (DataUtil::is_serialized($data['name']) ? unserialize($data['name']) : $data['name'])
                                   : array();

        $data['description'] = isset($data['description']) && !empty($data['description'])
                                   ? (DataUtil::is_serialized($data['description']) ? unserialize($data['description']) : $data['description'])
                                   : array();

        $data['order']       = isset($data['order']) && !empty($data['order'])
                                   ? (DataUtil::is_serialized($data['order']) ? unserialize($data['order']) : $data['order'])
                                   : array();
    }
}
