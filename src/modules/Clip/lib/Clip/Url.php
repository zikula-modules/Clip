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
    public function __construct($application, $controller, $action, $args=array(), $language=null, $fragment=null)
    {
        $this->application = $application;
        $this->controller = $controller;
        $this->action = $action;
        $this->args = $args;
        $this->language = $language ? $language : ZLanguage::getLanguageCode();
        $this->fragment = $fragment;
    }

    public function __toString()
    {
        return $this->getUrl();
    }

    public function getUrl($ssl=null, $fqurl=null, $forcelongurl=false, $forcelang=false)
    {
        return ModUtil::url($this->application, $this->controller, $this->action, $this->args, $ssl, $this->fragment, $fqurl, $forcelongurl, $forcelang);
    }

    public function clipArray()
    {
        return array(
            'modname'  => $this->application,
            'type'     => $this->controller,
            'func'     => $this->action,
            'args'     => $this->args,
            'lang'     => $this->language,
            'fragment' => $this->fragment
        );
    }
}
