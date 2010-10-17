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

    $field = $params['field'];
    $value = $params['value'];

    if (!$field) {
        return LogUtil::registerError(__f('Error! Missing argument [%s].', 'field', $dom));
    }

    if (!$value) {
        return LogUtil::registerError(__f('Error! Missing argument [%s].', 'value', $dom));
    }

    $html = '';
    foreach ($value as $cat) {
        $html .=  $cat['fullTitle'].'<br />';
    }

    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $html);
    } else {
        return $html;
    }
}
