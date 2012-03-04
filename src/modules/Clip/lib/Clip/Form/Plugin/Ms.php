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

class Clip_Form_Plugin_Ms extends Zikula_Form_Plugin_TextInput
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
        $this->pluginTitle = $this->__('Mediashare');
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
    public static function postRead(&$pub, $field)
    {
        $fieldname = $field['name'];
        $data = $pub[$fieldname];

        // default
        $cat = array();

        // if there's a value extract the category
        if (!empty($data) && is_numeric($data)) {
            $cat  = CategoryUtil::getCategoryByID($data);

            if ($cat) {
                $lang = ZLanguage::getLanguageCode();

                // compatible mode to pagesetter
                $cat['fullTitle'] = isset($cat['display_name'][$lang]) ? $cat['display_name'][$lang] : $cat['name'];
                $cat['fullDesc']  = isset($cat['display_desc'][$lang]) ? $cat['display_desc'][$lang] : '';
                $cat['value']     = $cat['name'];
                $cat['title']     = $cat['name'];
            }
        }

        $pub[$fieldname] = $cat;
    }
}
