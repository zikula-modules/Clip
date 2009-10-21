<?php
/**
 * PageMaster
 *
 * @copyright   (c) PageMaster Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @version     $ Id $
 * @package     Zikula_3rdParty_Modules
 * @subpackage  pagemaster
 */

function smarty_function_multilistdecode($params, &$smarty)
{
    $field = $params['field'];
    $value = $params['value'];

    if (!$field) {
        return 'Required parameter [field] not provided in smarty_function_multilistdecode';
    }

    if (!$value) {
        return 'Required parameter [value] not provided in smarty_function_multilistdecode';
    }

    foreach ($value as $cat) {
        $html .=  $cat['fullTitle'].'<br />';
    }

    if ($assign) {
        $smarty->assign($params['assign'], $html);
    } else {
        return $html;
    }
}
