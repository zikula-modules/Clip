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

require_once ('system/pnForm/plugins/function.pnformfloatinput.php');

class pmformfloatinput extends pnFormFloatInput {
    
    var $columnDef = 'F';
    var $title     = 'Float Value';

    function getFilename()
    {
        return __FILE__; // FIXME: may be found in smarty's data???
    }
}

function smarty_function_pmformfloatinput($params, &$render) {
    return $render->pnFormRegisterPlugin('pmformfloatinput', $params);
}
