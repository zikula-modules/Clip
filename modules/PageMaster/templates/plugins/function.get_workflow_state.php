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
 *
 * @author kundi
 * @param $args['tid'] tid
 * @param $args['pid'] pid
 * @param $args['assign'] optional
 *
 * @return string
 */
function smarty_function_get_workflow_state($params, &$smarty)
{
    $dom = ZLanguage::getModuleDomain('PageMaster');

    $tid = (int)$params['tid'];
    $id  = (int)$params['id'];

    if (!$tid) {
        return LogUtil::registerError(__f('Error! Missing argument [%s].', 'tid', $dom));
    }

    if (!$id) {
        return LogUtil::registerError(__f('Error! Missing argument [%s].', 'id', $dom));
    }

    $tablename = 'pagemaster_pubdata'.$tid;
    $pub       = array('id' => $id);

    WorkflowUtil::getWorkflowForObject($pub, $tablename, 'id', 'PageMaster');

    if ($params['assign']) {
        $smarty->assign($params['assign'], $pub['__WORKFLOW__']);
    } else {
        return $pub['__WORKFLOW__'];
    }
}
