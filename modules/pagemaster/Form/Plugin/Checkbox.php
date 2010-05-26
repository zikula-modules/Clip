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

class pagemaster_Form_Plugin_Checkbox extends Form_Plugin_Checkbox
{
    public $columnDef = 'I1(1)';
    public $title;

    function __construct()
    {
        $dom = ZLanguage::getModuleDomain('pagemaster');
        //! field type name
        $this->title = __('Checkbox', $dom);

        parent::__construct();
    }

    function getFilename()
    {
        return __FILE__;
    }
}
