<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c), Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id: modifier.array.php 27067 2009-10-21 17:20:35Z drak $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package PageMaster_Template_Plugins
 * @subpackage Plugins
 */

/**
 * Smarty plugin to display an array
 *
 * Example
 *   <!--[pmarray array=$myarray]--> prints the array in a friendly way
 *
 * @author       Mateo Tibaquira
 * @since        30 January 2010
 * @param        array     $array     the array to display
 * @return       string    the friendly output
 */
function smarty_function_pmarray($array)
{
    $array = array_values($array);

    // removes a useless "\n1" ending
    return substr(DataUtil::formatForDisplayHTML(print_r($array[0])), 0, -2);
}
