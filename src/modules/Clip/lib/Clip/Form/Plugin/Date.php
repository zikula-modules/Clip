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

class Clip_Form_Plugin_Date extends Zikula_Form_Plugin_DateInput
{
    // plugin definition
    public $pluginTitle;
    public $columnDef = 'T';
    public $filterClass = 'date';
    public $config = array();

    // Clip data handling
    public $tid;
    public $pid;
    public $field;

    public function setup()
    {
        $this->setDomain(ZLanguage::getModuleDomain('Clip'));

        //! field type name
        $this->pluginTitle = $this->__('Date');
    }

    public function getFilename()
    {
        return __FILE__;
    }

    /**
     * Form framework overrides.
     */
    public function readParameters($view, &$params)
    {
        $this->parseConfig($params['fieldconfig']);

        $params['includeTime'] = isset($params['includeTime']) ? $params['includeTime'] : $this->config['includeTime'];

        parent::readParameters($view, $params);
    }

    function loadValue(Zikula_Form_View $view, &$values)
    {
        if ($this->dataBased) {
            if (isset($values[$this->group][$this->tid][$this->pid][$this->field])) {
                $this->text = $this->formatValue($view, $values[$this->group][$this->tid][$this->pid][$this->field]);
            }
        }
    }

    function saveValue(Zikula_Form_View $view, &$data)
    {
        if ($this->dataBased) {
            $value = $this->parseValue($view, $this->text);

            if (!array_key_exists($this->group, $data)) {
                $data[$this->group] = array($this->tid => array($this->pid => array()));
            }
            $data[$this->group][$this->tid][$this->pid][$this->field] = $value;
        }
    }

    /**
     * Clip processing methods.
     */
    public function enrichFilterArgs(&$filterArgs, $field, $args)
    {
        $fieldname = $field['name'];
        $filterArgs['plugins'][$this->filterClass]['fields'][] = $fieldname;
    }

    public function getOutputDisplay($field)
    {
        $this->parseConfig($field['typedata']);
        $format = $this->config['includeTime'] ? 'datetimelong' : 'datelong';

        $body = "\n".
            '            <span class="z-formnote">{$pubdata.'.$field['name']."|dateformat:'$format'}</span>";

        return array('body' => $body);
    }

    /**
     * Clip admin methods.
     */
    public static function getConfigSaveJSFunc($field)
    {
        return 'function()
                {
                    if ($F(\'clipplugin_usedatetime\') == \'on\') {
                        $(\'typedata\').value = 1;
                    } else {
                        $(\'typedata\').value = 0;
                    }

                    Zikula.Clip.Pubfields.ConfigClose();
                }';
    }

    public function getConfigHtml($field, $view)
    {
        $this->parseConfig($view->_tpl_vars['field']['typedata']);
        $checked = $this->config['includeTime'] ? 'checked="checked"' : '';

        $html = '<div class="z-formrow">
                     <label for="clipplugin_usedatetime">'.$this->__('Include time').':</label>
                     <input type="checkbox" id="clipplugin_usedatetime" name="clipplugin_usedatetime" '.$checked.' />
                 </div>';

        return $html;
    }

    /**
     * Parse configuration
     */
    public function parseConfig($typedata='', $args=array())
    {
        // config string: "(bool)includeTime"
        $this->config['includeTime'] = (bool)$typedata;
    }
}
