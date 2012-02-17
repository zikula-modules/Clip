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

class Clip_Form_Plugin_RadioButton extends Zikula_Form_Plugin_RadioButton
{
    // Clip data handling
    public $alias;
    public $tid;
    public $rid;
    public $pid;
    public $field;

    public function readParameters(Zikula_Form_View $view, &$params)
    {
        unset($params['fieldconfig']);

        parent::readParameters($view, $params);
    }

    function loadValue(Zikula_Form_View $view, &$values)
    {
        if ($this->dataBased) {
            $value = null;

            if (isset($values[$this->group][$this->alias][$this->tid][$this->rid][$this->pid][$this->field])) {
                $value = $values[$this->group][$this->alias][$this->tid][$this->rid][$this->pid][$this->field];
            }

            if ($value !== null) {
                $this->checked = ($this->value === $value);
            } else {
                $this->checked = false;
            }
        }
    }

    function saveValue(Zikula_Form_View $view, &$data)
    {
        if ($this->dataBased && $this->checked) {
            if (!array_key_exists($this->group, $data)) {
                $data[$this->group] = array($this->alias => array($this->tid => array($this->rid => array($this->pid => array()))));
            }
            $data[$this->group][$this->alias][$this->tid][$this->rid][$this->pid][$this->field] = $this->value;
        }
    }
}
