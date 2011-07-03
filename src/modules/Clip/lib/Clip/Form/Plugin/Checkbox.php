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

    public function setup()
    {
        $this->setDomain(ZLanguage::getModuleDomain('Clip'));
        
        //! field type name
        $this->pluginTitle = $this->__('Checkbox');
    }

    public function getFilename()
    {
        return __FILE__;
    }

    /**
     * Clip processing methods.
     */
    public static function getOutputDisplay($field)
    {
        $body = "\n".
            '            <span class="z-formnote">{$pubdata.'.$field['name'].'|yesno}</span>';

        return array('body' => $body);
    }
}
