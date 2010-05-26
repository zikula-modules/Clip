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
 * PmWorkflowUtil Class
 * We need a hotfix here about the actions information for an object.
 */
class PmWorkflowUtil extends WorkflowUtil
{
    /**
     * get possible actions for a given item of data in it's current workflow state
     *
     * @param array $obj
     * @param string $dbTable
     * @param mixed $idcolumn id field default = 'id'
     * @param string $module module name (defaults to current module)
     *
     * @return mixed array of actions or bool false
     */
    function getActionsForObject(&$obj, $dbTable, $idcolumn = 'id', $module = null)
    {
        $dom = ZLanguage::getModuleDomain('PageMaster');

        if (!is_array($obj)) {
            return LogUtil::registerError(__f('Error! Missing argument [%s].', 'obj', $dom));
        }

        if (!isset($dbTable)) {
            return LogUtil::registerError(__f('Error! Missing argument [%s].', 'dbtable', $dom));
        }

        if (empty($module)) {
            $module = pnModGetName();
        }

        if (!WorkflowUtil::getWorkflowForObject($obj, $dbTable, $idcolumn, $module)) {
            return false;
        }

        $workflow = $obj['__WORKFLOW__'];
        return WorkflowUtil::getActionsByStateArray($workflow['schemaname'], $workflow['module'], $workflow['state'], $obj);
    }
}
