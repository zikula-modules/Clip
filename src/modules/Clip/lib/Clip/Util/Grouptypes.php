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
     * Get the JavaScript for the grouptypes tree.
     *
     * @param array   $grouptypes    The grouptypes array to represent in the tree.
     * @param boolean $doReplaceRoot Whether or not to replace the root name with a localized string (optional) (default=true).
     * @param boolean $sortable      Sets the zikula tree option sortable (optional) (default=false).
     * @param array   $options       Options array for Zikula_Tree.
     *
     * @return generated tree JS text.
     */
    public static function getTreeJS($grouptypes = null, $withPubtypes = false, $sortable = false, array $options = array())
    {
        $dom = ZLanguage::getModuleDomain('Clip');

        $renderRoot = true;
        if (!$grouptypes) {
            $tbl     = Doctrine_Core::getTable('Clip_Model_Grouptype');
            $treeObj = $tbl->getTree();

            if ($withPubtypes) {
                $q = $tbl->createQuery('g')
                         ->select('g.*, p.tid, p.title, p.description')
                         ->leftJoin('g.pubtypes p');
                $treeObj->setBaseQuery($q);
            }

            // Array hydration does not work with postHydrate hook
            $grouptypes = $treeObj->fetchTree()->toArray();

            $renderRoot = false;
        }

        $data = array();
        $leafNodes = array();
        foreach ($grouptypes as $group) {
            if ($group['level'] == 0) {
                $group['name'] = __('Root', $dom);
            }
            $data[] = self::getTreeJSNode($group, $leafNodes);
        }
        unset($grouptypes);

        $tree = new Zikula_Tree();
        $tree->setOption('objid', 'gid');
        $tree->setOption('customJSClass', 'Zikula.Clip.TreeSortable');
        $tree->setOption('nestedSet', true);
        $tree->setOption('renderRoot', $renderRoot);
        $tree->setOption('id', 'grouptypesTree');
        $tree->setOption('sortable', $sortable);
        // disable drag and drop for root category
        $tree->setOption('disabled', array(1));
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
     * @param array $grouptype Grouptype data.
     *
     * @return Prepared grouptype data.
     */
    public static function getTreeJSNode($grouptype, &$leafNodes)
    {
        $dom  = ZLanguage::getModuleDomain('Clip');
        $lang = ZLanguage::getLanguageCode();
        $sysl = System::getVar('language_i18n');

        // name
        if (is_string($grouptype['name'])) {
            $grouptype['name'] = DataUtil::formatForDisplay($grouptype['name']);
        } elseif (isset($grouptype['name'][$lang]) && !empty($grouptype['name'][$lang])) {
            $grouptype['name'] = DataUtil::formatForDisplay($grouptype['name'][$lang]);
        } elseif ($lang != $sysl && isset($grouptype['name'][$sysl]) && !empty($grouptype['name'][$sysl])) {
            $grouptype['name'] = DataUtil::formatForDisplay($grouptype['name'][$sysl]);
        } else {
            $grouptype['name'] = __f('Group ID [%s]', $grouptype['gid'], $dom);
        }

        // description
        if (isset($grouptype['description'][$lang]) && !empty($grouptype['description'][$lang])) {
            $grouptype['description'] = DataUtil::formatForDisplay($grouptype['description'][$lang]);
        } elseif ($lang != $sysl && isset($grouptype['description'][$sysl]) && !empty($grouptype['description'][$sysl])) {
            $grouptype['description'] = DataUtil::formatForDisplay($grouptype['description'][$sysl]);
        } else {
            $grouptype['description'] = '';
        }

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
            $pubtypes = array();

            $last = 100000;
            foreach ($grouptype['pubtypes'] as $pubtype) {
                $pos = array_search($pubtype['tid'], $grouptype['order']);
                if ($pos === false) {
                    $pos = $last++;
                }
                $pubtypes[$pos] = $pubtype;
            }
            ksort($pubtypes);

            foreach ($pubtypes as $pubtype) {
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
}
