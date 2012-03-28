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

class Clip_Form_Plugin_Text extends Zikula_Form_Plugin_TextInput
{
    // plugin definition
    public $pluginTitle;
    public $columnDef = 'C(65535)';
    public $config = array();

    // Clip data handling
    public $alias;
    public $tid;
    public $rid;
    public $pid;
    public $field;

    public function setup()
    {
        $this->setDomain(ZLanguage::getModuleDomain('Clip'));

        //! field type name
        $this->pluginTitle = $this->__('Text');
    }

    public function getFilename()
    {
        return __FILE__;
    }

    /**
     * Form framework overrides.
     */
    public function readParameters(Zikula_Form_View $view, &$params)
    {
        $this->parseConfig($params['fieldconfig']);
        unset($params['fieldconfig']);

        parent::readParameters($view, $params);
    }

    public function loadValue(Zikula_Form_View $view, &$values)
    {
        if ($this->dataBased) {
            if (isset($values[$this->group][$this->alias][$this->tid][$this->rid][$this->pid][$this->field])) {
                $this->text = $this->formatValue($view, $values[$this->group][$this->alias][$this->tid][$this->rid][$this->pid][$this->field]);
            }
        }
    }

    public function saveValue(Zikula_Form_View $view, &$data)
    {
        if ($this->dataBased) {
            $value = $this->parseValue($view, $this->text);

            if (!array_key_exists($this->group, $data)) {
                $data[$this->group] = array($this->alias => array($this->tid => array($this->rid => array($this->pid => array()))));
            }
            $data[$this->group][$this->alias][$this->tid][$this->rid][$this->pid][$this->field] = $value;
        }
    }

    public function render(Zikula_Form_View $view)
    {
        $this->textMode = 'multiline';

        return parent::render($view);
    }

    /**
     * Clip processing methods.
     */
    public static function getOutputDisplay($field)
    {
        $body = "\n".
            '             <div class="z-formnote">{$pubdata.'.$field['name'].'|safehtml|clip_notifyfilters:$pubtype}</div>';

        return array('body' => $body);
    }

    public static function getOutputEdit($field)
    {
        return array('args' => " rows='15' cols='70'");
    }

    /**
     * Clip admin methods.
     */
    public static function getConfigSaveJSFunc($field)
    {
        return 'function()
                {
                    $(\'typedata\').value = $(\'clipplugin_usescribite\') ? Number($F(\'clipplugin_usescribite\')) : \'\';

                    Zikula.Clip.Pubfields.ConfigClose();
                }';
    }

    public function getConfigHtml($field, $view)
    {
        $this->parseConfig($view->_tpl_vars['field']['typedata']);

        // TODO Formatting config
        if (ModUtil::available('scribite')) {
            $checked = $this->config['usescribite'] ? 'checked="checked"' : '';
            $html = '<div class="z-formrow">
                         <span class="z-warningmsg">'.$this->__('Be sure to setup a default editor for Scribite!. It will be used in these text fields.').'</span>
                         <label for="clipplugin_usescribite">'.$this->__('Use Scribite!').':</label>
                         <input type="checkbox" value="1" id="clipplugin_usescribite" name="clipplugin_usescribite" '.$checked.' />
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
    public function parseConfig($typedata='')
    {
        // config: "{(bool)usescribite, (string)editor}"
        $typedata = explode('|', $typedata);

        $this->config = array(
            'usescribite' => $typedata[0] !== '' ? (bool)$typedata[0] : false,
            'editor' => isset($typedata[1]) ? $typedata[1] : '-'
        );
    }
}
