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