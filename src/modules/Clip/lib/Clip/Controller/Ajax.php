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
class Clip_Controller_Ajax extends Zikula_Controller_AbstractAjax
{
    public function _postSetup()
    {
        // no need for a Zikula_View so override it.
    }

    public function changelistorder()
    {
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Clip::', '::', ACCESS_ADMIN));

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
        $this->checkAjaxToken();

        //// Validation
        $args['tid'] = (int)$this->request->getPost()->get('tid', null);

        if ($args['tid'] <= 0) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'tid'));
        }

        // Security check
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Clip:list:', "{$args['tid']}::", ACCESS_READ));

        // Parameters
        $pubtype = Clip_Util::getPubType($args['tid']);
        if (!$pubtype) {
            return LogUtil::registerError($this->__f('Error! No such publication type [%s] found.', $args['tid']));
        }

        $args = array(
            'tid'           => (int)$args['tid'],
            'keyword'       => $this->request->getPost()->get('keyword', null),
            'op'            => $this->request->getPost()->get('op', 'likefirst'),
            'filter'        => $this->request->getPost()->get('filter', null),
            'orderby'       => $this->request->getPost()->get('orderby', null),
            'startnum'      => $this->request->getPost()->get('startnum', null),
            'itemsperpage'  => (int)$this->request->getPost()->get('itemsperpage', $pubtype['itemsperpage']),
            'handleplugins' => $this->request->getPost()->get('handleplugins', true),
            'loadworkflow'  => $this->request->getPost()->get('loadworkflow', false),
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

    /**
     * Resequence group/pubtypes
     */
    public function treeresequence()
    {
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Clip::', '::', ACCESS_EDIT));

        // build a map of the input data
        $data = json_decode($this->request->getPost()->get('data'), true);

        $tids = array();
        $map  = array(1 => array());
        foreach ($data as $id => $item) {
            $id = explode('-', $id);
            $item['parent'] = $item['parent'] == 0 ? 1 : $item['parent'];
            if (!isset($id[1])) {
                // grouptype
                $map[$item['parent']][] = $id[0];
                $map[$id[0]] = array();
            } else {
                // pubtype
                $tids[$item['parent']][] = $id[1];
            }
        }
        unset($data);

        // build a map of the existing tree
        $grouptypes = Doctrine_Core::getTable('Clip_Model_Grouptype')->getTree()->fetchTree();

        $parents  = array(0 => 1);
        $original = array(1 => array());

        foreach ($grouptypes as $item) {
            $original[$item['gid']] = array();
            if ($item['level'] > 0) {
                $parentid = $parents[$item['level'] - 1];
                $original[$parentid][] = $item['gid'];
                // assign and link its pubtypes
                $item->order = $tids[$item['gid']];
                $item->link('pubtypes', $tids[$item['gid']]);
            }
            $parents[$item['level']] = $item['gid'];
        }
        $grouptypes->save();
        unset($grouptypes);
        unset($tids);

        // check the differences between maps
        $diffs = array();
        foreach (array_keys($original) as $gid) {
            $diffs[$gid] = array_diff($map[$gid], $original[$gid]);
        }
        $diffs = array_filter($diffs);
        $keys  = array_keys($diffs);

        // validate that there's only one change at time
        $result = true;
        if (count($keys) == 1 && count($diffs[$keys[0]]) == 1) {
            $tbl = Doctrine_Core::getTable('Clip_Model_Grouptype');

            foreach ($diffs as $gid => $diff) {
                $newpos = key($diff);
                $maxpos = count($map[$gid]) - 1;
                switch ($newpos) {
                    case 0:
                        $method = 'moveAsFirstChildOf';
                        break;
                    case $maxpos:
                        $method = 'moveAsLastChildOf';
                        break;
                    default:
                        $gid = $map[$gid][$newpos-1];
                        $method = 'moveAsNextSiblingOf';
                }
                $parent = $tbl->find($gid);
                $moved  = $tbl->find(current($diff));
                $moved->getNode()->$method($parent);
            }
        } elseif (count($keys) > 1) {
            // TODO error message because it has more than one change
            $result = false;
        }

        $result = array(
            'response' => $result
        );
        return new Zikula_Response_Ajax($result);
    }
}
