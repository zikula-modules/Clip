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

class Clip_Form_Plugin_Private extends Zikula_Form_Plugin_TextInput
{
    // plugin definition
    public $pluginTitle;
    public $columnDef = 'C(255)';

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
        $this->pluginTitle = $this->__('Private');
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

        // defaults readOnly to true
        $params['readOnly'] = isset($params['readOnly']) ? $params['readOnly'] : true;

        parent::readParameters($view, $params);

        // preserve the text value if passed and not the loaded value
        $this->text = isset($params['value']) ? $params['value'] : $this->text;
    }

    function create(Zikula_Form_View $view, &$params)
    {
        // overradable defaults
        $this->visible  = false;
        $this->textMode = 'hidden';

        parent::create($view, $params);
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

    /**
     * Clip processing methods.
     */
    public static function getOutputDisplay($field)
    {
        $body = "\n".
            '            <span class="z-formnote">{$pubdata.'.$field['name'].'|safehtml}</span>';

        return array('body' => $body);
    }
}
