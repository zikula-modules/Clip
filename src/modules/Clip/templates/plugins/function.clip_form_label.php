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
 * Generic Form Label.
 * Clip's interface to build the label for a form plugin.
 *
 * Available parameters:
 *  - tid          (integer) Pubtype's ID (defaults to current pubtype)
 *  - pid          (integer) Publication ID (defaults to form's publication ID)
 *  - field        (string)  Pubtype's field name.
 *  - mandatorysym (bool)    Whether this form field is mandatory.
 *
 * Examples:
 *
 *  <samp>{clip_form_label for='title' mandatorysym=true}</samp>
 *  <samp>{clip_form_label for='title' __text='Title' mandatorysym=true}</samp>
 *
 * @param array            $params All parameters passed to this plugin from the template.
 * @param Zikula_Form_View $view   Reference to the {@link Zikula_Form_View} object.
 *
 * @return mixed Plugin output.
 */
function smarty_function_clip_form_label($params, Zikula_Form_View &$render)
{
    if (!isset($params['for']) || !$params['for']) {
        $render->trigger_error($render->__f('Error! Missing argument [%s].', 'for'));
    }

    // clip data handling
    $alias = isset($params['alias']) && $params['alias'] ? $params['alias'] : $render->get_registered_object('clip_form')->getAlias();
    $tid   = isset($params['tid']) && $params['tid'] ? $params['tid'] : (int)$render->get_registered_object('clip_form')->getTid();
    $rid   = isset($params['rid']) && $params['rid'] ? $params['rid'] : $render->get_registered_object('clip_form')->getId();
    $pid   = isset($params['pid']) && $params['pid'] ? $params['pid'] : $render->get_registered_object('clip_form')->getPid($render);

    // form framework parameter adjustment
    $params['for'] = "clip_{$alias}_{$tid}_{$rid}_{$pid}_{$params['for']}";

    return $render->registerPlugin('Zikula_Form_Plugin_Label', $params);
}
