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
 *  - id        (string) Pubtype's field id (name).
 *  - mandatory (bool) Whether this form field is mandatory.
 *  - maxLength (bool) Maximum lenght of the input (optional).
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

    // clip data handling
    $params['alias'] = isset($params['alias']) && $params['alias'] ? $params['alias'] : $render->get_registered_object('clip_form')->getAlias();
    $params['tid']   = isset($params['tid']) && $params['tid'] ? $params['tid'] : (int)$render->get_registered_object('clip_form')->getTid();
    $params['pid']   = isset($params['pid']) && $params['pid'] ? $params['pid'] : $render->get_registered_object('clip_form')->getId();

    // form framework parameters adjustment
    $params['id'] = "clip_{$params['alias']}_{$params['tid']}_{$params['pid']}_{$params['field']}";
    $params['group'] = 'clipdata';

    $field = Clip_Util::getPubFieldData($params['tid'], $params['field']);

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

    // register block
    if (method_exists($plugin, 'blockRegister')) {
        return $plugin->blockRegister($params, $render, $content);
    } else {
        return $render->registerBlock(get_class($plugin), $params, $content);
    }
}
