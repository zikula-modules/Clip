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

function pagemaster_ajax_changedlistorder()
{
    $dom = ZLanguage::getModuleDomain('pagemaster');
    if (!SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
        AjaxUtil::error(DataUtil::formatForDisplayHTML(__('Sorry! No authorization to access this module.', $dom)));
    }

//    if (!SecurityUtil::confirmAuthKey()) {
//        AjaxUtil::error(__('Invalid 'authkey':  this probably means that you pressed the 'Back' button, or that the page 'authkey' expired. Please refresh the page and try again.', $dom));
//    }

    $pubfieldlist = FormUtil::getPassedValue('pubfieldlist');
    $tid = FormUtil::getPassedValue('tid');

    foreach ($pubfieldlist as $key => $value)
    {
        $data['lineno'] = $key;
        $result = DBUtil::updateObject($data, 'pagemaster_pubfields', 'pm_id = '.DataUtil::formatForStore($value).' AND pm_tid = '.DataUtil::formatForStore($tid));
        if (!$result) {
            AjaxUtil::error(__('Error! Update attempt failed.', $dom));
        }
    }

    return array('result' => true);
}
