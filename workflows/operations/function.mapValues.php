<?php/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://github.com/zikula-modules/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Workflows_Operations
 */
/**
 * mapValues operation.
 *
 * @param object $pub    Publication to change update.
 * @param array  $params Value(s) to map in the publication record.
 *
 * @return boolean True.
 */
function Clip_operation_mapValues(&$pub, $params)
{
    $dom = ZLanguage::getModuleDomain('Clip');
    // unset the always present nextstate parameter
    unset($params['nextstate']);
    // initializes the result flag
    $result = true;
    // map the values into the record
    // they should not exist in the record already
    foreach ($params as $key => $val) {
        if (!$pub->contains($key)) {
            $pub->mapValue($key, $val);
        }
    }
    // returns the operation result
    return $result;
}