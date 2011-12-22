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
class Clip_Controller_Ajaxexec extends Zikula_Controller_AbstractAjax
{
    public function _postSetup()
    {
        // no need for a Zikula_View so override it.
    }

    public function changelistorder()
    {
        $this->checkAjaxToken();
        // FIXME SECURITY check this
        $this->throwForbiddenUnless(Clip_Access::toClip(ACCESS_ADMIN));

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

        // update the model with the sorted fields
        Clip_Generator::updateModel($tid);

        return array('result' => true);
    }

    public function savegroup()
    {
        $this->checkAjaxToken();

        $mode = $this->request->getPost()->get('mode', 'new');
        // FIXME SECURITY check this
        $this->throwForbiddenUnless(Clip_Access::toClip($mode == 'edit' ? ACCESS_EDIT : ACCESS_ADD));

        $pos  = $this->request->getPost()->get('pos', 'root');
        $data = $this->request->getPost()->get('group');

        if ($mode == 'add') {
            $method = 'insertAsLastChildOf';
            switch ($pos) {
                case 'root':
                    $data['parent'] = 1;
                    break;
                case 'after':
                    $method = 'insertAsNextSiblingOf';
                    break;
            }
            $parent = Doctrine_Core::getTable('Clip_Model_Grouptype')->find($data['parent']);

            $group = new Clip_Model_Grouptype();
            $group->fromArray($data);
            $group->gid = (int)$data['gid'];
            $group->getNode()->$method($parent);
        } else {
            $group = Doctrine_Core::getTable('Clip_Model_Grouptype')->find($data['gid']);
            $this->throwNotFoundUnless($group, $this->__('Sorry! No such group found.'));

            $group->synchronizeWithArray($data);
            $group->save();
        }

        $node    = array($group->toArray());
        $options = array('withWraper' => false, 'sortable' => true);
        $nodejscode = Clip_Util_Grouptypes::getTreeJS($node, true, true, $options);

        $result = array(
            'action' => $mode,
            'pos'    => $pos,
            'gid'    => $group['gid'],
            'parent' => $data['parent'],
            'node'   => $nodejscode,
            'result' => true
        );
        return new Zikula_Response_Ajax($result);
    }

    public function deletegroup()
    {
        $this->checkAjaxToken();
        // FIXME SECURITY check this
        $this->throwForbiddenUnless(Clip_Access::toClip(ACCESS_DELETE));

        $gid   = $this->request->getPost()->get('gid');
        $group = Doctrine_Core::getTable('Clip_Model_Grouptype')->find($gid);
        $this->throwNotFoundUnless($group, $this->__('Sorry! No such group found.'));

        $group->getNode()->delete();

        $result = array(
            'action' => 'delete',
            'gid'    => $gid,
            'result' => true
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
        $gids = array(1 => array());
        foreach ($data as $id => $item) {
            $id = explode('-', $id);
            $item['parent'] = $item['parent'] == 0 ? 1 : $item['parent'];
            if (!isset($id[1])) {
                // grouptype
                $gids[$item['parent']][] = $id[0];
                $gids[$id[0]] = array();
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
                $item->save();
            }
            $parents[$item['level']] = $item['gid'];
        }
        unset($grouptypes);
        unset($tids);

        // check the differences between maps
        $diffs  = array();
        $udiffs = array();
        foreach (array_keys($original) as $gid) {
            $diffs[$gid]  = array_diff((array)$gids[$gid], $original[$gid]);
            $udiffs[$gid] = array_diff_assoc($gids[$gid], $original[$gid]);
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
                    $maxpos = count($gids[$gid]) - 1;
                    switch ($newpos) {
                        case 0:
                            $method = 'moveAsFirstChildOf';
                            break;
                        case $maxpos:
                            $method = 'moveAsLastChildOf';
                            break;
                        default:
                            $gid = $gids[$gid][$newpos-1];
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
}
