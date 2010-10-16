<?php
/**
 * Clip
 *
 * @copyright   (c) Clip Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @version     $ Id $
 * @package     Zikula_3rdParty_Modules
 * @subpackage  pagemaster
 */

class Clip_Form_Plugin_Text extends Form_Plugin_TextInput
{
    public $pluginTitle;
    public $columnDef = 'X';

    public $config;

    function setup()
    {
        $dom = ZLanguage::getModuleDomain('Clip');
        $this->setDomain($dom);

        //! field type name
        $this->pluginTitle = $this->__('Text');
    }

    function getFilename()
    {
        return __FILE__;
    }

    static function getPluginOutput($field)
    {
        $body = '{$pubdata.'.$field['name'].'|safehtml|modcallhooks:\'Clip\'}';

        return array('body' => $body);
    }

    function render($view)
    {
        $this->parseConfig($view->eventHandler->getPubfieldData($this->id, 'typedata'));

        $this->textMode = 'multiline';
        if ($this->config['usescribite'] && ModUtil::available('scribite')) {
            static $scribite_arr;
            $scribite_arr[] = $this->id;
            $scribite = ModUtil::func('scribite', 'user', 'loader',
                                  array('modulename' => 'Clip',
                                        'editor'     => 'xinha',
                                        'areas'      => $scribite_arr));
            PageUtil::addVar('rawtext', $scribite);
        }

        return parent::render($view);
    }

    static function getSaveTypeDataFunc($field)
    {
        $saveTypeDataFunc = 'function saveTypeData()
                             {
                                 if ($(\'pmplugin_usescribite\') && $F(\'pmplugin_usescribite\') == \'on\') {
                                     $(\'typedata\').value = 1;
                                 } else {
                                     $(\'typedata\').value = 0;
                                 }
                                 closeTypeData();
                             }';

        return $saveTypeDataFunc;
    }

    function getTypeHtml($field, $view)
    {
        $typedata = isset($view->_tpl_vars['typedata']) ? $view->_tpl_vars['typedata'] : false;
        $this->parseConfig($typedata);

        $checked = $this->config['usescribite'] ? 'checked="checked"' : '';

        // TODO Formatting config
        if (ModUtil::available('scribite')) {
            $html = '<div class="z-formrow">
                         <label for="pmplugin_usescribite">'.$this->__('Use Scribite!').':</label>
                         <input type="checkbox" id="pmplugin_usescribite" name="pmplugin_usescribite" '.$checked.' />
                     </div>';
        } else {
            $html = '<div class="z-formrow">
                         <span class="z-informationmsg">'.$this->__('Install Scribite! if you want to use it in this text field.').'</span>
                     </div>';
        }

        return $html;
    }

    /**
     * Parse configuration
     */
    function parseConfig($typedata='', $args=array())
    {
        $this->config = array();

        $this->config['usescribite'] = (bool)$typedata;
    }
}
