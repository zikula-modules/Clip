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
        $this->pluginTitle = $this->__('Date');
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

        $params['includeTime'] = isset($params['includeTime']) ? $params['includeTime'] : $this->includeTime;
        $params['ifFormat']    = $params['includeTime'] ? '%Y-%m-%d %H:%M' : '%Y-%m-%d';

        parent::readParameters($view, $params);
    }

    function loadValue(Zikula_Form_View $view, &$values)
    {
        if ($this->dataBased) {
            if (isset($values[$this->group][$this->alias][$this->tid][$this->rid][$this->pid][$this->field])) {
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

    function render(Zikula_Form_View $view)
    {
        // adds the jsCalendar header
        parent::render($view);

        $i18n = ZI18n::getInstance();

        $result = '<div>';

        if ($this->useSelectionMode) {
            $hiddenInputField = str_replace(array('type="text"', '&nbsp;*'),
                                            array('type="hidden"', ''),
                                            Zikula_Form_Plugin_TextInput::render($view));

            $result .= $hiddenInputField . '<span id="' . $this->id . 'cal">';
            if ($this->text) {
                $txtdate = DataUtil::formatForDisplay(DateUtil::getDatetime(DateUtil::parseUIDate($this->text), $this->daFormat));
            } else {
                $txtdate = $this->__('Select date');
            }
            $result .= $txtdate;
        } else {
            $result .= '<span class="z-form-date" style="white-space: nowrap">';
            $result .= Zikula_Form_Plugin_TextInput::render($view);
        }

        $result .= '</span>';

        $result .= '&nbsp;';
        $result .= "<img id=\"{$this->id}_img\" src=\"modules/Clip/images/icons/cal.png\" style=\"vertical-align: middle\" class=\"clickable\" alt=\"{$this->__('Select date')}\" />";

        $result .= '&nbsp;';
        if ($this->useSelectionMode) {
            $onclick = "onclick=\"document.getElementById('{$this->id}').value = '{$this->text}'; document.getElementById('{$this->id}cal').innerHTML = '{$txtdate}';\"";
        } else {
            $onclick = "onclick=\"document.getElementById('{$this->id}').value = '{$this->text}';\"";
        }
        $result .= "<img id=\"{$this->id}_imgclr\" src=\"modules/Clip/images/icons/editclear.png\" style=\"vertical-align: middle\" class=\"clickable\" alt=\"{$this->__('Reset date')}\" {$onclick}/>";

        $result .= '</div>';

        // build jsCalendar script options
        $result .= "<script type=\"text/javascript\">
            // <![CDATA[
            Calendar.setup(
            {
                inputField : \"{$this->id}\",";

        if ($this->includeTime) {
            $this->initDate = str_replace('-', ',', $this->initDate);
            $result .= "
                    ifFormat    : \"" . $this->ifFormat . "\",
                    showsTime   :    true,
                    timeFormat  :    \"" . $i18n->locale->getTimeformat() . "\",
                    singleClick :    false,";
        } else {
            $result .= "
                    ifFormat : \"" . $this->ifFormat . "\",";
        }

        if ($this->useSelectionMode) {
            $result .= "
                    displayArea :    \"{$this->id}cal\",
                    daFormat    :    \"{$this->daFormat}\",
                    align       :    \"Bl\",
                    singleClick :    true,";
        }

        $result .= "
                    button : \"{$this->id}_img\",";

        $result .= "
                    firstDay: " . $i18n->locale->getFirstweekday() . "
                }
            );
            // ]]>
            </script>";

        return $result;
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
        $format = $this->includeTime ? 'datetimelong' : 'datelong';

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
                    $(\'typedata\').value = Number($F(\'clipplugin_usedatetime\'));

                    Zikula.Clip.Pubfields.ConfigClose();
                }';
    }

    public function getConfigHtml($field, $view)
    {
        $this->parseConfig($view->_tpl_vars['field']['typedata']);
        $checked = $this->includeTime ? 'checked="checked"' : '';

        $html = '<div class="z-formrow">
                     <label for="clipplugin_usedatetime">'.$this->__('Include time').':</label>
                     <input type="checkbox" value="1" id="clipplugin_usedatetime" name="clipplugin_usedatetime" '.$checked.' />
                 </div>';

        return $html;
    }

    /**
     * Parse configuration
     */
    public function parseConfig($typedata='', $args=array())
    {
        // config string: "(bool)includeTime"
        $this->includeTime = (bool)$typedata;
    }
}
