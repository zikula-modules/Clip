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
    var $columnDef = 'C(512)';
    var $title;

    function __construct()
    {
        $dom = ZLanguage::getModuleDomain('pagemaster');
        //! field type name
        $this->title = __('URL', $dom);

        parent::__construct();
    }

    function getFilename()
    {
        return __FILE__; // FIXME: may be found in smarty's data???
    }
}
