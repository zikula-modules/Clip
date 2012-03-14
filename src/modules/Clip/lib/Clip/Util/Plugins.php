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
 * Clip Util for Plugins.
 */
class Clip_Util_Plugins
{
    /**
     * Retrieves the available plugins.
     *
     * @param string $id Retrieve the classname for this id.
     *
     * @return array|string Plugins class names list indexed by ID.
     */
    public static function getClasses($id = null)
    {
        static $classNames;

        if (isset($classNames)) {
            if (!is_null($id)) {
                return isset($classNames[$id]) ? $classNames[$id] : '';
            }

            return $classNames;
        }

        $classNames = array(
            'BigInt'     => 'Clip_Form_Plugin_BigInt',
            'Checkbox'   => 'Clip_Form_Plugin_Checkbox',
            'Date'       => 'Clip_Form_Plugin_Date',
            'Email'      => 'Clip_Form_Plugin_Email',
            'Float'      => 'Clip_Form_Plugin_Float',
            'Image'      => 'Clip_Form_Plugin_Image',
            'Int'        => 'Clip_Form_Plugin_Int',
            'Language'   => 'Clip_Form_Plugin_Language',
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
    public static function get($pluginID)
    {
        $dom = ZLanguage::getModuleDomain('Clip');

        $sm = ServiceUtil::getManager();

        if (!$sm->hasService("clip.plugin.$pluginID")) {
            $view = Clip_Util::newForm();

            $pluginClass = self::getClasses($pluginID);

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
     * Parser of Record/Collection fields with the plugins postRead.
     *
     * @param object|array $obj Collection or publication to parse.
     *
     * @return array Parsed array.
     */
    public static function postRead(&$obj)
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
            $plugin = self::get($field['fieldplugin']);

            if (method_exists($plugin, 'postRead')) {
                if ($islist) {
                    foreach ($obj as &$pub) {
                        $plugin->postRead($pub, $field);
                    }
                } else {
                    $plugin->postRead($obj, $field);
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
    public static function handleOrderBy($orderby, $pubfields, $tbl_alias)
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
                    $plugin = self::get($plugin_name);
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

    public static function getCoreFieldData($name, $field = null)
    {
        $corefield = array();
        $corefield['ismandatory'] = false;
        $corefield['fieldmaxlength'] = null;
        $corefield['typedata'] = '';

        switch ($name)
        {
            case 'core_urltitle':
                $corefield['fieldplugin'] = 'String';
                break;

            case 'core_language':
                $corefield['fieldplugin'] = 'Language';
                break;

            case 'core_pid':
            case 'core_hitcount':
            case 'core_revision':
                $corefield['fieldplugin'] = 'Int';
                break;

            case 'core_creator':
            case 'core_author':
                $corefield['fieldplugin'] = 'User';
                break;

            case 'core_publishdate':
            case 'core_expiredate':
                $corefield['fieldplugin'] = 'Date';
                $corefield['typedata'] = '1';
                break;

            case 'core_visible':
            case 'core_locked':
                $corefield['fieldplugin'] = 'Checkbox';
                break;
        }

        if ($field) {
            return isset($corefield[$field]) ? $corefield[$field] : null;
        }

        return $corefield;
    }
}
