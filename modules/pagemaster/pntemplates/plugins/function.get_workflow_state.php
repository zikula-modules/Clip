<?php
/**
 * PageMaster
 *
 * @copyright   (c) PageMaster Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @version     $ Id $
 * @package     Zikula_3rdParty_Modules
 * @subpackage  pagemaster
 */

/**
 * Returns the state
 * @author kundi
 * @param $args['tid'] tid
 * @param $args['pid'] pid
 * @param $args['assign'] optional

 * @return string
 */
function smarty_function_get_workflow_state($params, &$smarty)
{
    $tid                = $params['tid'];
    $id                 = $params['id'];
    if (!$tid)
    return 'Required parameter [tid] not provided in smarty_function_get_workflow_state';

    if (!$id)
    return 'Required parameter [id] not provided in smarty_function_get_workflow_state';

    $tablename = 'pagemaster_pubdata'.$tid;
    $pub['id'] = $id;
    
    WorkflowUtil::getWorkflowForObject($pub, $tablename, 'id', 'pagemaster');

    if ($params['assign']) {
        $smarty->assign($params['assign'], $pub['__WORKFLOW__']);
    } else {
        return $pub['__WORKFLOW__'];
    }
}
