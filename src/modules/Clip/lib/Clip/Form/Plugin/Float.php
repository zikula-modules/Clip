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

class Clip_Form_Plugin_Float extends Zikula_Form_Plugin_FloatInput
{
    // plugin definition
    public $pluginTitle;
    public $columnDef = 'F';
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
        $this->pluginTitle = $this->__('Float');
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

        $params['minValue'] = isset($params['minValue']) ? $params['minValue'] : $this->config['min'];
        $params['maxValue'] = isset($params['maxValue']) ? $params['maxValue'] : $this->config['max'];

        parent::readParameters($view, $params);
    }

    function loadValue(Zikula_Form_View $view, &$values)
    {
        if ($this->dataBased) {
            if (isset($values[$this->group][$this->alias][$this->tid][$this->pid][$this->rid][$this->field])) {
                $this->text = $this->formatValue($view, $values[$this->group][$this->alias][$this->tid][$this->rid][$this->pid][$this->field]);
            }
        }
    }

    function saveValue(Zikula_Form_View $view, &$data)
    {
        if ($this->dataBased) {
            $value = $this->parseValue($view, $this->text);

            if (!array_key_exists($this->group, $data)) {
                $data[$this->group] = array($this->alias => array($this->tid => array($this->rid => array($this->pid => array()))));
            }
            $data[$this->group][$this->alias][$this->tid][$this->rid][$this->pid][$this->field] = $value;
        }
    }

    /**
     * Clip admin methods.
     */
    public static function getConfigSaveJSFunc($field)
    {
        return 'function()
                {
                    if ($F(\'clipplugin_minvalue\') != null) {
                        $(\'typedata\').value = $F(\'clipplugin_minvalue\');
                    }
                    $(\'typedata\').value += \'|\';
                    if ($F(\'clipplugin_maxvalue\') != null) {
                        $(\'typedata\').value += $F(\'clipplugin_maxvalue\');
                    }

                    Zikula.Clip.Pubfields.ConfigClose();
                }';
    }

    public function getConfigHtml($field, $view)
    {
        $this->parseConfig($view->_tpl_vars['field']['typedata']);

        $html = ' <div class="z-formrow">
                      <label for="clipplugin_minvalue">'.$this->__('Min value').':</label>
                      <input type="text" id="clipplugin_minvalue" name="clipplugin_minvalue" value="'.$this->config['min'].'" />
                  </div>
                  <div class="z-formrow">
                      <label for="clipplugin_maxvalue">'.$this->__('Max value').':</label>
                      <input type="text" id="clipplugin_maxvalue" name="clipplugin_maxvalue" value="'.$this->config['max'].'" />
                  </div>';

        return $html;
    }

    /**
     * Parse configuration
     */
    public function parseConfig($typedata='', $args=array())
    {
        $typedata = explode('|', $typedata);

        $this->config = array(
            'min' => is_numeric($typedata[0]) ? (float)$typedata[0] : null,
            'max' => isset($typedata[1]) && is_numeric($typedata[1]) ? (float)$typedata[1] : null
        );
    }
}
