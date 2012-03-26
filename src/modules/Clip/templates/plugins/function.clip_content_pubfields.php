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
 * ContentType Plugin.
 * Clip plugin to support the pubfields selector on Content Types.
 *
 * Example:
 *
 *  <samp>{clip_content_pubfields id='orderby' group='data'}</samp>
 *
 * @param array            $params All parameters passed to this plugin from the template.
 * @param Zikula_Form_View $view   Reference to the {@link Zikula_Form_View} object.
 *
 * @return mixed Plugin output.
 */
function smarty_function_clip_content_pubfields($params, Zikula_Form_View $render)
{
    return $render->registerPlugin('Clip_Form_Plugin_Content_Pubfields', $params);
}
