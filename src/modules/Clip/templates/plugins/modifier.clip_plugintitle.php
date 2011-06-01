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
 * Clip modifier to show the translated plugin name.
 * For internal use: Clip's Admin Panel > Pubfields.
 *
 * Example:
 *
 *  <samp>{$plugin|clip_plugintitle}</samp>
 *
 * @param string $pluginID Name of the plugin.
 *
 * @return string Translated plugin title.
 */
function smarty_modifier_clip_plugintitle($pluginID)
{
    $plugin = Clip_Util_Plugins::get($pluginID);

    return $plugin->pluginTitle;
}
