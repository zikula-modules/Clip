<?php
/**
 * PageMaster
 *
 * @copyright   (c) PageMaster Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package     Zikula_3rdParty_Modules
 * @subpackage  pagemaster
 */

/**
 * PageMaster Util.
 */
class PageMaster_Util
{
    /**
     * Temporary pre-0.9 upgrade script classnames convertor
     */
    public static function processPluginClassname($pluginClass)
    {
        if (strpos($pluginClass, 'PageMaster_') !== 0) {
            switch ($pluginClass) {
                case 'pmformcheckboxinput':
                    $pluginClass = 'Checkbox';
                    break;
                case 'pmformcustomdata':
                    $pluginClass = 'CustomData';
                    break;
                case 'pmformdateinput':
                    $pluginClass = 'Date';
                    break;
                case 'pmformemailinput':
                    $pluginClass = 'Email';
                    break;
                case 'pmformfloatinput':
                    $pluginClass = 'Float';
                    break;
                case 'pmformimageinput':
                    $pluginClass = 'Image';
                    break;
                case 'pmformintinput':
                    $pluginClass = 'Int';
                    break;
                case 'pmformlistinput':
                    $pluginClass = 'List';
                    break;
                case 'pmformmsinput':
                    $pluginClass = 'Ms';
                    break;
                case 'pmformmulticheckinput':
                    $pluginClass = 'MultiCheck';
                    break;
                case 'pmformmultilistinput':
                    $pluginClass = 'MultiList';
                    break;
                case 'pmformpubinput':
                    $pluginClass = 'Pub';
                    break;
                case 'pmformstringinput':
                    $pluginClass = 'String';
                    break;
                case 'pmformtextinput':
                    $pluginClass = 'Text';
                    break;
                case 'pmformuploadinput':
                    $pluginClass = 'Upload';
                    break;
                case 'pmformurlinput':
                    $pluginClass = 'Url';
                    break;
            }

            $pluginClass = "PageMaster_Form_Plugin_$pluginClass";
        }

        return $pluginClass;
    }

    /**
     * Extract the TID from a string end.
     *
     * @param string $tablename
     *
     * @return integer Publication type ID.
     */
    public static function getTidFromStringSuffix($tablename)
    {
        $tid = '';
        while (is_numeric(substr($tablename, -1))) {
            $tid = substr($tablename, -1) . $tid;
            $tablename = substr($tablename, 0, strlen($tablename) - 1);
        }

        return $tid;
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
        } else {
            if (isset($obj['core_tid'])) {
                $tid = $obj['core_tid'];
            } else {
                $islist = true;
                $keys = array_keys($obj);
                $tid = $obj[$keys[0]]['core_tid'];
            }
        }

        $pubfields = PageMaster_Util::getPubFields($tid);

        foreach ($pubfields as $fieldname => $field) {
            $plugin = PageMaster_Util::getPlugin($field['fieldplugin']);

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
                    $plugin = PageMaster_Util::getPlugin($plugin_name);
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

        $path = 'modules/PageMaster/workflows';
        self::_addFiles($path, $workflows, 'xml');

        $path = 'config/workflows/PageMaster';
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

        $array += FileUtil::getFiles($path, false, true, $ext, 'f');
    }

    /**
     * Publication types selector generator.
     *
     * @param integer $tid
     *
     * @return array Array of text, values to be used in a selector.
     */
    public static function getPubtypesSelector($includetid = false, $includeempty = true)
    {
        $dom = ZLanguage::getModuleDomain('PageMaster');

        $pubtypes = PageMaster_Util::getPubType(-1);

        $array = array();

        if ($includeempty) {
            $array['core_empty'] = array(
                'text'  => '',
                'value' => ''
            );
        }

        foreach ($pubtypes as $tid => $pubtype) {
            $array[$tid] = array(
                'text'  => __($pubtype['title'], $dom).($includetid ? " (tid $tid)" : ''),
                'value' => $tid
            );
        }

        $array = array_values(array_filter($array));

        uasort($array, 'PageMaster_Util::_sortListByTitle');

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
        $dom = ZLanguage::getModuleDomain('PageMaster');

        $pubfields = PageMaster_Util::getPubFields($tid);

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
        $classNames = array();
        $classNames['Checkbox']   = 'PageMaster_Form_Plugin_Checkbox';
        $classNames['Date']       = 'PageMaster_Form_Plugin_Date';
        $classNames['Email']      = 'PageMaster_Form_Plugin_Email';
        $classNames['Float']      = 'PageMaster_Form_Plugin_Float';
        $classNames['Image']      = 'PageMaster_Form_Plugin_Image';
        $classNames['Int']        = 'PageMaster_Form_Plugin_Int';
        $classNames['List']       = 'PageMaster_Form_Plugin_List';
        $classNames['Ms']         = 'PageMaster_Form_Plugin_Ms';
        $classNames['MultiCheck'] = 'PageMaster_Form_Plugin_MultiCheck';
        $classNames['MultiList']  = 'PageMaster_Form_Plugin_MultiList';
        $classNames['Pub']        = 'PageMaster_Form_Plugin_Pub';
        $classNames['String']     = 'PageMaster_Form_Plugin_String';
        $classNames['Text']       = 'PageMaster_Form_Plugin_Text';
        $classNames['Upload']     = 'PageMaster_Form_Plugin_Upload';
        $classNames['Url']        = 'PageMaster_Form_Plugin_Url';

        // collect classes from other providers also allows for override
        $event = new Zikula_Event('pagemaster.get_field_plugin_classes');
        $event->setData($classNames);
        $classNames = EventUtil::getManager()->notify($event)->getData();

        // allow final override. since user event handlers are loaded first,
        // we have to dispatch a separate event - drak
        $event = new Zikula_Event('pagemaster.get_field_plugin_classes.overrides');
        $event->setData($classNames);
        $classNames = EventUtil::getManager()->notify($event)->getData();

        $plugins = array();
        foreach ($classNames as $name => $className) {
            $plugin = PageMaster_Util::getPlugin($className);
            $plugins[$name] = array(
                'plugin' => $plugin,
                'class'  => $className,
            );
        }

        uasort($plugins, 'PageMaster_Util::_sortPluginList');

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
     * Plugin getter.
     *
     * @param string $pluginClass Class name of the plugin.
     *
     * @return mixed Class instance.
     */
    public static function getPlugin($pluginClass)
    {
        // temporary 0.4.x conversion table
        $pluginClass = self::processPluginClassname($pluginClass);

        $pluginName = strtolower(substr($pluginClass, strrpos($pluginClass, '_') + 1));

        $sm = ServiceUtil::getManager();

        if (!$sm->hasService("pagemaster.plugin.$pluginName")) {
            $view = Zikula_View::getInstance('PageMaster');

            $params = array();
            $plugin = new $pluginClass($view, $params);
            if (!$plugin instanceof Form_Plugin) {
                throw new InvalidArgumentException(__f('Plugin %s must be an instance of Form_Plugin', $pluginName));
            }
            $plugin->setup();

            $sm->attachService("pagemaster.plugin.$pluginName", $plugin);
        }

        return $sm->getService("pagemaster.plugin.$pluginName");
    }

    /**
     * PubType getter.
     *
     * @param integer $tid Pubtype ID.
     *
     * @return array Information of one or all the pubtypes.
     */
    public static function getPubType($tid = -1)
    {
        static $pubtype_arr;

        if (!isset($pubtype_arr)) {
            $pubtype_arr = Doctrine_Core::getTable('PageMaster_Model_Pubtype')->getPubtypes();
        }

        if ($tid == -1) {
            return $pubtype_arr;
        }

        return isset($pubtype_arr[(int)$tid]) ? $pubtype_arr[(int)$tid] : false;
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
            $relation_arr = Doctrine_Core::getTable('PageMaster_Model_Pubrelation')->getRelations();
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
            $pubfields_arr[$tid] = Doctrine_Core::getTable('PageMaster_Model_Pubfield')
                                   ->selectCollection("tid = '$tid'", $orderBy, -1, -1, 'name');
        }

        return isset($pubfields_arr[$tid]) ? $pubfields_arr[$tid] : null;
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
            $pubtitles_arr = Doctrine_Core::getTable('PageMaster_Model_Pubfield')
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
}
