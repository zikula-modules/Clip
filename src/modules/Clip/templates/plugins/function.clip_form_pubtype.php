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
 * Plugin Selector of the Clip's available pubtypes.
 *
 * @param array            $params All parameters passed to this plugin from the template.
 * @param Zikula_Form_View $render Reference to the {@link Zikula_Form_View} object.
 *
 * @return mixed Plugin output.
 */
function smarty_function_clip_form_pubtype($params, Zikula_Form_View &$render)
{
    return $render->registerPlugin('Clip_Form_Pubtype', $params);
}
