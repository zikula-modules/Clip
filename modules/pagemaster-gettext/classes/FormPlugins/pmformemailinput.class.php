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

require_once('system/pnForm/plugins/function.pnformemailinput.php');

class pmformemailinput extends pnFormEMailInput
{
    var $columnDef = 'C(100)';
    var $title     = 'Email';

    function getFilename()
    {
        return __FILE__; // FIXME: may be found in smarty's data???
    }
}