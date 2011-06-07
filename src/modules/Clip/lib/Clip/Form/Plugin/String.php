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

class Clip_Form_Plugin_String extends Zikula_Form_Plugin_TextInput
{
    public $pluginTitle;
    public $columnDef = 'C(255)';

    function setup()
    {
        $this->setDomain(ZLanguage::getModuleDomain('Clip'));
        
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
    static function processQuery(&$query, $field, $args)
    {
        if (!$field['isuid']) {
            return;
        }

        // restrict the query for normal users
        if (!Clip_Access::toPubtype($args['tid'], 'editor')) {
            $uid = UserUtil::getVar('uid');
            $query->andWhere("$fieldname = ?", $uid);
        }
    }

    static function getOutputDisplay($field)
    {
        $body = "\n".
            '        <span class="z-formnote">{$pubdata.'.$field['name'].'|safehtml}</span>';

        return array('body' => $body);
    }
}
