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
class Clip_Controller_Ajaxdata extends Zikula_Controller_AbstractAjax
{
    public function _postSetup()
    {
        // no need for a Zikula_View so override it.
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
        $this->checkAjaxToken();

        //// Pubtype
        // validate and get the publication type first
        $args['tid'] = (int)$this->request->getPost()->get('tid', null);

        if (!Clip_Util::validateTid($args['tid'])) {
            return LogUtil::registerError($this->__f('Error! Invalid publication type ID passed [%s].', DataUtil::formatForDisplay($args['tid'])));
        }

        $pubtype = Clip_Util::getPubType($args['tid']);

        // Security check
        $this->throwForbiddenUnless(Clip_Access::toPubtype($pubtype, 'list'));

        //// Parameters
        $args = array(
            'tid'           => (int)$args['tid'],
            'keyword'       => $this->request->getPost()->get('keyword', null),
            'op'            => $this->request->getPost()->get('op', 'likefirst'),
            'filter'        => $this->request->getPost()->get('filter', null),
            'orderby'       => $this->request->getPost()->get('orderby', null),
            'startnum'      => $this->request->getPost()->get('startnum', null),
            'startnum'      => $this->request->getPost()->get('startnum', null),
            'itemsperpage'  => $pubtype['itemsperpage'],
            'handleplugins' => $this->request->getPost()->get('handleplugins', true),
            'loadworkflow'  => $this->request->getPost()->get('loadworkflow', false),
            'countmode'     => 'no', // API default
            'checkperm'     => false // API default (already checked)
        );

        if ($args['itemsperpage'] == 0) {
            $args['itemsperpage'] = $this->getVar('maxperpage', 100);
        }

        //// Misc values
        $titlefield = $pubtype->getTitleField();

        // piece needed by the autocompleter
        if (!empty($args['keyword'])) {
            $args['op'] = in_array($args['op'], array('search', 'likefirst', 'like')) ? $args['op'] : 'likefirst';
            $args['filter'] = (empty($args['filter']) ? '' : $args['filter'].',')."$titlefield:{$args['op']}:{$args['keyword']}";
        }
        // orderby processing
        if (empty($args['orderby'])) {
            $args['orderby'] = $titlefield;
        }

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
        $this->checkAjaxToken();

        $result = array();

        // FIXME SECURITY check this
        if (SecurityUtil::checkPermission('Users::', '::', ACCESS_COMMENT)) {
            $args = array(
                'keyword' => $this->request->getPost()->get('keyword'),
                'op'      => $this->request->getPost()->get('op', 'likefirst')
            );
            $args['op'] = in_array($args['op'], array('search', 'likefirst')) ? $args['op'] : 'likefirst';

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
