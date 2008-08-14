<?php
/**
 * PageMaster
 *
 * @copyright (c) 2008, PageMaster Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package     Zikula_3rd_party_Modules
 * @subpackage  pagemaster
 */

require_once('system/pnForm/plugins/function.pnformdropdownlist.php');

class pmformpubinput extends pnFormDropdownList
{
    var $columnDef = 'I';
    var $title     = 'Publication';

    function getFilename()
    {
        return __FILE__; // FIXME: may be found in smarty's data???
    }

    function load($render)
    {
        $pubfields = $render->pnFormEventHandler->pubfields;
        foreach ($pubfields as $key => $pubfield) {
            if ($pubfield['name'] == $this->id) {
                list($tid,$filter) = explode(';', $pubfield['typedata']);
            }
        }
        $pubfields_pub = DBUtil::selectObjectArray('pagemaster_pubfields', 'pm_tid = '.$tid);
        $pubarr = pnModAPIFunc('pagemaster', 'user', 'pubList',
                               array('tid'                => $tid,
                                     'countmode'          => 'no',
                                     'filter'             => $filter,
                                     'pubfields'          => $pubfields_pub,
                                     'checkPerm'          => true,
                                     'handlePluginFields' => false));

        $titleField = getTitleField($pubfields_pub);

        $items = array();
        $items[] = array('text' => '- - -',
                         'value' => '');

        foreach ($pubarr['publist'] as $pub ) {
            $items[] = array('text'  => $pub[$titleField],
                             'value' => $pub['core_pid']);
        }
        $this->items = $items;
        parent::load($render);
    }

    function getSaveTypeDataFunc($field)
    {
        $saveTypeDataFunc = 'function saveTypeData()
                             {
                                 document.getElementById(\'typedata\').value = document.getElementById(\'publication_tid\').value+\';\'+document.getElementById(\'publication_filter\').value+';'+document.getElementById(\'pub_join\').value+\';\'+document.getElementById(\'pub_joinfields\').value;  
                                 document.getElementById(\'typeDataDiv\').style.display = \'none\';
                             }';
        return $saveTypeDataFunc;
    }

    function getTypeHtml($field, $render)
    {
        $vars = explode(';', $render->_tpl_vars['typedata']);

        $tid         = $vars[0];
        $filter      = $vars[1];
        $join        = $vars[2];
        $join_fields = $vars[3];

        if ($join == 'on') {
            $checked = 'checked="checked"';
        } else {
            $checked = '';
        }

        $pubtypes = DBUtil::selectObjectArray('pagemaster_pubtypes');

        $html .= 'publication: <select name="publication_tid" id="publication_tid">';
        foreach ($pubtypes as $pubtype) {
            if ($pubtype['tid'] == $tid) {
                $selected = 'selected="selected"';
            } else {
                $selected = '';
            }
            $html .= '<option value="' . $pubtype['tid'] . '" '.$selected.' >' . $pubtype['title'] . '</option>';
        }
        $html .= '</select>';
        $html .= 'filter: <input type="text" name="publication_filter" value="'.$filter.'" id="publication_filter" /><br />';
        $html .= 'join: <input type="checkbox" name="pub_join" id="pub_join" '.$checked.'><br />';
        $html .= 'fields: (fieldname:alias,fieldname:alias..): <input type="text name="pub_joinfields" id="pub_joinfields"  value="'.$join_fields.'" ><br />';
        return $html;
    }
}

function smarty_function_pmformpubinput($params, &$render) {
    return $render->pnFormRegisterPlugin('pmformpubinput', $params);
}
