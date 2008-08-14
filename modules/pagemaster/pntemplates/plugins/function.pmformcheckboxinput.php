<?php
/**
 * PageMaster
 *
 * @copyright (c) 2008, PageMaster Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package     Zikula_3rd_party_Modules
 * @subpackage  pagemaster
 */

require_once('system/pnForm/plugins/function.pnformcheckbox.php');

class pmformcheckboxinput extends pnFormCheckbox
{
    var $columnDef = 'I(4)';
    var $title     = 'Checkbox';

    function getFilename()
    {
        return __FILE__; // FIXME: may be found in smarty's data???
    }
}

function smarty_function_pmformcheckboxinput($params, &$render) {
    return $render->pnFormRegisterPlugin('pmformcheckboxinput', $params);
}
