<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Lib
 */

/**
 * Clip Util for Grouptypes.
 */
class Clip_Util_Grouptypes
{
    /**
     * Get the accessible grouptypes tree array.
     *
     * @param integer $context      Context required to have access for the pubtypes (default: admin).
     * @param boolean $includeRoot  Whether or not to include the root grouptypes in the result (optional) (default: true).
     * @param boolean $includeEmpty Whether or not to include the grouptypes without pubtypes (optional) (default: false).
     * @param boolean $withPubtypes Whether or not to include the child pubtypes in the result (optional) (default: true).
     *
     * @return array Accessible tree for the current user.
     */
    public static function getTree($context = 'admin', $includeRoot = true, $includeEmpty = false, $withPubtypes = true)
    {
        $dom = ZLanguage::getModuleDomain('Clip');

        $tbl     = Doctrine_Core::getTable('Clip_Model_Grouptype');
        $treeObj = $tbl->getTree();

        if ($withPubtypes) {
            $q = $tbl->createQuery('g')
                     ->select('g.*, p.tid, p.title, p.description, p.workflow')
                     ->leftJoin('g.pubtypes p');
        } else {
            $q = $tbl->createQuery('g')->select('g.*');
        }

        $q->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY);

        // discard the root if needed
        if (!$includeRoot) {
            $q->where('g.gid > ?', 1);
        }

        $treeObj->setBaseQuery($q);

        // array hydration does not work with postHydrate hook
        $grouptypes = $treeObj->fetchTree();

        $haspubtypes = array();
        // organize the grouptype data
        foreach ($grouptypes as $k => &$group) {
            // set the localized name
            if ($group['level'] == 0) {
                $group['name'] = __('Root', $dom);
            } else {
                $group['name'] = ($group['name'] && DataUtil::is_serialized($group['name']) ? unserialize($group['name']) : $group['name']);
            }

            // set the localized description
            if ($group['level'] == 0) {
                $group['description'] = '';
            } else {
                $group['description'] = ($group['description'] && DataUtil::is_serialized($group['description']) ? unserialize($group['description']) : $group['description']);
            }

            // sort the group's pubtypes
            if ($withPubtypes && !empty($group['pubtypes'])) {
                $group['order'] = ($group['order'] && DataUtil::is_serialized($group['order']) ? unserialize($group['order']) : $group['order']);

                $group['pubtypes'] = self::sortPubtypes($group);

                $haspubtypes[] = array('lft' => $group['lft'], 'rgt' => $group['rgt']);
            }
            unset($group['order']);
        }

        // exclude the empty groups if needed
        if ($withPubtypes && !$includeEmpty) {
            foreach ($grouptypes as $k => $g) {
                if (empty($g['pubtypes'])) {
                    $skip = false;
                    // check if has a child grouptype with pubtypes
                    foreach ($haspubtypes as $c) {
                        if ($g['lft'] < $c['lft'] && $c['rgt'] < $g['rgt']) {
                            $skip = true;
                            break;
                        }
                    }
                    if (!$skip) {
                        unset($grouptypes[$k]);
                    }
                    continue;
                }
            }
        }

        // checks the permissions for the remaining ones
        foreach ($grouptypes as $k => $g) {
            if (!Clip_Access::toGrouptype($g['gid'])) {
                unset($grouptypes[$k]);
            }
            if ($withPubtypes) {
                foreach ($g['pubtypes'] as $j => $pubtype) {
                    if (!Clip_Access::toPubtype($pubtype['tid'], $context)) {
                        unset($grouptypes[$k][$j]);
                    }
                }
            }
        }

        return $grouptypes;
    }

    /**
     * Get the JavaScript for the grouptypes tree.
     *
     * @param array   $grouptypes   The grouptypes array to represent in the tree (optional).
     * @param boolean $renderRoot   Wheter to render the first item on $grouptypes (default: false).
     * @param boolean $withPubtypes Whether or not to include the child pubtypes (optional) (default: false).
     * @param array   $options      Options array for Zikula_Tree (optional (default: array()).
     * @param integer $context      Context to perform the permission checks to groptypes and pubtypes (default: admin)..
     *
     * @return string Generated tree JS text.
     */
    public static function getTreeJS($grouptypes = null, $renderRoot = false, $withPubtypes = false, array $options = array(), $context = 'admin')
    {
        if (!$grouptypes) {
            $grouptypes = self::getTree($context, true, true, $withPubtypes);
            // just for safety, disable the root grouptype of drag & drop
            $options['disabled'] = isset($options['disabled']) ? array_merge($options['disabled'], array(1)) : array(1);
        }

        $data = array();
        $leafNodes = array();
        foreach ($grouptypes as $group) {
            $data[] = self::getTreeJSNode($group, $leafNodes);
        }
        unset($grouptypes);

        $tree = new Zikula_Tree();
        $tree->setOption('objid', 'gid');
        $tree->setOption('customJSClass', 'Zikula.Clip.TreeSortable');
        $tree->setOption('nestedSet', true);
        $tree->setOption('renderRoot', $renderRoot);
        $tree->setOption('id', 'grouptypesTree');
        $tree->setOption('disabledForDrop', $leafNodes);
        if (!empty($options)) {
            $tree->setOptionArray($options);
        }
        $tree->loadArrayData($data);

        return $tree->getHTML();
    }

    /**
     * Prepare a grouptype for the tree.
     *
     * @param array $grouptype  Grouptype data.
     * @param array &$leafNodes Leaf nodes stack to disable drop.
     *
     * @return array Prepared grouptype data.
     */
    public static function getTreeJSNode($grouptype, &$leafNodes)
    {
        $dom = ZLanguage::getModuleDomain('Clip');

        $lang = ZLanguage::getLanguageCode();
        $sysl = System::getVar('language_i18n');

        // name
        if (is_array($grouptype['name'])) {
            if (isset($grouptype['name'][$lang])) {
                $grouptype['name'] = $grouptype['name'][$lang];
            } elseif ($sysl != $lang && isset($grouptype['name'][$sysl])) {
                $grouptype['name'] = $grouptype['name'][$sysl];
            } else {
                $grouptype['name'] = current($grouptype['name']);
            }
        }

        if (!$grouptype['name']) {
            $grouptype['name'] = __f('Group %s', $grouptype['gid'], $dom);
        }

        $grouptype['name'] = DataUtil::formatForDisplay($grouptype['name']);

        // description
        if (is_array($grouptype['description'])) {
            if (isset($grouptype['description'][$lang])) {
                $grouptype['description'] = $grouptype['description'][$lang];
            } elseif ($sysl != $lang && isset($grouptype['description'][$sysl])) {
                $grouptype['description'] = $grouptype['description'][$sysl];
            } else {
                $grouptype['description'] = current($grouptype['description']);
            }
        }

        $grouptype['description'] = DataUtil::formatForDisplay($grouptype['description']);

        // link title
        $grouptype['href'] = '#';

        $grouptype['title'] = array();
        $grouptype['title'][] = __('ID') . ": " . $grouptype['gid'];
        $grouptype['title'][] = __('Description') . ": " . $grouptype['description'];
        $grouptype['title'] = implode('&lt;br /&gt;', $grouptype['title']);

        $grouptype['icon'] = 'folder_open.png';
        $grouptype['class'] = 'z-tree-fixedparent';

        // eval pubtypes as nodes
        $grouptype['nodes'] = array();
        if (isset($grouptype['pubtypes']) && $grouptype['pubtypes']) {
            foreach ($grouptype['pubtypes'] as $pubtype) {
                $id = "{$grouptype['gid']}-{$pubtype['tid']}";
                $grouptype['nodes'][$id] = array(
                    'href'  => ModUtil::url('Clip', 'admin', 'pubtypeinfo', array('tid' => $pubtype['tid'])),
                    'name'  => DataUtil::formatForDisplay($pubtype['title']),
                    'title' => DataUtil::formatForDisplay($pubtype['description'])
                );
                $leafNodes[] = $id;
            }
            unset($grouptype['pubtypes']);
        }

        return $grouptype;
    }

    /**
     * Sort the pubtypes of a grouptype.
     *
     * @param array $grouptype Grouptype to sort its pubtypes.
     *
     * @return array Sorted array of pubtypes.
     */
    public static function sortPubtypes($grouptype)
    {
        $pubtypes = array();

        $last = sizeof($grouptype['order']);

        foreach ($grouptype['pubtypes'] as $pubtype) {
            $pos = array_search($pubtype['tid'], $grouptype['order']);
            if ($pos === false) {
                $pos = $last++;
            }
            $pubtypes[$pos] = $pubtype;
        }
        ksort($pubtypes);

        return $pubtypes;
    }
}
