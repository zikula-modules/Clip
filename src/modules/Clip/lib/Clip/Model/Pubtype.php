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
class Clip_Model_Pubtype extends Doctrine_Record
{
    /**
     * Set table definition.
     *
     * @return void
     */
    public function setTableDefinition()
    {
        $this->setTableName('clip_pubtypes');

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

        $this->hasColumn('pm_outputset as outputset', 'string', 255, array(
            'notnull' => true,
            'default' => ''
        ));

        $this->hasColumn('pm_inputset as inputset', 'string', 255, array(
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

        $this->hasColumn('pm_config as config', 'clob');
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
        return 'clip_pubdata'.$this->tid;
    }

    /**
     * Saving hook.
     *
     * @return void
     */
    public function preSave($event)
    {
        if (is_array($this->config)) {
            $this->config = serialize($this->config);
        }
    }

    /**
     * Hydrate hook.
     *
     * @return void
     */
    public function postHydrate($event)
    {
        if (is_object($event->data) && isset($event->data->config)) {
            if (!empty($event->data->config) && is_string($event->data->config)) {
                $event->data->config = unserialize($event->data->config);
            } else {
                $event->data->config = array(
                    'view' => array(
                        'load' => false,
                        'onlyown' => true,
                        'processrefs' => false,
                        'checkperm' => false,
                        'handleplugins' => false,
                        'loadworkflow' => false
                    ),
                    'display' => array(
                        'load' => true,
                        'onlyown' => true,
                        'processrefs' => true,
                        'checkperm' => true,
                        'handleplugins' => false,
                        'loadworkflow' => false
                    ),
                    'edit' => array(
                        'onlyown' => true
                    )
                );

            }
        } elseif (is_array($event->data) && isset($event->data['config']) && !empty($event->data['config']) && is_string($event->data['config'])) {
            $event->data['config'] = unserialize($event->data['config']);
        }
    }
}
