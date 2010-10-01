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
 * Ajax Controller.
 */
class PageMaster_Controller_Ajax extends Zikula_Controller
{
    public function changedlistorder()
    {
        if (!SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
            AjaxUtil::error($this->__('Sorry! No authorization to access this module.'));
        }

        //if (!SecurityUtil::confirmAuthKey()) {
        //    AjaxUtil::error($this->___("Invalid authorisation key ('authkey'). This is probably either because you pressed the 'Back' button to return to a page which does not allow that, or else because the page's authorisation key expired due to prolonged inactivity. Please refresh the page and try again."));
        //}

        $pubfields = FormUtil::getPassedValue('pubfieldlist');
        $tid       = FormUtil::getPassedValue('tid');

        foreach ($pubfields as $key => $value)
        {
            $result = Doctrine_Query::create()
                      ->update('PageMaster_Model_Pubfield pf')
                      ->set('pf.lineno', '?', $key)
                      ->where('pf.id = ?', $value)
                      ->addWhere('pf.tid = ?', $tid)
                      ->execute();

            if ($result === false) {
                AjaxUtil::error($this->__('Error! Update attempt failed.'));
            }
        }

        return array('result' => true);
    }

    /**
     * Publications list.
     *
     * @param integer $_POST['tid']                ID of the publication type.
     * @param string  $_POST['keyword']            core_title:likefirst:KEYWORD filter.
     * @param string  $_POST['filter']             Filter string.
     * @param string  $_POST['orderby']            OrderBy string.
     * @param integer $_POST['itemsperpage']       Number of items to retrieve.
     * @param integer $_POST['startnum']           Offset to start from.
     * @param boolean $_POST['handlePluginFields'] Whether to parse the plugin fields.
     * @param boolean $_POST['getApprovalState']   Whether to add the workflow information.
     *
     * @return array Publication list.
     */
    public function view()
    {
        // get the tid first
        $tid = FormUtil::getPassedValue('tid', null, 'POST');

        if (empty($tid) || !is_numeric($tid)) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'tid'));
        }

        // security check as early as possible
        if (!SecurityUtil::checkPermission('pagemaster:list:', "$tid::", ACCESS_READ)) {
            return LogUtil::registerPermissionError();
        }

        $pubtype = PageMaster_Util::getPubType($tid);
        if (empty($pubtype)) {
            return LogUtil::registerError($this->__f('Error! No such publication type [%s] found.', $tid));
        }

        $pubfields = PageMaster_Util::getPubFields($tid, 'lineno');
        if (empty($pubfields)) {
            LogUtil::registerError($this->__('Error! No publication fields found.'));
        }

        $titlefield = PageMaster_Util::findTitleField($pubfields);
        $pubtype->mapValue('titlefield', $titlefield);

        // get the input parameters
        $key                = FormUtil::getPassedValue('keyword', null, 'POST');
        $filter             = FormUtil::getPassedValue('filter', null, 'POST');
        $orderby            = FormUtil::getPassedValue('orderby', null, 'POST');
        $itemsperpage       = FormUtil::getPassedValue('itemsperpage', $pubtype['itemsperpage'], 'POST');
        $startnum           = FormUtil::getPassedValue('startnum', null, 'POST');
        $handlePluginFields = FormUtil::getPassedValue('handlePluginFields', true, 'POST');
        $getApprovalState   = FormUtil::getPassedValue('getApprovalState', false, 'POST');

        // validation
        $itemsperpage = (int)$itemsperpage > 0 ? (int)$itemsperpage : $pubtype['itemsperpage'];

        if (!empty($key)) {
            $filter = (empty($filter) ? '' : $filter.',')."$titlefield:likefirst:$key";
            if (empty($orderby)) {
                $orderby = $titlefield;
            }
        }

        $orderby = PageMaster_Util::createOrderBy($orderby);

        // Uses the API to get the list of publications
        $result = ModUtil::apiFunc('PageMaster', 'user', 'getall',
                                   array('tid'                => $tid,
                                         'filter'             => $filter,
                                         'orderby'            => $orderby,
                                         'itemsperpage'       => $itemsperpage,
                                         'startnum'           => $startnum,
                                         'countmode'          => 'no',
                                         'checkPerm'          => false, // already checked
                                         'handlePluginFields' => $handlePluginFields,
                                         'getApprovalState'   => $getApprovalState));

        return $result['publist']->toArray();
    }

    /**
     * Autocompletion list.
     * Returns the publications list on the expected autocompleter format.
     *
     * @see PageMaster_Controller_Ajax::view
     *
     * @return array Autocompletion list.
     */
    public function autocomplete()
    {
        $list = $this->view();

        $result = array();
        foreach ($list as $v) {
            $result[] = array(
                'caption' => $v['core_title'],
                'value'   => $v['id']
            );
        }

        return array('data' => $result);
    }
}
