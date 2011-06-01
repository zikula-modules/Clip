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
 * Utility plugin to display a Multi/RadioList field values.
 *
 * Available parameters:
 *  - value  (array)  Multi/RadioList field value.
 *  - field  (string) Category field to show (default: fullTitle).
 *  - list   (mixed)  Flag or css class to use for the list
 *  - assign (string) Optional variable name to assign the plugin result to.
 *
 * Examples:
 *
 *  <samp>{clip_multilistdecode value=$pubdata.multilist list=false field='cid'}</samp>
 *  <samp>{clip_multilistdecode value=$pubdata.radiolist list='z-multilist'}</samp>
 *
 * @param array       $params All parameters passed to this plugin from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return mixed False on failure, HTML result otherwise.
 */
function smarty_function_clip_multilistdecode($params, &$view)
{
    if (!isset($params['value']) || !$params['value']) {
        return LogUtil::registerError($view->__f('Error! Missing argument [%s].', 'value'));
    }

    $field = isset($params['field']) ? $params['field'] : 'fullTitle';
    $list  = isset($params['list']) ? (is_bool($params['list']) ? (bool)$params['list'] : $params['list']) : true;

    $html = $list ? '<ul'.(!is_bool($params['list'] ? " class=\"{$params['list']}\"" : '')).'>' : '';

    foreach ($params['value'] as $cat) {
        $value = isset($cat[$field]) ? $cat[$field] : $cat['fullTitle'];
        $html .=  ($list ? '<li>' : '') . $value . ($list ? '</li>' : '<br />');
    }

    $html .= $list ? '</ul>' : '';

    if (isset($params['assign'])) {
        $view->assign($params['assign'], $html);
    } else {
        return $html;
    }
}
