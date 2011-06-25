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
 *  {clip_form_block id='title' mandatory=true}
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
    if (!$params['id']) {
        return LogUtil::registerError($render->__f('Error! Missing argument [%s].', 'id'));
    }

    $tid = $render->eventHandler->getTid();

    $pubfields = Clip_Util::getPubFields($tid);

    // read settings in pubfields, if set by template ignore settings in pubfields
    if (!isset($params['mandatory'])){
        $params['mandatory'] = $pubfields[$params['id']]['ismandatory'];
    }
    if (!isset($params['maxLength'])){
        $params['maxLength'] = $pubfields[$params['id']]['fieldmaxlength'];
    }

    $plugin = Clip_Util_Plugins::get($pubfields[$params['id']]['fieldplugin']);

    if (method_exists($plugin, 'blockRegister')) {
        return $plugin->blockRegister($params, $render, $content);
    } else {
        return $render->registerBlock(get_class($plugin), $params, $content);
    }
}
