<?php
/**
 * PageMaster
 *
 * @copyright   (c) PageMaster Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @version     $ Id $
 * @package     Zikula_3rdParty_Modules
 * @subpackage  pagemaster
 */

Loader::requireOnce('system/pnForm/plugins/function.pnformtextinput.php');

class pmformtextinput extends pnFormTextInput
{
    var $columnDef = 'X';
    var $title;

    function __construct()
    {
        $dom = ZLanguage::getModuleDomain('pagemaster');
        $this->title = __('Text');
    }

    function pmformtextinput()
    {
        $this->__construct();
    }

    function getFilename()
    {
        return __FILE__;
    }

    function render(&$render)
    {
        $this->textMode = 'multiline';
        if ($render->pnFormEventHandler->pubfields[$this->id]['typedata'] == 1 && pnModAvailable('scribite')) {
            static $scribite_arr;
            $scribite_arr[] = $this->id;
            $scribite = pnModFunc('scribite', 'user', 'loader',
                                  array('modulename' => 'pagemaster',
                                        'editor'     => 'xinha',
                                        'areas'      => $scribite_arr));
            PageUtil::setVar('rawtext', $scribite);
        }
        return parent::render($render);
    }

    static function getSaveTypeDataFunc($field)
    {
        $saveTypeDataFunc = 'function saveTypeData()
                             {
                                 if ($(\'pmplugin_usescribite\')) {
                                     if ($F(\'pmplugin_usescribite\') == \'on\') {
                                         $(\'typedata\').value = 1;
                                     } else {
                                         $(\'typedata\').value = 0;
                                     }
                                 }
                                 closeTypeData();
                             }';
        return $saveTypeDataFunc;
    }

    static function getTypeHtml($field, $render)
    {
        $dom = ZLanguage::getModuleDomain('pagemaster');
        if (isset($render->_tpl_vars['typedata']) && $render->_tpl_vars['typedata'] == 1) {
            $checked = 'checked="checked"';
        } else {
            $checked = '';
        }

        $html = '<div class="pn-formrow">';
        if (pnModAvailable('scribite')) {
            $html .= '<label for="pmplugin_usescribite">'.__('Use Scribite!', $dom).':</label><input type="checkbox" id="pmplugin_usescribite" name="pmplugin_usescribite" '.$checked.' />';
        } else {
            $html .= __('Install Scribite! if you want to use it in this text field', $dom);
        }
        $html .= '</div>';

        return $html;
    }
}