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

require_once('system/pnForm/plugins/function.pnformcategorycheckboxlist.php');

class pmformmultilistinput extends pnFormCategoryCheckboxList
{
    var $columnDef   = 'C(512)';
    var $title       = _PAGEMASTER_PLUGIN_MULTILIST;
    var $filterClass = 'pmList';

    function getFilename()
    {
        return __FILE__; // FIXME: may be found in smarty's data???
    }

    function postRead($data, $field)
    {
        $data    = substr($data, 1);
        $data    = substr($data, 0, -1);
        $cat_arr = null;
        if ($data <> ''){
            $catIds = explode(':', $data);
            if (!empty($catIds)) {
                Loader::loadClass('CategoryUtil');
                pnModDBInfoLoad ('Categories');
                $pntables        = pnDBGetTables();
                $category_column = $pntables['categories_category_column'];

                $where = array();
                foreach ($catIds as $catId) {
                    $where[] = $category_column['id'].' = \''.DataUtil::formatForStore($catId).'\'';
                }
                $cat_arr = CategoryUtil::getCategories(implode(' OR ', $where), '', 'id');
                $lang   = SessionUtil::getVar('lang', null);
                
                foreach ($catIds as $catId) {
                    $cat_arr[$catId]['fullTitle'] = (isset($cat_arr[$catId]['display_name'][$lang]) ? $cat_arr[$catId]['display_name'][$lang] : $cat_arr[$catId]['name']);
                }
            }
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
    }

    function getSaveTypeDataFunc($field)
    {
        $saveTypeDataFunc = 'function saveTypeData()
                             {
                                 $(\'typedata\').value = $F(\'pmplugin_categorylist\') ;
                                 closeTypeData();
                             }';
        return $saveTypeDataFunc;
    }

    function getTypeHtml($field)
    {
        Loader::loadClass('CategoryUtil');
        Loader::loadClass('CategoryRegistryUtil');

        // TODO: Work based on a Category Registry
        $rootCat = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules/pagemaster/lists');
        $cats    = CategoryUtil::getCategoriesByParentID($rootCat['id']);

        $html .= '<div class="pn-formrow">
                  <label for="pmplugin_categorylist">'._CATEGORY.':</label><select id="pmplugin_categorylist" name="pmplugin_categorylist">';

        foreach ($cats as $cat) {
            $html .= '<option value="'.$cat['id'].'">'.$cat['name'].'</option>';
        }

        $html .= '</select>
                  </div>';

        return $html;
    }
}

function smarty_function_pmformmultilistinput($params, &$render) {
    return $render->pnFormRegisterPlugin('pmformmultilistinput', $params);
}
