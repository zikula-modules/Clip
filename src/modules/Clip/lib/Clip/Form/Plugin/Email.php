<?php
/**
 * Clip
 *
 * @copyright   (c) Clip Team
 * @link        http://code.zikula.org/clip/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @version     $ Id $
 * @package     Zikula_3rdParty_Modules
 * @subpackage  clip
 */

class Clip_Form_Plugin_Email extends Form_Plugin_EmailInput
{
    public $pluginTitle;
    public $columnDef = 'C(100)';

    function setup()
    {
        $dom = ZLanguage::getModuleDomain('Clip');
        $this->setDomain($dom);

        //! field type name
        $this->pluginTitle = $this->__('Email');
    }

    function getFilename()
    {
        return __FILE__;
    }
}
