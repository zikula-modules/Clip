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

class Clip_Form_Plugin_Url extends Zikula_Form_Plugin_TextInput
{
    // plugin definition
    public $pluginTitle;
    public $columnDef = 'C(512)';

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
        $this->pluginTitle = $this->__('URL');
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

        $this->maxLength = 2000;
        $this->cssClass .= ' z-form-url';
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
     * Overrides the validation check to allow
     * {modname:func&param=value:type}
     */
    public function validate(Zikula_Form_View $view)
    {
        parent::validate($view);

        if (!$this->isValid) {
            return;
        }

        if (!empty($this->text)) {
            if (!System::varValidate($this->text, 'url')) {
                if ($this->text == $this->parseURL($this->text)) {
                    $this->setError(__('Error! Invalid URL.'));
                }
            }
        }
    }

    /**
     * Clip processing methods.
     */
    public function postRead(&$pub, $field)
    {
        $fieldname = $field['name'];
        $data = $pub[$fieldname];

        // if there's an URL, process it
        if (!empty($data)) {
            $data = $this->parseURL($data);
        }

        $pub[$fieldname] = $data;
    }

    /**
     * Method to parse an internal URL 
     */
    public static function parseURL($url)
    {
        // parse the URL
        // {modname:function&param=value:type}
        if (strpos($url, '{') === 0 && strpos($url, '}') === strlen($url)-1) {
            $url = substr($url, 1, -1);
            $url = explode(':', $url);

            // call[0] should be the module name
            if (isset($url[0]) && !empty($url[0])) { 
                $modname = $url[0];
                // default for params
                $params = array();
                // call[1] can be a function or function&param=value
                if (isset($url[1]) && !empty($url[1])) {
                    $urlparts = explode('&', $url[1]); 
                    $func = $urlparts[0];
                    unset($urlparts[0]);
                    if (count($urlparts) > 0) {
                        foreach ($urlparts as $urlpart) {
                            $part = explode('=', $urlpart);
                            $params[trim($part[0])] = trim($part[1]);
                        }
                    }
                } else {
                    $func = 'main';
                } 
                // addon: call[2] can be the type parameter, default 'user'
                $type = (isset($url[2]) &&!empty($url[2])) ? $url[2] : 'user';

                return ModUtil::url($modname, $type, $func, $params, null, null, true);
            }
        }

        return $url;
    }
}
