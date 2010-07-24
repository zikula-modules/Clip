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
     * Process some core fields.
     *
     * @param array &$pubdata Publication data.
     *
     * @return void
     */
    public static function pubPostProcess(&$pubdata)
    {
        $tid = self::getTidFromTablename($pubdata['__WORKFLOW__']['obj_table']);

        $fields = array(
            'core_title' => '',
            'core_uniqueid' => "{$tid}-{$pubdata['core_pid']}",
            'core_tid' => $tid,
            'core_pid' => '',
            'core_author' => '',
            'core_creator' => ($pubdata['core_author'] == UserUtil::getVar('uid')) ? true : false,
            'core_approvalstate' => $pubdata['__WORKFLOW__']['state']
        );

        $pubdata = array_merge($fields, $pubdata);
    }

    /**
     * Temporary pre-0.9 upgrade script classnames convertor
     */
    public static function processPluginClassname($pluginclass)
    {
        if (strpos($pluginclass, 'PageMaster_') !== 0) {
            switch ($pluginclass) {
                case 'pmformcheckboxinput':
                    $pluginclass = 'Checkbox';
                    break;
                case 'pmformcustomdata':
                    $pluginclass = 'CustomData';
                    break;
                case 'pmformdateinput':
                    $pluginclass = 'Date';
                    break;
                case 'pmformemailinput':
                    $pluginclass = 'Email';
                    break;
                case 'pmformfloatinput':
                    $pluginclass = 'Float';
                    break;
                case 'pmformimageinput':
                    $pluginclass = 'Image';
                    break;
                case 'pmformintinput':
                    $pluginclass = 'Int';
                    break;
                case 'pmformlistinput':
                    $pluginclass = 'List';
                    break;
                case 'pmformmsinput':
                    $pluginclass = 'Ms';
                    break;
                case 'pmformmulticheckinput':
                    $pluginclass = 'MultiCheck';
                    break;
                case 'pmformmultilistinput':
                    $pluginclass = 'MultiList';
                    break;
                case 'pmformpubinput':
                    $pluginclass = 'Pub';
                    break;
                case 'pmformstringinput':
                    $pluginclass = 'String';
                    break;
                case 'pmformtextinput':
                    $pluginclass = 'Text';
                    break;
                case 'pmformuploadinput':
                    $pluginclass = 'Upload';
                    break;
                case 'pmformurlinput':
                    $pluginclass = 'Url';
                    break;
            }

            $pluginclass = "PageMaster_Form_Plugin_$pluginclass";
        }

        return $pluginclass;
    }

    /**
     * Extract the TID from the tablename.
     *
     * @param string $tablename
     *
     * @return integer Publication type ID.
     */
    public static function getTidFromTablename($tablename)
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
    function createOrderBy($orderby)
    {
        $orderbylist = explode(',', $orderby);
        $orderby     = '';

        foreach ($orderbylist as $key => $value) {
            if ($key > 0) {
                $orderby .= ', ';
            }
            // $value = {col[:ascdesc]}
            $value    = explode(':', $value);
            $orderby .= DataUtil::formatForStore($value[0]);
            $orderby .= (isset($value[1]) ? ' '.DataUtil::formatForStore($value[1]) : '');
        }

        return trim($orderby);
    }

    /**
     * Name reference generator.
     *
     * @return string Random id.
     */
    function getNewFileReference()
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
     * @param array   $publist   List or publication to parse.
     * @param array   $pubfields Fields of the publication type.
     * @param boolean $islist    Flag indicating that it is a list or is only one.
     *
     * @return array Parsed array.
     */
    public static function handlePluginFields($publist, $pubfields, $islist=true)
    {
        foreach ($pubfields as $fieldname => $field) {
            $pluginclass = $field['fieldplugin'];
            $plugin = PageMaster_Util::getPlugin($pluginclass);

            if (method_exists($plugin, 'postRead')) {
                if ($islist) {
                    foreach (array_keys($publist) as $key) {
                        $publist[$key][$fieldname] = $plugin->postRead($publist[$key][$fieldname], $field);
                    }
                } else {
                    $publist[$fieldname] = $plugin->postRead($publist[$fieldname], $field);
                }
            }
        }

        return $publist;
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
     * @param array  $array
     * @param string $ext
     */
    private static function _addFiles($path, &$array, $ext='php')
    {
        if (!is_dir($path) || !is_readable($path)) {
            return;
        }

        $array += FileUtil::getFiles($path, false, true, $ext, 'f');
    }

    /**
     * Available plugins list.
     *
     * @return array List of the available plugins.
     */
    public static function getPluginsOptionList()
    {
        $classNames = array();
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

        // allow final override.  since user event handlers are loaded first,
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
    public function _sortPluginList($a, $b)
    {
        return strcmp($a['plugin']->title, $b['plugin']->title);
    }

    /**
     * Plugin getter.
     *
     * @param string $pluginclass Class name of the plugin.
     *
     * @return mixed Class instance.
     */
    public static function getPlugin($pluginclass)
    {
        // temporary conversion table
        if (strpos($pluginclass, 'pmform') === 0) {
            switch ($pluginclass) {
                case 'pmformcheckboxinput':
                    $pluginclass = 'Checkbox';
                    break;
                case 'pmformcustomdata':
                    $pluginclass = 'CustomData';
                    break;
                case 'pmformdateinput':
                    $pluginclass = 'Date';
                    break;
                case 'pmformemailinput':
                    $pluginclass = 'Email';
                    break;
                case 'pmformfloatinput':
                    $pluginclass = 'Float';
                    break;
                case 'pmformimageinput':
                    $pluginclass = 'Image';
                    break;
                case 'pmformintinput':
                    $pluginclass = 'Int';
                    break;
                case 'pmformlistinput':
                    $pluginclass = 'List';
                    break;
                case 'pmformmsinput':
                    $pluginclass = 'Ms';
                    break;
                case 'pmformmulticheckinput':
                    $pluginclass = 'MultiCheck';
                    break;
                case 'pmformmultilistinput':
                    $pluginclass = 'MultiList';
                    break;
                case 'pmformpubinput':
                    $pluginclass = 'Pub';
                    break;
                case 'pmformstringinput':
                    $pluginclass = 'String';
                    break;
                case 'pmformtextinput':
                    $pluginclass = 'Text';
                    break;
                case 'pmformuploadinput':
                    $pluginclass = 'Upload';
                    break;
                case 'pmformurlinput':
                    $pluginclass = 'Url';
                    break;
            }
            $pluginclass = "PageMaster_Form_Plugin_$pluginclass";
        }

        $sm = ServiceUtil::getManager();

        if (!$sm->hasService($pluginclass)) {
            $plugin = new $pluginclass;
            $sm->attachService($pluginclass, $plugin);
        }

        return $sm->getService($pluginclass);
    }

    /**
     * PubFields getter.
     *
     * @param integer $tid     Pubtype ID.
     * @param string  $orderBy Field name to sort by.
     *
     * @return array Array of fields of one or all the loaded pubtypes.
     */
    public static function getPubFields($tid = -1, $orderBy = 'lineno')
    {
        static $pubfields_arr;

        $tid = (int)$tid;
        if ($tid != -1 && !isset($pubfields_arr[$tid])) {
            $pubfields_arr[$tid] = DBUtil::selectObjectArray('pagemaster_pubfields', "pm_tid = '$tid'", $orderBy, -1, -1, 'name');
        }

        if ($tid == -1) {
            return $pubfields_arr;
        }

        return $pubfields_arr[$tid];
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
            $pubtype_arr = DBUtil::selectObjectArray('pagemaster_pubtypes', '', 'tid', -1, -1, 'tid');
        }

        if ($tid == -1) {
            return $pubtype_arr;
        }

        return isset($pubtype_arr[(int)$tid]) ? $pubtype_arr[(int)$tid] : false;
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
            $pubtitles_arr = DBUtil::selectFieldArray('pagemaster_pubfields', 'name', "pm_istitle = '1'", '', false, 'tid');
        }

        if ($tid == -1) {
            return $pubtitles_arr;
        }

        return isset($pubtitles_arr[(int)$tid]) ? $pubtitles_arr[(int)$tid] : false;
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

        foreach (array_keys($pubfields) as $i) {
            if ($pubfields[$i]['istitle'] == 1) {
                $core_title = $pubfields[$i]['name'];
                break;
            }
        }

        return $core_title;
    }
}
