<?php
/**
 * PageMaster
 *
 * @copyright (c) 2008, PageMaster Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @version     $ Id $
 * @package     Zikula_3rd_party_Modules
 * @subpackage  pagemaster
 */

/**
 * Generates HTML vor Category Browsing
 * @author kundi
 * @param $args['tid'] tid
 * @param $args['field'] fieldname of the pubfield which contains category
 * @param $args['template'] optional filename of template
 * @param $args['count'] optional count available pubs in this category
 * @param $args['multiselect'] are more selection in one browser allowed (makes only sense for multilist fields)
 * @param $args['globalmultiselect'] are more then one selections in all available browsers allowed
 * @param $args['togglediv'] this div will be toggled, if at least one entry is selected (if you wanna hidde cats as pulldownmenus)
 * @param $args['cache'] enable cache
 * @param $args['assign'] optional

 * @return html of category tree
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
