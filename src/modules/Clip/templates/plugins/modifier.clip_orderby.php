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
 * Clip modifier to process the orderby string
 * and return the corresponding link CSS class or revert parameter.
 *
 * Examples
 *
 *   {'core_title'|clip_orderby:'core_title'} returns 'core_title:desc'
 *   {'core_pid'|clip_orderby:'core_title'} returns 'core_title'
 *   {'core_title'|clip_orderby:'core_title'} returns 'z-order-desc'
 *   {'core_pid'|clip_orderby:'core_title'} returns 'z-order-asc'
 *
 * @param string $orderby The orderbt to process.
 * @param string $field   Field to compare.
 * @param string $return  Value to return (param or class)
 *
 * @return string CSS class or parameter result.
 */
function smarty_modifier_clip_orderby($orderby, $field, $return='param')
{
    if (stripos($orderby, $field) !== false) {
        $order = (stripos($orderby, "$field:desc") === false) ? 'desc' : 'asc';
    } else {
        $order = 'asc';
    }
    $output = '';

    switch ($return) {
        case 'param':
            $output = "$field:$order";
            break;
        case 'class':
            $output = "z-order-$order";
            break;
    }

    return $output;
}
