<?php
/**
 * PageMaster
 *
 * @copyright (c) 2008, PageMaster Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package     Zikula_3rd_party_Modules
 * @subpackage  pagemaster
 */

function pagemaster_ajax_changedlistorder()
{
    if (!SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
        AjaxUtil::error(DataUtil::formatForDisplayHTML(_MODULENOAUTH));
    }

//    if (!SecurityUtil::confirmAuthKey()) {
//        AjaxUtil::error(_BADAUTHKEY);
//    }

    $pubfieldlist = FormUtil::getPassedValue('pubfieldlist');
    $tid = FormUtil::getPassedValue('tid');

    foreach($pubfieldlist as $key => $value)
    {
        $data['lineno'] = $key;
        $result = DBUtil::updateObject($data, 'pagemaster_pubfields', 'pm_id = '.DataUtil::formatForStore($value).' AND pm_tid = '.DataUtil::formatForStore($tid));
        if (!$result) {
            AjaxUtil::error(_UPDATEFAILED);
        }
    }
    return array('result' => true);
}
