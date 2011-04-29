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
 * Relation form plugin.
 */
function smarty_function_clip_form_relation($params, &$view) {
    return $view->registerPlugin('Clip_Form_Plugin_Relation', $params);
}
