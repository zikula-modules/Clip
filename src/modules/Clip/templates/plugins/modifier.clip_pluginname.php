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
 * @param string $pluginID The name to process.
 *
 * @return string Translation of the plugin name.
 */
function smarty_modifier_clip_pluginname($pluginID)
{
    $plugin = Clip_Util::getPlugin($pluginID);

    return $plugin->pluginTitle;
}
