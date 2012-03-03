<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage View_Plugins
 */

/**
 * Generic Form Value.
 * Clip's interface to map a value on a publication input.
 *
 * Available parameters:
 *  - alias       (string)  Form data alias
 *  - tid         (integer) Pubtype's ID (defaults to current pubtype)
 *  - rid         (integer) Publication ID (defaults to form's publication ID)
 *  - pid         (integer) Publication PID (defaults to form's publication PID)
 *  - field       (string)  Pubtype's field name.
 *  - mandatory   (bool)    Whether this form field is mandatory.
 *  - maxLength   (integer) Maximum lenght of the input (optional).
 *  - fieldplugin (string)  Override the field plugin ID
 *  - fieldconfig (string)  Configuration for the field when fieldplugin is used.
 *  - pluginclass (string)  Override the plugin Clip class.
 *
 * Examples:
 *
 *  <samp>{clip_form_value field='myvalue1' fieldplugin='List' category=$categoryID mandatory=true}</samp>
 * 
 *  <samp>{clip_form_value field='value2' fieldplugin='Text' maxLength=10}</samp>
 *
 * @param array            $params All parameters passed to this plugin from the template.
 * @param Zikula_Form_View $view   Reference to the {@link Zikula_Form_View} object.
 *
 * @return mixed Plugin output.
 */
function smarty_function_clip_form_value($params, Zikula_Form_View &$render)
{
    if (!isset($params['field']) || !$params['field']) {
        $render->trigger_error($render->__f('Error! Missing argument [%s].', 'field'));
    }

    if (!isset($params['pluginclass']) && !isset($params['fieldplugin'])) {
        $render->trigger_error($render->__f('Error! Missing argument [%s].', 'pluginclass | fieldplugin'));
    }

    if ($params['field'] == 'id') {
        $render->trigger_error($render->__f("Error! '%1\$s' parameter cannot be '%2\$s'.", array('field', 'id')));
    }

    // clip data handling
    $params['alias'] = isset($params['alias']) && $params['alias'] ? $params['alias'] : $render->get_registered_object('clip_form')->getAlias();
    $params['tid']   = isset($params['tid']) && $params['tid'] ? $params['tid'] : (int)$render->get_registered_object('clip_form')->getTid();
    $params['rid']   = isset($params['rid']) && $params['rid'] ? $params['rid'] : $render->get_registered_object('clip_form')->getId();
    $params['pid']   = isset($params['pid']) && $params['pid'] ? $params['pid'] : $render->get_registered_object('clip_form')->getPid($render);

    // form framework parameters adjustment
    $params['alias'] = $params['alias'];
    $params['id']    = "clip_{$params['alias']}_{$params['tid']}_{$params['rid']}_{$params['pid']}_{$params['field']}";
    $params['group'] = 'clipdata';

    $field = Clip_Util::getPubFieldData($params['tid'], $params['field']);

    if ($field) {
        $render->trigger_error($render->__f("Error! '%1\$s' parameter cannot be '%2\$s'.", array('field', DataUtil::formatForDisplay($params['field']))).' '.$render->__('It must not match any existing field.'));
    }

    // check if there's a custom plugin class to use
    if (isset($params['pluginclass'])) {
        $pluginclass = $params['pluginclass'];
        unset($params['pluginclass']);

        // treat the single-word classes as Clip's ones
        if (strpos($pluginclass, '_') === false) {
            $pluginclass = 'Clip_Form_Plugin_'.$pluginclass;
        }

        // validate that the class exists
        if (!class_exists($pluginclass)) {
            $render->trigger_error($render->__f('Error! The specified plugin class [%s] does not exists.', $pluginclass));
        }

        // check if it's needed to remove some parameters
        $vars = array_keys(get_class_vars($pluginclass));

        if (!in_array('maxLength', $vars)) {
            unset($params['maxLength']);
        }
        if (!in_array('mandatory', $vars)) {
            unset($params['mandatory']);
        }

        $plugin = new $pluginclass($render, $params);

    } else if (isset($params['fieldplugin'])) {
        // plugin class and configuration customization
        $pluginclass = $params['fieldplugin'];
        // be sure there's a config specified or reset to empty
        $params['fieldconfig'] = isset($params['fieldconfig']) ? $params['fieldconfig'] : '';
        // unset them
        unset($params['fieldplugin']);

        // field plugin class
        $plugin = Clip_Util_Plugins::get($pluginclass);

        // check if it's needed to remove some parameters
        $vars = array_keys(get_object_vars($plugin));

        if (!in_array('maxLength', $vars)) {
            unset($params['maxLength']);
        }
        if (!in_array('mandatory', $vars)) {
            unset($params['mandatory']);
        }
    }

    // register plugin
    if (method_exists($plugin, 'pluginRegister')) {
        return $plugin->pluginRegister($params, $render);
    } else {
        return $render->registerPlugin(get_class($plugin), $params);
    }
}
