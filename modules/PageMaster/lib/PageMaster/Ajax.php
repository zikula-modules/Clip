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

class PageMaster_Ajax extends Zikula_Controller
{

    public function changedlistorder()
    {
        if (!SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
            AjaxUtil::error($this->__('Sorry! No authorization to access this module.'));
        }

        //    if (!SecurityUtil::confirmAuthKey()) {
        //        AjaxUtil::error($this->___("Invalid authorisation key ('authkey'). This is probably either because you pressed the 'Back' button to return to a page which does not allow that, or else because the page's authorisation key expired due to prolonged inactivity. Please refresh the page and try again."));
        //    }

        $pubfields = FormUtil::getPassedValue('pubfieldlist');
        $tid       = FormUtil::getPassedValue('tid');

        foreach ($pubfields as $key => $value)
        {
            $data['lineno'] = $key;
            $where  = "pm_id = '".DataUtil::formatForStore($value)."' AND pm_tid = '".DataUtil::formatForStore($tid)."'";
            $result = DBUtil::updateObject($data, 'pagemaster_pubfields', $where);
            if (!$result) {
                AjaxUtil::error($this->__('Error! Update attempt failed.'));
            }
        }

        return array('result' => true);
    }
}