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

require_once('system/pnForm/plugins/function.pnformcategoryradiolist.php');

class pmformradiolistinput extends pnFormCategoryRadioList
{
    var $columnDef   = 'C(512)';
    var $title       = 'Radio';
    var $filterClass = 'pmList';

    function getFilename()
    {
        return __FILE__; // FIXME: may be found in smarty's data???
    }

    static function postRead($data, $field)
    {
        $data   = substr($data, 1);
        $data   = substr($data, 0, -1);
        $catIds = explode(':', $data);
        $lang = pnUserGetLang();
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
        if (isset($render->pnFormEventHandler->pubfields[$this->id])) {
            $params['category'] = $render->pnFormEventHandler->pubfields[$this->id]['typedata'];
        }
        parent::load(&$render, $params);
        if ($this->mandatory)
            array_shift($this->items); //pnFormCategorySelector makes a "- - -" entry for mandatory field, what makes no sense for checkboxes
    }

    static function getSaveTypeDataFunc($field)
    {
        $saveTypeDataFunc = 'function saveTypeData()
                             {
                                 document.getElementById(\'typedata\').value = document.getElementById(\'pagemaster_list\').value ;
                                 document.getElementById(\'typeDataDiv\').style.display = \'none\';
                             }';
        return $saveTypeDataFunc;
    }

    static function getTypeHtml($field)
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