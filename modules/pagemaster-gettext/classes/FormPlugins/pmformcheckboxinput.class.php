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

require_once('system/pnForm/plugins/function.pnformcheckbox.php');

class pmformcheckboxinput extends pnFormCheckbox
{
    var $columnDef = 'I(4)';
    var $title     = _PAGEMASTER_PLUGIN_CHECKBOX;

    function getFilename()
    {
        return __FILE__; // FIXME: may be found in smarty's data???
    }
}