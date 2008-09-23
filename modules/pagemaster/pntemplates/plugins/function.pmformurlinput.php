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

require_once('system/pnForm/plugins/function.pnformurlinput.php');

class pmformurlinput extends pnFormURLInput
{
    var $columnDef = 'C(500)';
    var $title     = 'Url';

    function getFilename()
    {
        return __FILE__; // FIXME: may be found in smarty's data???
    }
}

function smarty_function_pmformurlinput($params, &$render) {
    return $render->pnFormRegisterPlugin('pmformurlinput', $params);
}
