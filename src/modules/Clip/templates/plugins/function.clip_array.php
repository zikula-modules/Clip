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
 * Smarty plugin to display an array
 *
 * Example
 *   <!--[clip_array array=$myarray]--> prints the array in a friendly way
 *
 * @author       Mateo Tibaquira
 * @since        30 January 2010
 * @param        array     $array     the array to display
 * @return       string    the friendly output
 */
function smarty_function_clip_array($array)
{
    $array = array_values($array);

    // removes a useless "\n1" ending
    return substr(DataUtil::formatForDisplayHTML(print_r($array[0])), 0, -2);
}
