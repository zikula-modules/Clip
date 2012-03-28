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

class Clip_Form_Plugin_Language extends Zikula_Form_Plugin_LanguageSelector
{
    // plugin definition
    public $pluginTitle;
    public $columnDef = 'C(10)';
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
        $this->pluginTitle = $this->__('Language Selector');
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

        $params['onlyInstalledLanguages'] = isset($params['onlyInstalledLanguages']) ? $params['onlyInstalledLanguages'] : $this->config[0];
        $params['addAllOption'] = isset($params['addAllOption']) ? $params['addAllOption'] : $this->config[1];

        parent::readParameters($view, $params);
    }

    public function loadValue(Zikula_Form_View $view, &$values)
    {
        if ($this->dataBased) {
            $items = null;
            $value = null;

            $data = isset($values[$this->group][$this->alias][$this->tid][$this->rid][$this->pid]) ? $values[$this->group][$this->alias][$this->tid][$this->rid][$this->pid] : null;

            if ($data && isset($data[$this->field])) {
                $value = $data[$this->field];
            }
            if ($data && $this->itemsDataField && isset($data[$this->itemsDataField])) {
                $items = $data[$this->itemsDataField];
            }

            if ($items !== null) {
                $this->setItems($items);
            }

            $this->setSelectedValue($value);
        }
    }

    public function saveValue(Zikula_Form_View $view, &$data)
    {
        if ($this->dataBased) {
            if (!array_key_exists($this->group, $data)) {
                $data[$this->group] = array($this->alias => array($this->tid => array($this->rid => array($this->pid => array()))));
            }
            $data[$this->group][$this->alias][$this->tid][$this->rid][$this->pid][$this->field] = $this->getSelectedValue();
        }
    }

    /**
     * Clip processing methods.
     */
    public static function getOutputDisplay($field)
    {
        $full = '        <div class="z-formrow">'."\n".
                '            <span class="z-label">{$pubfields.'.$field['name'].'|clip_translate}:</span>'."\n".
                '            {if !empty($pubdata.'.$field['name'].')}'."\n".
                '                <span class="z-formnote">{$pubdata.'.$field['name'].'|getlanguagename}</span>'."\n".
                '            {else}'."\n".
                '                <span class="z-formnote">{gt text=\''.no__('Available for all languages.').'\'}</span>'."\n".
                '            {/if}'."\n".
                '        </div>';

        return array('full' => $full);
    }

    /**
     * Clip admin methods.
     */
    public static function getConfigSaveJSFunc($field)
    {
        return 'function()
                {
                    $(\'typedata\').value = Number($F(\'clipplugin_onlyinstalled\'))+\',\'+Number($F(\'clipplugin_alloption\'));

                    Zikula.Clip.Pubfields.ConfigClose();
                }';
    }

    public function getConfigHtml($field, $view)
    {
        $this->parseConfig($view->_tpl_vars['field']['typedata']);

        // empty item checkbox
        $checked = $this->config[0] ? 'checked="checked"' : '';
        $html .= '<div class="z-formrow">
                      <label for="clipplugin_onlyinstalled">'.$this->__('Show only the installed languages?').'</label>
                      <input type="checkbox" value="1" id="clipplugin_onlyinstalled" name="clipplugin_onlyinstalled" '.$checked.' />
                  </div>';

        // edit link checkbox
        $checked = $this->config[1] ? 'checked="checked"' : '';
        $html .= '<div class="z-formrow">
                      <label for="clipplugin_alloption">'.$this->__("Show the 'All' option?").':</label>
                      <input type="checkbox" value="1" id="clipplugin_alloption" name="clipplugin_alloption" '.$checked.' />
                  </div>';

        return $html;
    }

    /**
     * Parse configuration
     */
    public function parseConfig($typedata='')
    {
        // config string: "(bool)onlyInstalledLanguages,(bool)addAllOption"
        $typedata = explode(',', $typedata);

        $this->config = array(
            0 => $typedata[0] !== '' ? (bool)$typedata[0] : true,
            1 => isset($typedata[1]) ? (bool)$typedata[1] : true
        );
    }
}
