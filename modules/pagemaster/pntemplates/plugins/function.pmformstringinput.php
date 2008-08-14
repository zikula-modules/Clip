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

require_once('system/pnForm/plugins/function.pnformtextinput.php');

class pmformstringinput extends pnFormTextInput
{
    var $columnDef = 'C(512)';
    var $title     = 'String';

    function getFilename()
    {
        return __FILE__; // FIXME: may be found in smarty's data???
    }
}

function smarty_function_pmformstringinput($params, &$render) {
    return $render->pnFormRegisterPlugin('pmformstringinput', $params);
}
