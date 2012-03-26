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
 * Clip Util for Selectors.
 */
class Clip_Util_Selectors
{
    /**
     * Available workflows getter.
     *
     * @return array List of available workflows for form options.
     */
    public static function workflows()
    {
        $workflows = array();

        $path = 'modules/Clip/workflows';
        self::addFiles($path, $workflows, 'xml');

        $path = 'config/workflows/Clip';
        self::addFiles($path, $workflows, 'xml');

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
    private static function addFiles($path, &$array, $ext='php')
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
    public static function exportFormats($includeempty = true)
    {
        $array = array();

        if ($includeempty) {
            $array['empty'] = array(
                'text'  => '',
                'value' => ''
            );
        }

        // TODO implement the other ones
        $formats = array('xml'/*, 'cvs', 'xls'*/);

        foreach ($formats as $format) {
            $array[$format] = array(
                'text'  => strtoupper($format),
                'value' => $format
            );
        }

        $array = array_values(array_filter($array));

        uasort($array, 'Clip_Util_Selectors::sortByTitle');

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
    public static function pubtypes($includetid = false, $includeempty = true)
    {
        $dom = ZLanguage::getModuleDomain('Clip');

        $pubtypes = Clip_Util::getPubType()->toKeyValueArray('tid', 'title');

        // build the selector
        $array = array();

        if ($includeempty) {
            $array['core_empty'] = array(
                'text'  => '',
                'value' => ''
            );
        }

        foreach ($pubtypes as $tid => $title) {
            $array[$tid] = array(
                'text'  => $title.($includetid ? " ($tid)" : ''),
                'value' => $tid
            );
        }

        return array_values(array_filter($array));
    }

    /**
     * Field selector generator.
     *
     * @param integer $tid
     *
     * @return array Array of text, values to be used in a selector.
     */
    public static function fields($tid, $includeempty = true)
    {
        $dom = ZLanguage::getModuleDomain('Clip');

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

        if (Clip_Util::validateTid($tid)) {
            $pubfields = Clip_Util::getPubFields($tid);

            foreach ($pubfields as $fieldname => $pubfield) {
                $index = ($pubfield['istitle'] == 1) ? 'core_title' : $fieldname;
                $array[$index] = array(
                    'text'  => $pubfield['title'],
                    'value' => $fieldname
                );
            }
        }

        if (!$includeempty) {
            unset($array['core_empty']);
        }

        return array_values(array_filter(array_merge($arraysort, $array)));
    }

    /**
     * Internal comparision criteria.
     *
     * @param array $a Element a to compare.
     * @param array $b Element b to compare.
     *
     * @return integer Comparision result.
     */
    public static function sortByTitle($a, $b)
    {
        return strcmp($a['text'], $b['text']);
    }

    /**
     * Available plugins list.
     *
     * @return array List of the available plugins.
     */
    public static function plugins()
    {
        $availablePlugins = Clip_Util_Plugins::getClasses();

        $plugins = array();
        foreach ($availablePlugins as $id => $className) {
            $plugin = Clip_Util_Plugins::get($id);
            $plugins[] = array(
                'text'  => $plugin,
                'value' => $id,
            );
        }

        uasort($plugins, 'Clip_Util_Selectors::sortPlugins');

        foreach ($plugins as $id => $plugin) {
            $plugins[$id]['text'] = $plugin['text']->pluginTitle;
        }

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
    public static function sortPlugins($a, $b)
    {
        return strcmp($a['text']->pluginTitle, $b['text']->pluginTitle);
    }
}
