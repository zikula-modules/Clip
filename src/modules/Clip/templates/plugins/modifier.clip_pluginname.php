<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage View_Modifiers
 */

/**
 * Clip modifier to translate the plugin name.
 *
 * @param string $pluginname The name to process.
 *
 * @return string Translation of the plugin name.
 */
function smarty_modifier_clip_pluginname($pluginname)
{
    $dom = ZLanguage::getModuleDomain('Clip');

    // TODO event asking the translation
    $pluginname = str_replace('Clip_Form_Plugin_', '', $pluginname);

    return __($pluginname, $dom);
}
