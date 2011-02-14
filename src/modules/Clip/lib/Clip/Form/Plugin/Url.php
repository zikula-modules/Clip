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
    public $pluginTitle;
    public $columnDef = 'C(512)';

    function setup()
    {
        $this->setDomain(ZLanguage::getModuleDomain('Clip'));
        
        //! field type name
        $this->pluginTitle = $this->__('URL');
    }

    function getFilename()
    {
        return __FILE__;
    }

    /**
     * Form Framework methods.
     */
    function readParameters($view, &$params)
    {
        parent::readParameters($view, $params);

        $this->maxLength = 2000;
        $this->cssClass .= ' url';
    }

    /**
     * Overrides the validation check to allow
     * {modname:func&param=value:type}
     */
    function validate($view)
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
    function postRead($data, $field)
    {
        // if there's an URL, process it
        if (!empty($data)) {
            $data = $this->parseURL($data);
        }

        return $data;
    }

    /**
     * Method to parse an internal URL 
     */
    static function parseURL($url)
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
