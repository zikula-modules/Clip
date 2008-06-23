<?php
/**
 * PageMaster
 *
 * @copyright (c) 2008, PageMaster Team
 * @link        http://code.zikula.org/projects/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package     Zikula_3rd_party_Modules
 * @subpackage  pagemaster
 */

require_once('system/pnForm/plugins/function.pnformcategoryselector.php');

class pmformlistinput extends pnFormCategorySelector
{
    var $columnDef   = 'I (9,0)';
    var $title       = 'List';
    var $filterClass = 'pmList';

    function getFilename()
    {
        return __FILE__; // FIXME: may be found in smarty's data???
    }

    function postRead($data, $field)
    {
        Loader::loadClass('CategoryUtil');
        $cat = CategoryUtil::getCategoryByID($data);

        $lang = SessionUtil::getVar('lang', null);

        // compatible mode to pagesetter
        $cat['fullTitle'] = $cat['display_name'][$lang];
        $cat['value']     = $cat['name'];
        $cat['title']     = $cat['name'];
        return $cat;
    }

    function load(&$render, $params)
    {
        $pubfields = $render->pnFormEventHandler->pubfields;
        foreach ($pubfields as $key => $pubfield) {
            if ($pubfield['name'] == $this->id) {
                $params['category'] = $pubfield['typedata'];
            }
        }
        parent::load(&$render, $params);
    }

    function getSaveTypeDataFunc($field)
    {
        $saveTypeDataFunc = 'function saveTypeData()
                             {
                                 document.getElementById(\'typedata\').value = document.getElementById(\'pagemaster_list\').value ;
                                 document.getElementById(\'typeDataDiv\').style.display = \'none\';
                             }';
        return $saveTypeDataFunc;
    }

    function getTypeHtml($field)
    {
        Loader::loadClass('CategoryUtil');
        $rootCat = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules/pagemaster/lists');
        $cats = CategoryUtil::getCategoriesByParentID($rootCat['id']);
        $html .= '<select name="pagemaster_list" id="pagemaster_list">';
        foreach ($cats as $cat) {
            $html .= '<option value="'.$cat['id'].'">'.$cat['name'].'</option>';
        }
        $html .= '</select>';
        return $html;
    }
}

function smarty_function_pmformlistinput($params, &$render) {
    return $render->pnFormRegisterPlugin('pmformlistinput', $params);
}
