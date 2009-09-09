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

require_once ('system/pnForm/plugins/function.pnformfloatinput.php');

class pmformfloatinput extends pnFormFloatInput {
    
    var $columnDef = 'F';
    var $title     = _PAGEMASTER_PLUGIN_FLOAT;

    function getFilename()
    {
        return __FILE__; // FIXME: may be found in smarty's data???
    }
}