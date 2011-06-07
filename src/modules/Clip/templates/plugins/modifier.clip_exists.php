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
 * Clip modifier to check if a Doctrine Collection/Record exists.
 *
 * Example:
 *
 *  <samp>{if $pubdata.relation|clip_exists}</samp>
 *
 * @param object $object Doctrine object to process.
 *
 * @return boolean True if exists and not empty, false otherwise.
 */
function smarty_modifier_clip_translate($data, $lang=null)
{
    if (!is_object($data)) {
        return $data;
    }

    if ($data instanceof Doctrine_Collection) {
        return count($data);

    } elseif ($data instanceof Doctrine_Record) {
        return $data->exists();
    }
}
