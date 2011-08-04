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
function smarty_modifier_clip_exists($data, $q=null)
{
    if (!is_object($data)) {
        return false;
    }

    $exists = false;

    if ($data instanceof Doctrine_Collection) {
        $exists = (bool)count($data);

    } elseif ($data instanceof Doctrine_Record) {
        $exists = $data->exists();
    }

    if ($exists && $q && in_array($q, array('one', 'many'))) {
        switch ($q) {
            case 'many':
                return $data instanceof Doctrine_Collection;
            case 'one':
                return $data instanceof Doctrine_Record;
        }
    }

    return $exists;
}
