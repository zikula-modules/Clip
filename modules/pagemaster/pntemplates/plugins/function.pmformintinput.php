<?php
/**
 * PageMaster
 *
 * @copyright (c) 2008, PageMaster Team
 * @link        http://code.zikula.org/projects/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package     Zikula_3rd_party_Modules
 * @subpackage  pagemaster
 */

require_once('system/pnForm/plugins/function.pnformintinput.php');

class pmformintinput extends pnFormIntInput
{
    var $columnDef = 'I (9,0)';
    var $title =     'Integer Value';

    function getFilename()
    {
        return __FILE__; // FIXME: may be found in smarty's data???
    }
}

function smarty_function_pmformintinput($params, &$render) {
    return $render->pnFormRegisterPlugin('pmformintinput', $params);
}