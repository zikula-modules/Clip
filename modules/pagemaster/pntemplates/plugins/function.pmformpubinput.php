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
                                 $(\'typedata\').value = $F(\'pmplugin_pubtid\')+\';\'+$F(\'pmplugin_pubfilter\')+\';\'+$F(\'pmplugin_pubjoin\')+\';\'+$F(\'pmplugin_pubjoinfields\');  
                                 closeTypeData();
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

        $html = '<div class="pn-formrow">
                 '._PAGEMASTER_PUBLICATION.': <select id="pmplugin_pubtid" name="pmplugin_pubtid">';
        foreach ($pubtypes as $pubtype) {
            if ($pubtype['tid'] == $tid) {
                $selected = 'selected="selected"';
            } else {
                $selected = '';
            }
            $html .= '<option value="'.$pubtype['tid'].'" '.$selected.' >'.$pubtype['title'].'</option>';
        }
        $html .= '</select>
                  </div>';
        $html .= '<div class="pn-formrow">
                 '._PAGEMASTER_PUBFILTER.': <input type="text" id="pmplugin_pubfilter" name="pmplugin_pubfilter" value="'.$filter.'" />
                 </div>';
        $html .= '<div class="pn-formrow">
                 '._PAGEMASTER_PUBJOIN.': <input type="checkbox" id="pmplugin_pubjoin" name="pmplugin_pubjoin" '.$checked.' />
                 </div>';
        $html .= '<div class="pn-formrow">
                 '._PAGEMASTER_PUBJOINFIELDS.': <input type="text name="pmplugin_pubjoinfields" id="pmplugin_pubjoinfields"  value="'.$join_fields.'" >
                 </div>';
        return $html;
    }
}

function smarty_function_pmformpubinput($params, &$render) {
    return $render->pnFormRegisterPlugin('pmformpubinput', $params);
}
