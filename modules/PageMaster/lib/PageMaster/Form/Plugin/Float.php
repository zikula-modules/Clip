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

class PageMaster_Form_Plugin_Float extends Form_Plugin_FloatInput
{
    public $columnDef = 'F';
    public $title;

    function __construct()
    {
        $dom = ZLanguage::getModuleDomain('PageMaster');
        //! field type name
        $this->title = __('Float Value', $dom);

        parent::__construct();
    }

    function getFilename()
    {
        return __FILE__;
    }
}
