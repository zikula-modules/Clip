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

class Clip_Form_Plugin_Checkbox extends Zikula_Form_Plugin_Checkbox
{
    // plugin definition
    public $pluginTitle;
    public $columnDef = 'L';

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
        $this->pluginTitle = $this->__('Checkbox');
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
        unset($params['fieldconfig']);

        parent::readParameters($view, $params);
    }

    function loadValue(Zikula_Form_View $view, &$values)
    {
        if ($this->dataBased) {
            if (isset($values[$this->group][$this->alias][$this->tid][$this->rid][$this->pid][$this->field])) {
                $this->checked = $values[$this->group][$this->alias][$this->tid][$this->rid][$this->pid][$this->field];
            }
        }
    }

    function saveValue(Zikula_Form_View $view, &$data)
    {
        if ($this->dataBased) {
            if (!array_key_exists($this->group, $data)) {
                $data[$this->group] = array($this->alias => array($this->tid => array($this->rid => array($this->pid => array()))));
            }
            $data[$this->group][$this->alias][$this->tid][$this->rid][$this->pid][$this->field] = $this->checked;
        }
    }

    /**
     * Clip processing methods.
     */
    public static function getOutputDisplay($field)
    {
        $body = "\n".
            '            <span class="z-formnote">{$pubdata.'.$field['name'].'|yesno}</span>';

        return array('body' => $body);
    }
}
