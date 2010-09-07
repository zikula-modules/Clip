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
class PageMaster_Base_Pubdata extends Doctrine_Record
{
    /**
     * Record post process.
     *
     * @return void
     */
    public function pubPostProcess($loadworkflow = true)
    {
        $tablename = $this->_table->getInternalTableName();
        $tid = PageMaster_Util::getTidFromStringSuffix($tablename);

        if ($loadworkflow) {
            Zikula_Workflow_Util::getWorkflowForObject($this, $tablename, 'id', 'PageMaster');
        } else {
            $this->mapValue('__WORKFLOW__', array());
        }

        $core_title = PageMaster_Util::getTitleField($tid);

        $this->mapValue('core_title' , $this[$core_title]);
        $this->mapValue('core_uniqueid', "{$tid}-{$this['core_pid']}");
        $this->mapValue('core_tid', $tid);
        $this->mapValue('core_creator', ($this['core_author'] == UserUtil::getVar('uid')) ? true : false);
        $this->mapValue('core_approvalstate', isset($this['__WORKFLOW__']['state']) ? $this['__WORKFLOW__']['state'] : null);
    }

    /**
     * Returns the record fields as keys of a result array.
     *
     * @return array List of available fields as keys.
     */
    public function pubFields()
    {
        $fields = array();

        foreach ($this as $column => $value) {
            $fields[$column] = 'column';
        }

        // FIXME Prevent mapped Doctrine_Records from being displayed fully
        foreach ($this->_values as $key => $value) {
            $fields[$key] = 'value';
        }

        foreach ($this->_table->getRelations() as $key => $relation) {
            $fields[$key] = 'relation';
        }

        // reorder the fields conveniently
        $reorder = array(
            'core_title' => '',
            'core_uniqueid' => '',
            'core_tid' => '',
            'core_pid' => '',
            'core_author' => '',
            'core_creator' => '',
            'core_approvalstate' => ''
        );
        $fields = array_merge($reorder, $fields);
        
        return $fields;
    }

    /**
     * Returns the record relations as an indexed array.
     *
     * @return array List of available relations => tids.
     */
    public function getRelations()
    {
        $relations = array();

        foreach ($this->_table->getRelations() as $key => $relation) {
            $relations[$key] = PageMaster_Util::getTidFromStringSuffix($relation->getClass());
        }

        return $relations;
    }
}
