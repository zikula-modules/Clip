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

require_once('system/pnForm/plugins/function.pnformdateinput.php');

class pmformdateinput extends pnFormDateInput
{
    var $columnDef = 'T';
    var $title     = _PAGEMASTER_PLUGIN_DATE;
    var $filterClass = 'date';

	
    function getFilename()
    {
        return __FILE__; // FIXME: may be found in smarty's data???
    }

    function getSaveTypeDataFunc($field)
    {
        $saveTypeDataFunc = 'function saveTypeData()
                             {
                                 if ($F(\'pmplugin_usedatetime\') == \'on\') {
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
        if ($render->_tpl_vars['typedata'] == 1) {
            $checked = 'checked="checked"';
        } else {
            $checked = '';
        }
        $html .= '<div class="pn-formrow">
                  <label for="pmplugin_usedatetime">'._PAGEMASTER_USEDATETIME.':</label><input type="checkbox" id="pmplugin_usedatetime" name="pmplugin_usedatetime" '.$checked.' />
                  </div>';
        return $html;
    }

    function create(&$render, &$params)
    {
        $pubfields = $render->pnFormEventHandler->pubfields;

        if (array_key_exists($this->id, $pubfields)) {
            $params['includeTime'] = $pubfields[$this->id]['typedata'];
        }

        parent::create($render, $params);
    }
}

function smarty_function_pmformdateinput($params, &$render) {
    return $render->pnFormRegisterPlugin('pmformdateinput', $params);
}
