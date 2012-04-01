<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Lib
 */

/**
 * Clip URL.
 */
class Clip_Url extends Zikula_ModUrl
{
    protected $application;
    protected $controller;
    protected $action;
    protected $args;
    protected $language;
    protected $fragment;

    public function __construct($module, $type, $func, $args=array(), $language=null, $fragment=null)
    {
        $this->application = $module;
        $this->controller  = $type;
        $this->action      = $func;
        $this->args        = $args;
        $this->language    = $language ? $language : ZLanguage::getLanguageCode();
        $this->fragment    = $fragment;
    }

    public function __toString()
    {
        return DataUtil::formatForDisplay($this->getUrl());
    }

    public function getUrl($ssl=null, $fqurl=null, $forcelongurl=false, $forcelang=false)
    {
        return ModUtil::url($this->application, $this->controller, $this->action, $this->args, $ssl, $this->fragment, $fqurl, $forcelongurl, $forcelang);
    }

    public function modFunc()
    {
        return ModUtil::func($this->application, $this->controller, $this->action, $this->args);
    }

    public function clipArray()
    {
        return array(
            'module'   => $this->application,
            'type'     => $this->controller,
            'func'     => $this->action,
            'args'     => $this->args,
            'language' => $this->language,
            'fragment' => $this->fragment
        );
    }

    public function serialize()
    {
        return serialize($this->toArray());
    }

    // getters
    public function getApplication()
    {
        return $this->application;
    }

    public function getController()
    {
        return $this->controller;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getArgs()
    {
        return $this->args;
    }

    public function getArg($name)
    {
        return isset($this->args[$name]) ? $this->args[$name] : null;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function getFragment()
    {
        return $this->fragment;
    }

    // setters
    public function setApplication($module)
    {
        $this->application = $module;
        return $this;
    }

    public function setController($type)
    {
        $this->controller = $type;
        return $this;
    }

    public function setAction($func)
    {
        $this->action = $func;
        return $this;
    }

    public function setArgs($args)
    {
        $this->args = $args;
        return $this;
    }

    public function setLanguage($lang)
    {
        $this->language = $lang;
        return $this;
    }

    public function setFragment($fragment)
    {
        $this->fragment = $fragment;
        return $this;
    }
}
