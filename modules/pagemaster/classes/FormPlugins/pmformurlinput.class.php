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

require_once('system/pnForm/plugins/function.pnformurlinput.php');

class pmformurlinput extends pnFormURLInput
{
    var $columnDef = 'C(500)';
    var $title;

    function __construct()
    {
        $dom = ZLanguage::getModuleDomain('pagemaster');
        $this->title = __('URL');
    }

    function pmformurlinput()
    {
        $this->__construct();
    }

    function getFilename()
    {
        return __FILE__; // FIXME: may be found in smarty's data???
    }
}
