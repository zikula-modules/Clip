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
 * Plugin to be used on display templates.
 */
function smarty_function_clip_multilistdecode($params, &$smarty)
{
    $dom = ZLanguage::getModuleDomain('Clip');

    if (!isset($params['value']) || !$params['value']) {
        return LogUtil::registerError(__f('Error! Missing argument [%s].', 'value', $dom));
    }

    $field = isset($params['field']) ? $params['field'] : 'fullTitle';
    $list  = isset($params['list']) ? (bool)$params['list'] : true;

    $html = $list ? '<ul>' : '';
    foreach ($params['value'] as $cat) {
        $value = isset($cat[$field]) ? $cat[$field] : $cat['fullTitle'];
        $html .=  ($list ? '<li>' : '') . $value . ($list ? '</li>' : '<br />');
    }
    $html .= $list ? '</ul>' : '';

    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $html);
    } else {
        return $html;
    }
}
