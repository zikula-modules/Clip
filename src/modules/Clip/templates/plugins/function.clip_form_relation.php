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
 * Form Plugin to handle a pubtype's relation Autocompleter.
 *
 * Available parameters:
 *  - alias       (string)  Form data alias
 *  - tid         (integer) Pubtype's ID (defaults to current pubtype)
 *  - rid         (integer) Publication ID (defaults to form's publication ID)
 *  - pid         (integer) Publication PID (defaults to form's publication PID)
 *  - field       (string)  Pubtype's field id (name).
 *  - pluginclass (string)  Clip relation plugin class to use.
 *
 * @param array            $params All parameters passed to this plugin from the template.
 * @param Zikula_Form_View $render Reference to the {@link Zikula_Form_View} object.
 *
 * @return mixed False on failure, or the HTML output.
 */
function smarty_function_clip_form_relation($params, Zikula_Form_View &$render)
{
    if (!isset($params['field']) || !$params['field']) {
        $render->trigger_error($render->__f('Error! Missing argument [%s].', 'field'));
    }

    // clip data handling
    $params['alias'] = isset($params['alias']) && $params['alias'] ? $params['alias'] : $render->get_registered_object('clip_form')->getAlias();
    $params['tid']   = isset($params['tid']) && $params['tid'] ? $params['tid'] : (int)$render->get_registered_object('clip_form')->getTid();
    $params['rid']   = isset($params['rid']) && $params['rid'] ? $params['rid'] : $render->get_registered_object('clip_form')->getId();
    $params['pid']   = isset($params['pid']) && $params['pid'] ? $params['pid'] : $render->get_registered_object('clip_form')->getPid($render);

    // form framework parameters adjustment
    $params['id']    = "cliprel_{$params['alias']}_{$params['tid']}_{$params['rid']}_{$params['pid']}_{$params['field']}";
    $params['group'] = 'clipdata';

    // resolve classname
    $classname = isset($params['pluginclass']) ? $params['pluginclass'] : 'Autocompleter';

    // treat the single-word classes as Clip's ones
    if (strpos($classname, '_') === false) {
        $classname = 'Clip_Form_Plugin_Relations_'.$classname;
    }

    // validate that the class exists
    if (!class_exists($classname)) {
        $render->trigger_error($render->__f('Error! The specified plugin class [%s] does not exists.', $classname));
    }
    unset($params['pluginclass']);

    // register plugin
    return $render->registerPlugin($classname, $params);
}
