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

class Clip_Form_Plugin_Text extends Form_Plugin_TextInput
{
    public $pluginTitle;
    public $columnDef = 'X';

    public $config = array();

    function setup()
    {
        //! field type name
        $this->pluginTitle = $this->__('Text');
    }

    function getFilename()
    {
        return __FILE__;
    }

    /**
     * Form Framework methods.
     */
    function readParameters($view, &$params)
    {
        $this->parseConfig($view->eventHandler->getPubfieldData($params['id'], 'typedata'));

        parent::readParameters($view, $params);

        $this->textMode = 'multiline';
    }

    function render($view)
    {
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

    /**
     * Clip processing methods.
     */
    static function getPluginOutput($field)
    {
        $body = '{$pubdata.'.$field['name'].'|safehtml|modcallhooks:\'Clip\'}';

        return array('body' => $body);
    }

    /**
     * Clip admin methods.
     */
    static function getSaveTypeDataFunc($field)
    {
        $saveTypeDataFunc = 'function saveTypeData()
                             {
                                 if ($(\'clipplugin_usescribite\') && $F(\'clipplugin_usescribite\') == \'on\') {
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
        $this->parseConfig($view->_tpl_vars['field']['typedata']);

        // TODO Formatting config
        if (ModUtil::available('scribite')) {
            $checked = $this->config['usescribite'] ? 'checked="checked"' : '';
            $html = '<div class="z-formrow">
                         <label for="clipplugin_usescribite">'.$this->__('Use Scribite!').':</label>
                         <input type="checkbox" id="clipplugin_usescribite" name="clipplugin_usescribite" '.$checked.' />
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
    function parseConfig($typedata='')
    {
        // config string: "(bool)usescribite"
        $this->config['usescribite'] = (bool)$typedata;
    }
}
