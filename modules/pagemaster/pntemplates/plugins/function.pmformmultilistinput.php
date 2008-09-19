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

require_once('system/pnForm/plugins/function.pnformcategorycheckboxlist.php');

class pmformmultilistinput extends pnFormCategoryCheckboxList
{
    var $columnDef   = 'C(512)';
    var $title       = 'MultiList';
    var $filterClass = 'pmList';

    function getFilename()
    {
        return __FILE__; // FIXME: may be found in smarty's data???
    }

    function postRead($data, $field)
    {
    	$data   = substr($data, 1);
        $data   = substr($data, 0, -1);
        $catIds = explode(':', $data);
        $lang   = SessionUtil::getVar('lang', null);
        Loader::loadClass('CategoryUtil');
        foreach ($catIds as $catId) {
            $cat = CategoryUtil::getCategoryByID($catId);
            $cat['fullTitle'] = $cat['display_name'][$lang];
            $cat_arr[] = $cat;
        }
        return $cat_arr;
    }
    
    function create(&$render, &$params)
    {
        $this->saveAsString = 1;
        parent::create($render, $params);
    }

    function load(&$render, $params)
    {
        $pubfields = $render->pnFormEventHandler->pubfields;
        foreach ($pubfields as $key => $pubfield) {
            if ($pubfield['name'] == $this->id) {
                $catid = $pubfield['typedata'];
            }
        }
        $params['category'] = $catid;
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
        Loader::loadClass('CategoryRegistryUtil');

        $rootCat = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules/pagemaster/lists');
        $cats    = CategoryUtil::getCategoriesByParentID($rootCat['id']);

        $html .= '<select name="pagemaster_list" id="pagemaster_list">';
        foreach ($cats as $cat) {
            $html .= '<option value="'.$cat['id'].'">'.$cat['name'].'</option>';
        }
        $html .= '</select>';
        return $html;
    }
}

function smarty_function_pmformmultilistinput($params, &$render) {
    return $render->pnFormRegisterPlugin('pmformmultilistinput', $params);
}
