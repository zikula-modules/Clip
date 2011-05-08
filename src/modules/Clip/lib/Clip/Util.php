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
 * Clip Util.
 */
class Clip_Util
{
    /**
     * Arguments store.
     *
     * @var array
     */
    protected static $args = array();

    /**
     * Retrieves the available plugins.
     *
     * @param string $id Retrieve the classname for this id.
     *
     * @return array|string Plugins class names list indexed by ID.
     */
    public static function getPluginClasses($id = null)
    {
        static $classNames;

        if (isset($classNames)) {
            if (!is_null($id)) {
                return isset($classNames[$id]) ? $classNames[$id] : '';
            }
            return $classNames;
        }

        $classNames = array(
            'Checkbox'   => 'Clip_Form_Plugin_Checkbox',
            'Date'       => 'Clip_Form_Plugin_Date',
            'Email'      => 'Clip_Form_Plugin_Email',
            'Float'      => 'Clip_Form_Plugin_Float',
            'Image'      => 'Clip_Form_Plugin_Image',
            'Int'        => 'Clip_Form_Plugin_Int',
            'List'       => 'Clip_Form_Plugin_List',
            'Ms'         => 'Clip_Form_Plugin_Ms',
            'MultiCheck' => 'Clip_Form_Plugin_MultiCheck',
            'MultiList'  => 'Clip_Form_Plugin_MultiList',
            'RadioList'  => 'Clip_Form_Plugin_RadioList',
            'String'     => 'Clip_Form_Plugin_String',
            'Text'       => 'Clip_Form_Plugin_Text',
            'Upload'     => 'Clip_Form_Plugin_Upload',
            'Url'        => 'Clip_Form_Plugin_Url',
            'User'       => 'Clip_Form_Plugin_User'
        );

        // collect classes from other providers also allows for override
        $event = new Zikula_Event('clip.get_field_plugin_classes');
        $event->setData($classNames);
        $classNames = EventUtil::getManager()->notify($event)->getData();

        // allow final override. since user event handlers are loaded first,
        // we have to dispatch a separate event - drak
        $event = new Zikula_Event('clip.get_field_plugin_classes.overrides');
        $event->setData($classNames);
        $classNames = EventUtil::getManager()->notify($event)->getData();

        if (!is_null($id)) {
            return isset($classNames[$id]) ? $classNames[$id] : '';
        }

        return $classNames;
    }

    /**
     * Plugin getter.
     *
     * @param string $pluginClass Class name of the plugin.
     *
     * @return mixed Class instance.
     */
    public static function getPlugin($pluginID)
    {
        $dom = ZLanguage::getModuleDomain('Clip');

        $sm = ServiceUtil::getManager();

        if (!$sm->hasService("clip.plugin.$pluginID")) {
            $view = self::newUserForm();

            $pluginClass = self::getPluginClasses($pluginID);
            if (!$pluginClass) {
                throw new InvalidArgumentException(__f('Plugin ID [%s] not found in the available plugins.', $pluginID, $dom));
            }

            $params = array();
            $plugin = new $pluginClass($view, $params);
            if (!$plugin instanceof Zikula_Form_AbstractPlugin) {
                throw new InvalidArgumentException(__f('Plugin [%s] must be an instance of Zikula_Form_AbstractPlugin.', $pluginClass, $dom));
            }
            $plugin->setup();

            $sm->attachService("clip.plugin.$pluginID", $plugin);
        }

        return $sm->getService("clip.plugin.$pluginID");
    }

    /**
     * User form instance builder
     *
     * @param Zikula_Controller $controller
     * @see FormUtil::newForm
     *
     * @return Clip_Form_View User Form View instance.
     */
    public static function newUserForm(&$controller=null)
    {
        $serviceManager = ServiceUtil::getManager();
        $serviceId      = 'zikula.view.form.clip';

        if (!$serviceManager->hasService($serviceId)) {
            $view = new Clip_Form_View($serviceManager, 'Clip');
            $view->add_core_data();
            $serviceManager->attachService($serviceId, $view);
        } else {
            $view = $serviceManager->getService($serviceId);
        }

        if ($controller) {
            $view->setController($controller);
            $view->assign('controller', $controller);
        }

        return $view;
    }

    /**
     * Extract the filter from the input.
     *
     * @param string $varname Name of the filter variable on use.
     * @param string $default Default filter to use.
     *
     * @see FilterUtil::getFiltersFromInput
     *
     * @return integer Publication type ID.
     */
    public static function getFiltersFromInput($default = '', $varname = 'filter')
    {
        $i = 1;
        $filter = array();

        // Get unnumbered filter string
        $filterStr = FormUtil::getPassedValue($varname, '');
        if (!empty($filterStr)) {
            $filter[] = $filterStr;
        }

        // Get filter1 ... filterN
        while (true) {
            $filterStr = FormUtil::getPassedValue("{$varname}{$i}", '');

            if (empty($filterStr)) {
                break;
            }

            $filter[] = $filterStr;
            ++$i;
        }

        if (count($filter) > 0) {
            $filter = "(" . implode(')*(', $filter) . ")";
        }

        return $filter;
    }

    /**
     * Generates the current user's groups string ID.
     *
     * @return string String identified based on the group memberships.
     */
    public static function getUserGIdentifier()
    {
        static $id;

        if (!isset($id)) {
            $gids = UserUtil::getGroupsForUser(UserUtil::getVar('uid'));
            sort($gids);
            $id = 'g_'.implode('_', $gids);
        }

        return $id;
    }

    /**
     * Extract the TID from a string end.
     *
     * @param string $tablename
     *
     * @return integer Publication type ID.
     */
    public static function getDefaultCategoryID()
    {
        static $id;

        if (!isset($id)) {
            $id = CategoryRegistryUtil::getRegisteredModuleCategory('Clip', 'clip_pubtypes', 'Global');
        }

        return $id;
    }

    /**
     * Extract the TID from a string end.
     *
     * @param string $tablename
     *
     * @return integer Publication type ID.
     */
    public static function getTidFromString($tablename)
    {
        $tid = '';
        while (is_numeric(substr($tablename, -1))) {
            $tid = substr($tablename, -1) . $tid;
            $tablename = substr($tablename, 0, strlen($tablename) - 1);
        }

        return $tid;
    }

    /**
     * Removes any numerical suffix of a string.
     *
     * @param string $string
     *
     * @return string String without numeric suffix.
     */
    public static function getStringPrefix($string)
    {
        $suffixnumber = self::getTidFromString($string);

        return str_replace($suffixnumber, '', $string);
    }

    /**
     * Format the orderby parameter.
     *
     * @param string $orderby
     *
     * @return string Formatted orderby.
     */
    public static function createOrderBy($orderby)
    {
        if (!is_array($orderby)) {
            $orderbylist = explode(',', $orderby);
        } else {
            $orderbylist = $orderby;
        }

        $orderbylist = array_map('trim', $orderbylist);

        $orderby     = '';
        foreach ($orderbylist as $key => $value) {
            if ($key > 0) {
                $orderby .= ', ';
            }
            // $value = {col[:asc|desc]}
            $value    = explode(':', $value);
            $orderby .= DataUtil::formatForStore($value[0]);
            $orderby .= (isset($value[1]) && in_array(strtoupper($value[1]), array('ASC', 'DESC')) ? ' '.strtoupper($value[1]) : '');
        }

        return $orderby;
    }

    /**
     * Name reference generator.
     *
     * @return string Random id.
     */
    public static function getNewFileReference()
    {
        $chars   = '0123456789abcdefghijklmnopqrstuvwxyz';
        $charLen = strlen($chars);

        $id = '';

        for ($i = 0; $i < 30; ++ $i) {
            $id .= $chars[mt_rand(0, $charLen-1)];
        }

        return $id;
    }

    /**
     * Parser of fields with the plugins postRead.
     *
     * @param object|array $obj Collection or publication to parse.
     *
     * @return array Parsed array.
     */
    public static function handlePluginFields(&$obj)
    {
        $tid    = 0;
        $islist = false;

        // detects if it is a list or a publication
        // and extracts the pubtype ID
        if (is_object($obj)) {
            if ($obj instanceof Doctrine_Record) {
                $tid = $obj['core_tid'];
            } elseif ($obj instanceof Doctrine_Collection) {
                $islist = true;
                $pub = $obj->getFirst();
                $tid = $pub['core_tid'];
            }
        } elseif (!empty($obj)) {
            if (isset($obj['core_tid'])) {
                $tid = $obj['core_tid'];
            } else {
                $islist = true;
                $keys = array_keys($obj);
                $tid = isset($obj[$keys[0]]['core_tid']) ? $obj[$keys[0]]['core_tid'] : 0;
            }
        }

        // if we haven't a tid
        // probably we got an empty object
        if (empty($tid)) {
            return;
        }

        // process the fields
        $pubfields = Clip_Util::getPubFields($tid);

        foreach ($pubfields as $fieldname => $field) {
            $plugin = Clip_Util::getPlugin($field['fieldplugin']);

            if (method_exists($plugin, 'postRead')) {
                if ($islist) {
                    foreach ($obj as &$pub) {
                        $pub[$fieldname] = $plugin->postRead($pub[$fieldname], $field);
                    }
                } else {
                    $obj[$fieldname] = $plugin->postRead($obj[$fieldname], $field);
                }
            }
        }
    }

    /**
     * Handler of the order criteria.
     *
     * @param string $orderby   Orderbt parameter.
     * @param array  $pubfields Publication type fields.
     * @param string $tbl_alias Alias of the main table.
     *
     * @return string Parsed order parameter.
     */
    public static function handlePluginOrderBy($orderby, $pubfields, $tbl_alias)
    {
        if (!empty($orderby)) {
            $orderby_arr = explode(',', $orderby);
            $orderby_new = '';

            foreach ($orderby_arr as $orderby_field) {
                $orderby_field = trim($orderby_field);
                if (strpos($orderby_field, ' ') === false) {
                    $orderby_field .= ' ';
                }
                list($orderby_col, $orderby_dir) = explode(' ', $orderby_field);
                $plugin_name = '';
                $field_name  = '';

                foreach ($pubfields as $fieldname => $field) {
                    if (strtolower($fieldname) == strtolower($orderby_col)) {
                        $plugin_name = $field['fieldplugin'];
                        $field_name  = $field['name'];
                        break;
                    }
                }
                if (!empty($plugin_name)) {
                    $plugin = Clip_Util::getPlugin($plugin_name);
                    if (method_exists($plugin, 'orderBy')) {
                        $orderby_col = $plugin->orderBy($field_name, $tbl_alias);
                    } else {
                        $orderby_col = $tbl_alias.$orderby_col;
                    }
                } else {
                    $orderby_col = $orderby_col;
                }
                $orderby_new .= $orderby_col.' '.$orderby_dir.',';
            }
            $orderby = substr($orderby_new, 0, -1);
        }

        return $orderby;
    }

    /**
     * Available workflows getter.
     *
     * @return array List of available workflows for form options.
     */
    public static function getWorkflowsOptionList()
    {
        $workflows = array();

        $path = 'modules/Clip/workflows';
        self::_addFiles($path, $workflows, 'xml');

        $path = 'config/workflows/Clip';
        self::_addFiles($path, $workflows, 'xml');

        foreach ($workflows as $k => $v) {
            $workflows[$k] = array(
                'text'  => $v,
                'value' => $v
            );
        }

        return $workflows;
    }

    /**
     * Private folder read method.
     *
     * @param string $path
     * @param array  &$array
     * @param string $ext
     *
     * @return void
     */
    private static function _addFiles($path, &$array, $ext='php')
    {
        if (!is_dir($path) || !is_readable($path)) {
            return;
        }

        $files = FileUtil::getFiles($path, false, true, $ext, 'f');
        $array = array_merge($array, $files);
    }

    /**
     * Export/import formats selector generator.
     *
     * @param boolean $includeempty Include en ampty entry at the beggining (default: true).
     *
     * @return array Array of text, values to be used in a selector.
     */
    public static function getFormatsSelector($includeempty = true)
    {
        $array = array();

        if ($includeempty) {
            $array['empty'] = array(
                'text'  => '',
                'value' => ''
            );
        }

        $formats = array('xml'/*, 'cvs', 'xls'*/);

        foreach ($formats as $format) {
            $array[$format] = array(
                'text'  => strtoupper($format),
                'value' => $format
            );
        }

        $array = array_values(array_filter($array));

        uasort($array, 'Clip_Util::_sortListByTitle');

        return $array;
    }

    /**
     * Publication types selector generator.
     *
     * @param boolean $includetid Include the TID in the texts (default: false).
     * @param boolean $includeempty Include en ampty entry at the beggining (default: true).
     *
     * @return array Array of text, values to be used in a selector.
     */
    public static function getPubtypesSelector($includetid = false, $includeempty = true)
    {
        $dom = ZLanguage::getModuleDomain('Clip');

        $pubtypes = Clip_Util::getPubType(-1);

        $array = array();

        if ($includeempty) {
            $array['core_empty'] = array(
                'text'  => '',
                'value' => ''
            );
        }

        foreach ($pubtypes as $tid => $pubtype) {
            $array[$tid] = array(
                'text'  => __($pubtype['title'], $dom).($includetid ? " ($tid)" : ''),
                'value' => $tid
            );
        }

        $array = array_values(array_filter($array));

        uasort($array, 'Clip_Util::_sortListByTitle');

        return $array;
    }

    /**
     * Field selector generator.
     *
     * @param integer $tid
     *
     * @return array Array of text, values to be used in a selector.
     */
    public static function getFieldsSelector($tid, $includeempty = true)
    {
        $dom = ZLanguage::getModuleDomain('Clip');

        $pubfields = Clip_Util::getPubFields($tid);

        $arraysort = array(
            'core_empty' => array(),
            'core_title' => array(),
            'core_cr_date' => array(),
            'core_pu_date' => array(),
            'core_hitcount' => array()
        );

        $array = array(
            'core_empty' => array(
                'text'  => '',
                'value' => ''
            ),
            'core_cr_date' => array(
                'text'  => __('Creation date', $dom),
                'value' => 'cr_date'
            ),
            'core_lu_date' => array(
                'text'  => __('Update date', $dom),
                'value' => 'lu_date'
            ),
            'core_cr_uid' => array(
                'text'  => __('Creator', $dom),
                'value' => 'core_author'
            ),
            'core_lu_uid' => array(
                'text'  => __('Updater', $dom),
                'value' => 'lu_uid'
            ),
            'core_pu_date' => array(
                'text'  => __('Publish date', $dom),
                'value' => 'core_publishdate'
            ),
            'core_ex_date' => array(
                'text'  => __('Expire date', $dom),
                'value' => 'core_expiredate'
            ),
            'core_language' => array(
                'text'  => __('Language', $dom),
                'value' => 'core_language'
            ),
            'core_hitcount' => array(
                'text'  => __('Number of reads', $dom),
                'value' => 'core_hitcount'
            )
        );

        foreach ($pubfields as $fieldname => $pubfield) {
            $index = ($pubfield['istitle'] == 1) ? 'core_title' : $fieldname;
            $array[$index] = array(
                'text'  => __($pubfield['title'], $dom),
                'value' => $fieldname
            );
        }

        if (!$includeempty) {
            unset($array['core_empty']);
        }

        $array = array_values(array_filter(array_merge($arraysort, $array)));

        return $array;
    }

    /**
     * Internal comparision criteria.
     *
     * @param array $a Element a to compare.
     * @param array $b Element b to compare.
     *
     * @return integer Comparision result.
     */
    public static function _sortListByTitle($a, $b)
    {
        return strcmp($a['text'], $b['text']);
    }

    /**
     * Available plugins list.
     *
     * @return array List of the available plugins.
     */
    public static function getPluginsOptionList()
    {
        $availablePlugins = self::getPluginClasses();

        $plugins = array();
        foreach ($availablePlugins as $id => $className) {
            $plugin = Clip_Util::getPlugin($id);
            $plugins[$id] = array(
                'plugin' => $plugin,
                'class'  => $className,
            );
        }

        uasort($plugins, 'Clip_Util::_sortPluginList');

        return $plugins;
    }

    /**
     * Internal plugin comparision criteria.
     *
     * @param array $a Element a to compare.
     * @param array $b Element b to compare.
     *
     * @return integer Comparision result. 
     */
    public static function _sortPluginList($a, $b)
    {
        return strcmp($a['plugin']->pluginTitle, $b['plugin']->pluginTitle);
    }

    /**
     * PubType getter.
     *
     * @param integer $tid Pubtype ID.
     *
     * @return Clip_Model_Pubtype Information of one or all the pubtypes.
     */
    public static function getPubType($tid = -1)
    {
        static $pubtypes;

        if (!isset($pubtypes)) {
            $pubtypes = Doctrine_Core::getTable('Clip_Model_Pubtype')->getPubtypes();
        }

        if ($tid == -1) {
            return $pubtypes;
        }

        $keys = array_keys($pubtypes->toArray());
        foreach ($keys as $key) {
            $pubtype = self::getPubTypeSub($pubtypes[$key], $tid);
            if ($pubtype != null) {
                return $pubtype;
            }
        }

        $null = null;
        return $null;
    }

    /* utility function to return the pubtype reference */
    private static function getPubTypeSub(&$pubtype, $tid)
    {
        if ($pubtype['tid'] == $tid) {
            return $pubtype;
        }

        $null = null;
        return $null;
    }

    /**
     * Rerlation getter.
     *
     * @param integer $tid Pubtype ID.
     * @param boolean $owningSide Wheter to fetch the owning side relations of the pubtype.
     *
     * @return array Relations for the passed pubtype.
     */
    public static function getRelations($tid = -1, $owningSide = true, $force = false)
    {
        static $relation_arr;

        if (!isset($relation_arr) || $force) {
            $relation_arr = Doctrine_Core::getTable('Clip_Model_Pubrelation')->getClipRelations();
        }

        $own = $owningSide ? 'own' : 'not';

        if ($tid == -1) {
            return $relation_arr[$own];
        }

        return isset($relation_arr[$own][$tid]) ? $relation_arr[$own][$tid] : array();
    }

    /**
     * PubFields getter.
     *
     * @param integer $tid     Pubtype ID.
     * @param string  $orderBy Field name to sort by.
     *
     * @return array Array of fields of one or all the loaded pubtypes.
     */
    public static function getPubFields($tid, $orderBy = 'lineno')
    {
        static $pubfields_arr;

        $tid = (int)$tid;
        if ($tid && !isset($pubfields_arr[$tid])) {
            $pubfields_arr[$tid] = Doctrine_Core::getTable('Clip_Model_Pubfield')
                                   ->selectCollection("tid = '$tid'", $orderBy, -1, -1, 'name');
        }

        return isset($pubfields_arr[$tid]) ? $pubfields_arr[$tid] : array();
    }

    /**
     * Title field getter.
     *
     * @param integer $tid Pubtype ID.
     *
     * @return array One or all the pubtype titles.
     */
    public static function getTitleField($tid = -1)
    {
        static $pubtitles_arr;

        if (!isset($pubtitles_arr)) {
            $pubtitles_arr = Doctrine_Core::getTable('Clip_Model_Pubfield')
                             ->selectFieldArray('name', "istitle = '1'", '', false, 'tid');
        }

        if ($tid == -1) {
            return $pubtitles_arr;
        }

        return isset($pubtitles_arr[(int)$tid]) ? $pubtitles_arr[(int)$tid] : 'id';
    }

    /**
     * Loop the pubfields array until get the title field.
     *
     * @param array $pubfields
     *
     * @return string Name of the title field.
     */
    public static function findTitleField($pubfields)
    {
        $core_title = 'id';

        if ($pubfields instanceof Doctrine_Collection) {
            $pubfields = $pubfields->toArray();
        }

        foreach (array_keys($pubfields) as $i) {
            if ($pubfields[$i]['istitle'] == 1) {
                $core_title = $pubfields[$i]['name'];
                break;
            }
        }

        return $core_title;
    }

    /**
     * self::$args getter and setter
     */
    public static function getArgs($id=null)
    {
        $args = self::$args;
        self::$args = array();

        if ($id && isset($args[$id])) {
            return $args[$id];
        }

        return $args;
    }

    public static function setArgs($id, $args)
    {
        self::$args[$id] = $args;
    }

    /**
     * Validates a TID number.
     *
     * @param integer $tid
     *
     * @return boolean True on valid publication type, false otherwise.
     */
    public static function validateTid($tid)
    {
        if (is_numeric($tid) && $tid > 0 && self::getPubType($tid)) {
            return true;
        }

        return false;
    }

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
    public static function getGrouptypesTreeJS($grouptypes = null, $withPubtypes = false, $sortable = false, array $options = array())
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
            $data[] = self::getGrouptypesTreeJSNode($group, $leafNodes);
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
    public static function getGrouptypesTreeJSNode($grouptype, &$leafNodes)
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
                    'href'  => ModUtil::url('Clip', 'admin', 'main', array('tid' => $pubtype['tid'])),
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
     * Install the default 'blog' and 'staticpages' publication types.
     *
     * @return void
     */
    public static function installDefaultypes()
    {
        $dom = ZLanguage::getModuleDomain('Clip');

        $lang  = ZLanguage::getLanguageCode();
        $batch = new Clip_Import_Batch();

        $defaults = array('blog', 'staticpages');

        foreach ($defaults as $default) {
            // check if the pubtype exists
            $pubtype = Doctrine_Core::getTable('Clip_Model_Pubtype')->findByUrltitle($default);
            if (count($pubtype)) {
                LogUtil::registerStatus(__f("There is already a '%s' publication type.", $default, $dom));
            } else {
                // import the default XML
                $file = "modules/Clip/docs/xml/$lang/$default.xml";
                if (!file_exists($file)) {
                    $file = "modules/Clip/docs/xml/en/$default.xml";
                }

                if ($batch->setup(array('url' => $file)) && $batch->execute()) {
                    LogUtil::registerStatus(__f("'%s' publication type created successfully.", $default, $dom));
                } else {
                    LogUtil::registerStatus(__f("Could not import the '%s' publication type.", $default, $dom));
                }
            }
        }
    }
}
