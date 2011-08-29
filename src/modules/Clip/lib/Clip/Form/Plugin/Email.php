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

class Clip_Form_Plugin_Email extends Zikula_Form_Plugin_EmailInput
{
    // plugin definition
    public $pluginTitle;
    public $columnDef = 'C(100)';

    // Clip data handling
    public $tid;
    public $pid;
    public $field;

    public function setup()
    {
        $this->setDomain(ZLanguage::getModuleDomain('Clip'));

        //! field type name
        $this->pluginTitle = $this->__('Email');
    }

    public function getFilename()
    {
        return __FILE__;
    }

    /**
     * Form framework overrides.
     */
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
}
