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

class Clip_Form_Plugin_String extends Form_Plugin_TextInput
{
    public $pluginTitle;
    public $columnDef = 'C(255)';

    function setup()
    {
        //! field type name
        $this->pluginTitle = $this->__('String');
    }

    function getFilename()
    {
        return __FILE__;
    }

    /**
     * Clip processing methods.
     */
    static function getPluginOutput($field)
    {
        $body = '{$pubdata.'.$field['name'].'|safehtml|modcallhooks:\'Clip\'}';

        return array('body' => $body);
    }
}
