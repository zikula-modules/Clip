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

Loader::requireOnce('system/pnForm/plugins/function.pnformtextinput.php');

class pmformstringinput extends pnFormTextInput
{
    var $columnDef = 'C(512)';
    var $title     = 'String';

    function __construct()
    {
        $dom = ZLanguage::getModuleDomain('pagemaster');
        $this->title = __('String');
    }

    function pmformstringinput()
    {
        $this->__construct();
    }

    function getFilename()
    {
        return __FILE__; // FIXME: may be found in smarty's data???
    }
}
