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

require_once('system/pnForm/plugins/function.pnformtextinput.php');

class pmformtextinput extends pnFormTextInput
{
    var $columnDef = 'X';
    var $title;

    var $config;

    function __construct()
    {
        $dom = ZLanguage::getModuleDomain('PageMaster');
        //! field type name
        $this->title = __('Text', $dom);

        parent::__construct();
    }

    function getFilename()
    {
        return __FILE__;
    }

    function render(&$render)
    {
        $this->parseConfig($render->pnFormEventHandler->pubfields[$this->id]['typedata']);

        $this->textMode = 'multiline';
        if ($this->config['usescribite'] && pnModAvailable('scribite')) {
            static $scribite_arr;
            $scribite_arr[] = $this->id;
            $scribite = pnModFunc('scribite', 'user', 'loader',
                                  array('modulename' => 'PageMaster',
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
                                 if ($(\'pmplugin_usescribite\') && $F(\'pmplugin_usescribite\') == \'on\') {
                                     $(\'typedata\').value = 1;
                                 } else {
                                     $(\'typedata\').value = 0;
                                 }
                                 closeTypeData();
                             }';

        return $saveTypeDataFunc;
    }

    function getTypeHtml($field, $render)
    {
        $dom = ZLanguage::getModuleDomain('PageMaster');

        $typedata = isset($render->_tpl_vars['typedata']) ? $render->_tpl_vars['typedata'] : false;
        $this->parseConfig($typedata);

        $checked = $this->config['usescribite'] ? 'checked="checked"' : '';

        // TODO Formatting config
        if (pnModAvailable('scribite')) {
            $html = '<div class="z-formrow">
                         <label for="pmplugin_usescribite">'.__('Use Scribite!', $dom).':</label>
                         <input type="checkbox" id="pmplugin_usescribite" name="pmplugin_usescribite" '.$checked.' />
                     </div>';
        } else {
            $html = '<div class="z-formrow">
                         <span class="z-informationmsg">'.__('Install Scribite! if you want to use it in this text field', $dom).'</span>
                     </div>';
        }

        return $html;
    }

    /**
     * Parse configuration
     */
    function parseConfig($typedata = '', $args = array())
    {
        $this->config = array();

        $this->config['usescribite'] = (bool)$typedata;
    }
}
