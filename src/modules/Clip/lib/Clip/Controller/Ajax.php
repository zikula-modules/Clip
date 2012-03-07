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
    public function __call($func, $args)
    {
        $this->checkAjaxToken();

        // try to get a method checking both controllers
        $response = false;
        if (in_array($func, array('pubtypeinfo', 'pubtype', 'pubfields', 'relations', 'generator'))) {
            $response = ModUtil::func('Clip', 'admin', $func, $args[0]);
        } elseif (method_exists('Clip_Controller_User', $func)) {
            $response = ModUtil::func('Clip', 'user', $func, $args[0]);
        }

        $this->throwNotFoundUnless($response);

        if (!is_object($response) || !$response instanceof Zikula_Response_Ajax_AbstractBase) {
            $response = new Zikula_Response_Ajax_Plain($response);
        }

        return $response;
    }

    public function editgroup()
    {
        $this->checkAjaxToken();

        $mode = $this->request->getPost()->get('mode', 'add');
        // FIXME SECURITY check this
        $this->throwForbiddenUnless(Clip_Access::toClip($mode == 'edit' ? ACCESS_EDIT : ACCESS_ADD));

        $gid    = $this->request->getPost()->get('gid', 0);
        $pos    = $this->request->getPost()->get('pos', 'root');
        $parent = $this->request->getPost()->get('parent', 1);

        if ($mode == 'edit') {
            // edit mode of an existing item
            if (!$gid) {
                return new Zikula_Response_Ajax_BadData($this->__f("Error! Cannot determine valid '%1$s' for edit in '%2$s'.", array('gid', 'editgroup')));
            }
            $group = Doctrine_Core::getTable('Clip_Model_Grouptype')->find($gid);
            $this->throwNotFoundUnless($group, $this->__('Sorry! No such group found.'));
        } else {
            // new item mode
            $group = new Clip_Model_Grouptype();
            $group->mapValue('parent', $parent);
        }

        Zikula_AbstractController::configureView();
        $this->view->setCaching(Zikula_View::CACHE_DISABLED);

        $this->view->assign('mode', $mode)
                   ->assign('pos', $pos)
                   ->assign('group', $group)
                   ->assign('languages', ZLanguage::getInstalledLanguages());

        $result = array(
            'action' => $mode,
            'result' => $this->view->fetch('clip_ajax_groupedit.tpl')
        );
        return new Zikula_Response_Ajax($result);
    }

    /**
     * Resequence group/pubtypes
     */
    public function treeresequence()
    {
        $this->checkAjaxToken();
        // FIXME SECURITY check this
        $this->throwForbiddenUnless(Clip_Access::toClip(ACCESS_EDIT));

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
        $diffs  = array();
        $udiffs = array();
        foreach (array_keys($original) as $gid) {
            $diffs[$gid]  = array_diff($map[$gid], $original[$gid]);
            $udiffs[$gid] = array_diff_assoc($map[$gid], $original[$gid]);
        }

        $result = true;

        // for move between trees
        $diffs = array_filter($diffs);
        if (count($diffs)) {
            $keys = array_keys($diffs);
            // validate that there's only one change at time
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
                    $refer = $tbl->find($gid);
                    $moved = $tbl->find(current($diff));
                    $moved->getNode()->$method($refer);
                }
            } elseif (count($keys) > 1) {
                // TODO error message because it has more than one change
                $result = false;
            }
        }

        // for leaf reorder
        $udiffs = array_filter($udiffs);
        if (count($udiffs) == 1) {
            // validate that there's only one change at time
            $tbl = Doctrine_Core::getTable('Clip_Model_Grouptype');

            foreach ($udiffs as $gid => $udiff) {
                $maxpos = count($original[$gid]) - 1;
                // check the first item
                $ufirst = reset($udiff);
                $kfirst = key($udiff);
                $pfirst = array_search($ufirst, $original[$gid]);
                // check the last item
                $ulast = end($udiff);
                $klast = key($udiff);
                $plast = array_search($ulast, $original[$gid]);
                if ($pfirst == $maxpos || $original[$gid][$pfirst+1] != $udiff[$kfirst+1]) {
                    // check if it was the last one or moved up
                    $rel = $udiff[$kfirst+1];
                    $gid = $ufirst;
                    $method = 'moveAsPrevSiblingOf';
                } elseif ($plast == 0 || $original[$gid][$plast-1] != $udiff[$klast-1]) {
                    // check if it was the first or the original order doesn't match
                    $rel = $udiff[$klast-1];
                    $gid = $ulast;
                    $method = 'moveAsNextSiblingOf';
                }
                $refer = $tbl->find($rel);
                $moved = $tbl->find($gid);
                $moved->getNode()->$method($refer);
            }
        } elseif (count($udiffs) > 1) {
            // TODO error message because it has more than one change
            $result = false;
        }

        $result = array(
            'response' => $result
        );
        return new Zikula_Response_Ajax($result);
    }

    /**
     * Increment a counter inside a publication.
     *
     * Allows to download a file optionally.
     *
     * @param integer $args['tid']   ID of the publication type.
     * @param integer $args['pid']   ID of the publication.
     * @param integer $args['id']    ID of the publication revision (optional if pid is used).
     * @param string  $args['count'] Field to increment the count (optional).
     * @param string  $args['field'] Field to download (optional).
     *
     * @return Publication output.
     */
    public function count($args)
    {
        //// Token check
        $this->checkCsrfToken($this->request->getGet()->get('csrftoken', null));

        //// Pubtype
        // validate and get the publication type first
        $args['tid'] = isset($args['tid']) ? $args['tid'] : FormUtil::getPassedValue('tid');

        if (!Clip_Util::validateTid($args['tid'])) {
            return LogUtil::registerError($this->__f('Error! Invalid publication type ID passed [%s].', DataUtil::formatForDisplay($args['tid'])));
        }

        $pubtype = Clip_Util::getPubType($args['tid']);

        //// Parameters
        // define the arguments
        $apiargs = array(
            'tid'           => $args['tid'],
            'pid'           => isset($args['pid']) ? $args['pid'] : FormUtil::getPassedValue('pid'),
            'id'            => isset($args['id']) ? $args['id'] : FormUtil::getPassedValue('id'),
            'checkperm'     => false,
            'handleplugins' => true,
            'loadworkflow'  => false,
            'rel'           => array()
        );
        $args = array(
            'count' => isset($args['count']) ? $args['count'] : FormUtil::getPassedValue('count'),
            'field' => isset($args['field']) ? $args['field'] : FormUtil::getPassedValue('field')
        );

        //// Validation
        // validate the passed fields
        $record = $pubtype->getPubInstance();

        if ($args['count']) {
            if (!$record->isPubField($args['count'])) {
                return LogUtil::registerError($this->__('Error! Invalid field requested.'));
            }

            if (!Clip_Util::getPubFieldData($apiargs['tid'], $args['count'], 'iscounter')) {
                return LogUtil::registerError($this->__('Error! Invalid field to increment was passed.'));
            }
        }

        if ($args['field'] && !$record->isPubField($args['field'])) {
            return LogUtil::registerError($this->__('Error! Invalid field requested.'));
        }

        if (!$args['field'] && !$args['count']) {
            return LogUtil::registerError($this->__('Error! Invalid request.'));
        }

        // required the publication ID or record ID
        if ((empty($apiargs['pid']) || !is_numeric($apiargs['pid'])) && (empty($apiargs['id']) || !is_numeric($apiargs['id']))) {
            return LogUtil::registerError($this->__f('Error! Missing or wrong argument [%s].', 'id | pid'));
        }

        // get the pid if it was not passed
        if (empty($apiargs['pid'])) {
            $apiargs['pid'] = ModUtil::apiFunc('Clip', 'user', 'getPid', $apiargs);
        }

        //// Security
        $this->throwForbiddenUnless(Clip_Access::toPub($pubtype, $apiargs['pid'], $apiargs['id'], 'display'));

        // setup an admin flag
        $isadmin = Clip_Access::toPubtype($pubtype);

        //// Execution
        // fill the conditions of the item to get
        $apiargs['where'] = array();
        if (!Clip_Access::toPubtype($apiargs['tid'], 'editor')) {
            $apiargs['where'][] = array('core_online = ?', 1);
            $apiargs['where'][] = array('core_intrash = ?', 0);
        }

        // get the publication from the database
        $pubdata = ModUtil::apiFunc('Clip', 'user', 'get', $apiargs);

        if (!$pubdata) {
            if ($isadmin) {
                // detailed error message for the admin only
                return LogUtil::registerError($this->__f('No such publication [tid: %1$s - pid: %2$s; id: %3$s] found.', array($apiargs['tid'], $apiargs['pid'], $apiargs['id'])));
            } else {
                return LogUtil::registerError($this->__('No such publication found.'));
            }
        }

        // process the request
        if ($args['field']) {
            // there's a download requested
            if (!is_array($pubdata[$args['field']]) || !isset($pubdata[$args['field']]['file_name'])) {
                return LogUtil::registerError($this->__('Error! Invalid field requested.'));
            }

            $fileinfo = $pubdata[$args['field']];
            $basepath = ModUtil::getVar('Clip', 'uploadpath');
            $filepath = $basepath.'/'.$fileinfo['file_name'];

            if (!$fileinfo['file_name'] || !file_exists($filepath)) {
                return LogUtil::registerError($this->__('The requested file does not exists.'));
            }

            $output = new Clip_Util_Response_Download($filepath, $fileinfo['orig_name']);
        }

        // check if there's a field requested to increment
        if ($args['count']) {
            Doctrine_Core::getTable('ClipModels_Pubdata'.$apiargs['tid'])->incrementFieldBy($args['count'], $apiargs['pid'], 'core_pid');

            // check if there was no download
            if (!isset($output)) {
                $result = array(
                    'response' => true
                );
                $output = new Zikula_Response_Ajax($result);
            }
        }

        //// Output
        // return the response object
        return $output;
    }
}
