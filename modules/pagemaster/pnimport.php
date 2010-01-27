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

Loader::includeOnce('modules/pagemaster/common.php');

function pagemaster_import_importps()
{
    if (!SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    $step = FormUtil::getPassedValue('step');
    if (!empty($step)) {
        pnModAPIFunc('pagemaster', 'import', 'importps'.$step);
    }

    // check if there are pubtypes already
    $numpubtypes = DBUtil::selectObjectCount('pagemaster_pubtypes');

    // build the output
    $render = pnRender::getInstance('pagemaster', null, null, true);

    $render->assign('alreadyexists', $numpubtypes > 0 ? true : false);

    return $render->fetch('pagemaster_admin_importps.htm');
}
