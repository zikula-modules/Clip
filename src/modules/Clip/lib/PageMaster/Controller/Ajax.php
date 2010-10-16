<?php
/**
 * Clip
 *
 * @copyright   (c) Clip Team
 * @link        http://code.zikula.org/clip/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package     Zikula_3rdParty_Modules
 * @subpackage  clip
 */

/**
 * Ajax Controller.
 */
class Clip_Controller_Ajax extends Zikula_Controller
{
    public function changedlistorder()
    {
        if (!SecurityUtil::checkPermission('clip::', '::', ACCESS_ADMIN)) {
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
                      ->update('Clip_Model_Pubfield pf')
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
     * @param integer $_POST['tid']           ID of the publication type.
     * @param string  $_POST['keyword']       core_title:likefirst:KEYWORD filter.
     * @param string  $_POST['filter']        Filter string.
     * @param string  $_POST['orderby']       OrderBy string.
     * @param integer $_POST['startnum']      Offset to start from.
     * @param integer $_POST['itemsperpage']  Number of items to retrieve.
     * @param boolean $_POST['handleplugins'] Whether to parse the plugin fields.
     * @param boolean $_POST['loadworkflow']  Whether to add the workflow information.
     *
     * @return array Publication list.
     */
    public function view()
    {
        //// Validation
        $args['tid'] = (int)FormUtil::getPassedValue('tid', null, 'POST');

        if ($args['tid'] <= 0) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'tid'));
        }

        //// Security check
        if (!SecurityUtil::checkPermission('clip:list:', "{$args['tid']}::", ACCESS_READ)) {
            return LogUtil::registerPermissionError();
        }

        //// Parameters
        $pubtype = Clip_Util::getPubType($args['tid']);
        if (!$pubtype) {
            return LogUtil::registerError($this->__f('Error! No such publication type [%s] found.', $args['tid']));
        }

        $args = array(
            'tid'           => (int)$args['tid'],
            'keyword'       => FormUtil::getPassedValue('keyword', null, 'POST'),
            'filter'        => FormUtil::getPassedValue('filter', null, 'POST'),
            'orderby'       => FormUtil::getPassedValue('orderby', null, 'POST'),
            'startnum'      => FormUtil::getPassedValue('startnum', null, 'POST'),
            'itemsperpage'  => (int)FormUtil::getPassedValue('itemsperpage', $pubtype['itemsperpage'], 'POST'),
            'handleplugins' => FormUtil::getPassedValue('handleplugins', true, 'POST'),
            'loadworkflow'  => FormUtil::getPassedValue('loadworkflow', false, 'POST'),
            'countmode'     => 'no', // API default
            'checkperm'     => false // API default (already checked)
        );

        $args['itemsperpage'] = $args['itemsperpage'] > 0 ? $args['itemsperpage'] : $pubtype['itemsperpage'];

        //// Misc values
        $pubfields = Clip_Util::getPubFields($args['tid'], 'lineno');
        if (empty($pubfields)) {
            LogUtil::registerError($this->__('Error! No publication fields found.'));
        }

        $titlefield = Clip_Util::findTitleField($pubfields);
        $pubtype->mapValue('titlefield', $titlefield);

        // piece needed by the autocompleter
        if (!empty($args['keyword'])) {
            $args['filter'] = (empty($args['filter']) ? '' : $args['filter'].',')."$titlefield:likefirst:{$args['keyword']}";
        }
        // orderby processing
        if (empty($args['orderby'])) {
            $args['orderby'] = $titlefield;
        }
        $args['orderby'] = Clip_Util::createOrderBy($args['orderby']);

        //// Execution
        // Uses the API to get the list of publications
        $result = ModUtil::apiFunc('Clip', 'user', 'getall', $args);

        return array('data' => $result['publist']->toArray());
    }

    /**
     * Autocompletion list.
     * Returns the publications list on the expected autocompleter format.
     *
     * @see Clip_Controller_Ajax::view
     *
     * @return array Autocompletion list.
     */
    public function autocomplete()
    {
        $list = $this->view();

        $result = array();
        foreach ($list['data'] as $v) {
            $result[] = array(
                'value'   => $v['id'],
                'caption' => $v['core_title']
            );
        }

        return array('data' => $result);
    }
}
