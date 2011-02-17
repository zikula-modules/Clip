<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Controller
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
            'op'            => FormUtil::getPassedValue('op', 'likefirst', 'POST'),
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
        $titlefield = Clip_Util::getTitleField($args['tid']);
        $pubtype->mapValue('titlefield', $titlefield);

        // piece needed by the autocompleter
        if (!empty($args['keyword'])) {
            $args['op'] = in_array($args['op'], array('search', 'likefirst', 'like')) ? $args['op'] : 'likefirst';
            $args['filter'] = (empty($args['filter']) ? '' : $args['filter'].',')."$titlefield:{$args['op']}:{$args['keyword']}";
        }
        // orderby processing
        if (empty($args['orderby'])) {
            $args['orderby'] = $titlefield;
        }
        $args['orderby'] = Clip_Util::createOrderBy($args['orderby']);

        //// Execution
        // Uses the API to get the list of publications
        $result = ModUtil::apiFunc('Clip', 'user', 'getall', $args);

        return $result['publist']->toArray();
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
        foreach ($list as $v) {
            $result[] = array(
                'value'   => $v['id'],
                'caption' => DataUtil::formatForDisplay($v['core_title'])
            );
        }

        return new Zikula_Response_Ajax_Json(array('data' => $result));
    }

    /**
     * Autocompletion Users list.
     * Returns the users list on the expected autocompleter format.
     *
     * @return array Autocompletion list.
     */
    public function getusers()
    {
        $result = array();

        if (SecurityUtil::checkPermission('Users::', '::', ACCESS_COMMENT)) {
            $args = array(
                'keyword' => FormUtil::getPassedValue('keyword', null, 'POST'),
                'op'      => FormUtil::getPassedValue('op', 'likefirst', 'POST')
            );
            $args['op'] = in_array($args['op'], array('search', 'likefirst')) ? $args['op'] : 'likefirst';

            ModUtil::dbInfoLoad('Users');
            $tables = DBUtil::getTables();

            $usersColumn = $tables['users_column'];

            $value = DataUtil::formatForStore($args['keyword']);
            switch ($args['op']) {
                case 'search':
                    $value = '%'.$value;
                case 'likefirst':
                    $value .= '%';
                    $value = "'$value'";
                    break;
            }
            $where = 'WHERE ' . $usersColumn['uname'] . ' LIKE ' . $value;
            $results = DBUtil::selectFieldArray('users', 'uname', $where, $usersColumn['uname'], false, 'uid');

            foreach ($results as $uid => $uname) {
                $result[] = array(
                    'value'   => $uid,
                    'caption' => DataUtil::formatForDisplay($uname)
                );
            }
        }

        return new Zikula_Response_Ajax_Json(array('data' => $result));
    }
}
