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
 * Clip modifier to translate a data array.
 *
 * Example:
 *
 *  <samp>{$title|clip_translate}</samp>
 *
 * @param array  $data Data to process.
 * @param string $lang Forced language instead the user's language.
 *
 * @return string Localized string found in the data.
 */
function smarty_modifier_clip_translate($data, $lang=null)
{
    if (!is_array($data)) {
        return $data;
    }

    // TODO pending implementation
    return $data;
}
