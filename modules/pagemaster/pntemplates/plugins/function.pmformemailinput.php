<?php
/**
 * PageMaster
 *
 * @copyright (c) 2008, PageMaster Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @version     $ Id $
 * @package     Zikula_3rd_party_Modules
 * @subpackage  pagemaster
 */

require_once('system/pnForm/plugins/function.pnformemailinput.php');

class pmformemailinput extends pnFormEMailInput
{
    var $columnDef = 'C(100)';
    var $title     = _PAGEMASTER_PLUGIN_EMAIL;

    function getFilename()
    {
        return __FILE__; // FIXME: may be found in smarty's data???
    }
}

function smarty_function_pmformemailinput($params, &$render) {
    return $render->pnFormRegisterPlugin('pmformemailinput', $params);
}
