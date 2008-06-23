<?php
/**
 * PageMaster
 *
 * @copyright (c) 2008, PageMaster Team
 * @link        http://code.zikula.org/projects/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package     Zikula_3rd_party_Modules
 * @subpackage  pagemaster
 */

Loader::includeOnce('modules/pagemaster/common.php');

function pagemaster_import_importps()
{
    if (!SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerError(_NOT_AUTHORIZED);
    }

    $step = FormUtil::getPassedValue('step');
    if ($step <> '') {
        $ret = pnModAPIFunc('pagemaster', 'import', 'importps'.$step);
    }
    $render = pnRender::getInstance('pagemaster', null, null, true);

    // check if exitsts
    $pubtypes = DBUtil::selectObjectArray('pagemaster_pubtypes');
    if (count($pubtypes) > 0) {
        $render->assign('allreadyexists', 1);
    } else {
        $render->assign('allreadyexists', 0);
    }

    return $render->fetch('pagemaster_admin_importps.htm');
}
