<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Form_Plugin
 */

class Clip_Form_Plugin_Checkbox extends Zikula_Form_Plugin_Checkbox
{
    public $pluginTitle;
    public $columnDef = 'L';

    function setup()
    {
        $this->setDomain(ZLanguage::getModuleDomain('Clip'));
        
        //! field type name
        $this->pluginTitle = $this->__('Checkbox');
    }

    function getFilename()
    {
        return __FILE__;
    }

    /**
     * Form Framework methods.
     */
    static function getPluginOutput($field)
    {
        $body = '{$pubdata.'.$field['name'].'|yesno}';

        return array('body' => $body);
    }
}
