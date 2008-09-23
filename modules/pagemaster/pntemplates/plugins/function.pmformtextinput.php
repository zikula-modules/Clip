<?php
/**
 * PageMaster
 *
 * @copyright (c) 2008, PageMaster Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @version     $ Id $
 * @package     Zikula_3rd_party_Modules
 * @subpackage  pagemaster
 */

require_once('system/pnForm/plugins/function.pnformtextinput.php');

class pmformtextinput extends pnFormTextInput
{
    var $columnDef = 'X';
    var $title     = 'Text';

    function getFilename()
    {
        return __FILE__;
    }

    function render(&$render)
    {
        $this->textMode = 'multiline';
        if (pnModAvailable('scribite')) {
            $pubfields = $render->pnFormEventHandler->pubfields;
            foreach ($pubfields as $key => $pubfield) {
                if ($pubfield['name'] == $this->id and $pubfield['typedata'] == 1) {
                    static $scribite_arr;
                    $scribite_arr[] = $this->id;
                    $scribite = pnModFunc('scribite', 'user', 'loader',
                                          array('modname' => 'pagemaster',
                                                'editor'  => 'xinha',
                                                'areas'   => $scribite_arr));
                    PageUtil::setVar('rawtext', $scribite);
                }
            }
        }
        return parent::render($render);
    }

    function getSaveTypeDataFunc($field) {
        $saveTypeDataFunc = 'function saveTypeData()
                             {
                                 if ($F(\'pagemaster_scribite\') == \'on\') {
                                     $(\'typedata\').value = 1;
                                 } else {
                                     $(\'typedata\').value = 0;
                                 } 
                                 closeTypeData();
                             }';
        return $saveTypeDataFunc;
    }

    function getTypeHtml($field, $render) {
        if ($render->_tpl_vars['typedata'] == 1) {
            $checked = 'checked="checked"';
        } else {
            $checked = '';
        }
        $html = '<div class="pn-formrow">
                '._PAGEMASTER_USESCRIBITE.': <input type="checkbox" id="pagemaster_scribite" name="pagemaster_scribite" '.$checked.' />
                 </div>';
        return $html;
    }
}

function smarty_function_pmformtextinput($params, &$render) {
    return $render->pnFormRegisterPlugin('pmformtextinput', $params);
}
