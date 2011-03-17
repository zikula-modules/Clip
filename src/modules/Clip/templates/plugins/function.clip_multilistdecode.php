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

function smarty_function_clip_multilistdecode($params, &$smarty)
{
    $dom = ZLanguage::getModuleDomain('Clip');

    $field = isset($params['field']) ? $params['field'] : 'fullTitle';

    if (!isset($params['value']) || !$value) {
        return LogUtil::registerError(__f('Error! Missing argument [%s].', 'value', $dom));
    }

    $html = '';
    foreach ($params['value'] as $cat) {
        $html .=  (isset($cat[$field]) ? $cat[$field] : $cat['fullTitle']) . '<br />';
    }

    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $html);
    } else {
        return $html;
    }
}
