<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://github.com/zikula-modules/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Controller
 */

namespace Clip\Controller;

use DataUtil;
use LogUtil;
use Clip_Util;
use Clip_Access;
use ModUtil;
use Zikula_Response_Ajax_Json;
use DBUtil;
use SecurityUtil;

/**
 * Ajax Controller.
 */
class AjaxdataController extends \Zikula_Controller_AbstractAjax
{
    public function _postSetup()
    {
        
    }
    
    /**
     * Publications list.
     *
     * @param mixed   $_POST['tid']           ID/urltitle of the publication type.
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
    public function viewAction()
    {
        $this->checkAjaxToken();
        //// Pubtype
        // validate and get the publication type first
        $args['tid'] = (int) $this->request->getPost()->get('tid', null);
        if (!Clip_Util::validateTid($args['tid'])) {
            return LogUtil::registerError($this->__f('Error! Invalid publication type ID passed [%s].', DataUtil::formatForDisplay($args['tid'])));
        }
        $pubtype = Clip_Util::getPubType($args['tid']);
        // Security check
        $this->throwForbiddenUnless(Clip_Access::toPubtype($pubtype, 'list'));
        //// Parameters
        $args = array('tid' => $pubtype['tid'], 'keyword' => $this->request->getPost()->get('keyword', null), 'op' => $this->request->getPost()->get('op', 'likefirst'), 'filter' => $this->request->getPost()->get('filter', null), 'orderby' => $this->request->getPost()->get('orderby', null), 'startnum' => $this->request->getPost()->get('startnum', null), 'startnum' => $this->request->getPost()->get('startnum', null), 'itemsperpage' => $pubtype['itemsperpage'], 'handleplugins' => $this->request->getPost()->get('handleplugins', true), 'loadworkflow' => $this->request->getPost()->get('loadworkflow', false), 'countmode' => 'no', 'checkperm' => false);
        if ($args['itemsperpage'] == 0) {
            $args['itemsperpage'] = $this->getVar('maxperpage', 100);
        }
        //// Misc values
        $titlefield = $pubtype->getTitleField();
        // piece needed by the autocompleter
        if (!empty($args['keyword'])) {
            $args['op'] = in_array($args['op'], array('search', 'likefirst', 'like')) ? $args['op'] : 'likefirst';
            $args['filter'] = (empty($args['filter']) ? '' : $args['filter'] . ',') . "{$titlefield}:{$args['op']}:{$args['keyword']}";
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
    public function autocompleteAction()
    {
        $list = $this->view();
        $result = array();
        foreach ($list['data'] as $v) {
            $result[] = array('value' => $v['id'], 'caption' => DataUtil::formatForDisplay($v['core_title']));
        }
        return new Zikula_Response_Ajax_Json(array('data' => $result));
    }
    
    /**
     * Autocompletion Users list.
     * Returns the users list on the expected autocompleter format.
     *
     * @return array Autocompletion list.
     */
    public function getusersAction()
    {
        $this->checkAjaxToken();
        $result = array();
        // FIXME SECURITY check this
        if (SecurityUtil::checkPermission('Users::', '::', ACCESS_COMMENT)) {
            $args = array('keyword' => $this->request->getPost()->get('keyword'), 'op' => $this->request->getPost()->get('op', 'likefirst'));
            $args['op'] = in_array($args['op'], array('search', 'likefirst')) ? $args['op'] : 'likefirst';
            $tables = DBUtil::getTables();
            $usersColumn = $tables['users_column'];
            $value = DataUtil::formatForStore($args['keyword']);
            // check anonymous match first
            $anonymous = $this->__('Anonymous');
            if (stripos($anonymous, $value) !== false) {
                $result[] = array('value' => 0, 'caption' => DataUtil::formatForDisplay($anonymous));
            }
            // check matches in the database
            switch ($args['op']) {
                case 'search':
                    $value = '%' . $value;
                case 'likefirst':
                    $value .= '%';
                    $value = "'{$value}'";
                    break;
            }
            $where = 'WHERE ' . $usersColumn['uname'] . ' LIKE ' . $value;
            $results = DBUtil::selectFieldArray('users', 'uname', $where, $usersColumn['uname'], false, 'uid');
            foreach ($results as $uid => $uname) {
                $result[] = array('value' => $uid, 'caption' => DataUtil::formatForDisplay($uname));
            }
        }
        return new Zikula_Response_Ajax_Json(array('data' => $result));
    }
    
    /**
     * Autocompletion Groups list.
     * Returns the groups list on the expected autocompleter format.
     *
     * @return array Autocompletion list.
     */
    public function getgroupsAction()
    {
        $this->checkAjaxToken();
        $result = array();
        // FIXME SECURITY check this
        if (SecurityUtil::checkPermission('Groups::', 'ANY', ACCESS_OVERVIEW)) {
            $args = array('keyword' => $this->request->getPost()->get('keyword'), 'op' => $this->request->getPost()->get('op', 'likefirst'));
            $args['op'] = in_array($args['op'], array('search', 'likefirst')) ? $args['op'] : 'likefirst';
            $tables = DBUtil::getTables();
            $grpColumn = $tables['groups_column'];
            $value = DataUtil::formatForStore($args['keyword']);
            // check matches in the database
            switch ($args['op']) {
                case 'search':
                    $value = '%' . $value;
                case 'likefirst':
                    $value .= '%';
                    $value = "'{$value}'";
                    break;
            }
            $where = 'WHERE ' . $grpColumn['name'] . ' LIKE ' . $value;
            $results = DBUtil::selectFieldArray('groups', 'name', $where, $grpColumn['name'], false, 'gid');
            foreach ($results as $gid => $gname) {
                $result[] = array('value' => $gid, 'caption' => DataUtil::formatForDisplay($gname));
            }
        }
        return new Zikula_Response_Ajax_Json(array('data' => $result));
    }
    
    /**
     * Autocompletion Recipients list.
     * Returns the recipients list on the expected autocompleter format.
     *
     * @return array Autocompletion list.
     */
    public function recipientsAction()
    {
        $this->checkAjaxToken();
        $result = array();
        $args = array('keyword' => $this->request->getPost()->get('keyword'));
        $tables = DBUtil::getTables();
        if (SecurityUtil::checkPermission('Groups::', 'ANY', ACCESS_OVERVIEW)) {
            $grpColumn = $tables['groups_column'];
            $value = DataUtil::formatForStore($args['keyword']);
            $where = "WHERE {$grpColumn['name']} LIKE '%{$value}%'";
            $results = DBUtil::selectFieldArray('groups', 'name', $where, $grpColumn['name'], false, 'gid');
            foreach ($results as $gid => $name) {
                $result[] = array('value' => "g{$gid}", 'caption' => $this->__f('%s (Group)', DataUtil::formatForDisplay($name)));
            }
        }
        if (SecurityUtil::checkPermission('Users::', '::', ACCESS_COMMENT)) {
            $usersColumn = $tables['users_column'];
            $value = DataUtil::formatForStore($args['keyword']);
            $where = "WHERE {$usersColumn['uname']} LIKE '%{$value}%'";
            $results = DBUtil::selectFieldArray('users', 'uname', $where, $usersColumn['uname'], false, 'uid');
            foreach ($results as $uid => $uname) {
                $result[] = array('value' => "u{$uid}", 'caption' => DataUtil::formatForDisplay($uname));
            }
        }
        return new Zikula_Response_Ajax_Json(array('data' => $result));
    }
}
