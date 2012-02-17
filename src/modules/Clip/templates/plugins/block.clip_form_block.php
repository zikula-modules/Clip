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
 * Generic Form Plugin block.
 * Clip's interface to load a block form plugin.
 *
 * Available parameters:
 *  - alias     (string)  Form data alias
 *  - tid       (integer) Pubtype's ID (defaults to current pubtype)
 *  - rid       (integer) Publication ID (defaults to form's publication ID)
 *  - pid       (integer) Publication PID (defaults to form's publication PID)
 *  - field     (string)  Pubtype's field id (name).
 *  - mandatory (bool)    Whether this form field is mandatory.
 *  - maxLength (bool)    Maximum lenght of the input (optional).
 *
 * Example:
 *
 *  <samp>
 *  {clip_form_block field='title' mandatory=true}
 *    ... subplugins + content ...
 *  {/clip_form_block}
 *  </samp>
 *
 * @param array            $params All parameters passed to this plugin from the template.
 * @param Zikula_Form_View $view   Reference to the {@link Zikula_Form_View} object.
 *
 * @return mixed Plugin output.
 */
function smarty_block_clip_form_block($params, $content, Zikula_Form_View &$render)
{
    if (!isset($params['field']) || !$params['field']) {
        $render->trigger_error($render->__f('Error! Missing argument [%s].', 'field'));
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
    $params['id']    = "clip_{$params['alias']}_{$params['tid']}_{$params['rid']}_{$params['pid']}_{$params['field']}";
    $params['group'] = 'clipdata';

    $field = Clip_Util::getPubFieldData($params['tid'], $params['field']);

    if (!$field) {
        $render->trigger_error($render->__f("Error! The publication field '%s' does not exist.", DataUtil::formatForDisplay($params['field'])));
    }

    // use the main settings if not explicitly declared on the template
    if (!isset($params['mandatory'])){
        $params['mandatory'] = $field['ismandatory'];
    }
    if (!isset($params['maxLength'])){
        $params['maxLength'] = $field['fieldmaxlength'];
    }

    // setup the main field configuration
    $params['fieldconfig'] = $field['typedata'];

    // plugin instance
    $plugin = Clip_Util_Plugins::get($field['fieldplugin']);

    // check if it's needed to remove some parameters
    $vars = array_keys(get_object_vars($plugin));

    if (!in_array('maxLength', $vars)) {
        unset($params['maxLength']);
    }
    if (!in_array('mandatory', $vars)) {
        unset($params['mandatory']);
    }

    // register block
    if (method_exists($plugin, 'blockRegister')) {
        return $plugin->blockRegister($params, $render, $content);
    } else {
        return $render->registerBlock(get_class($plugin), $params, $content);
    }
}
