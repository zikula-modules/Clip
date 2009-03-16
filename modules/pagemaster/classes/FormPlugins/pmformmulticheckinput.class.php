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

require_once('system/pnForm/plugins/function.pnformcategorycheckboxlist.php');

class pmformmulticheckinput extends pnFormCategoryCheckboxList
{
    var $columnDef   = 'C(512)';
    var $title       = _PAGEMASTER_PLUGIN_MULTICHECK;
    var $filterClass = 'pmList';

    function getFilename()
    {
        return __FILE__; // FIXME: may be found in smarty's data???
    }

    static function postRead($data, $field)
    {
        if (!empty($data) && $data <> '::') {
	    $lang = pnUserGetLang();
            if (strpos($data, ':') === 0) {
                $data = substr($data, 1, -1);
            }
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
                foreach ($catIds as $catId) {
                    $cat_arr[$catId]['fullTitle'] = (isset($cat_arr[$catId]['display_name'][$lang]) ? $cat_arr[$catId]['display_name'][$lang] : $cat_arr[$catId]['name']);
                }
            }
        }
        return $cat_arr;
    }

    function render(&$render)
    {
      	if ($this->mandatory== '1')
		$mand = '* Pflichtfeld';
	return $mand.parent::render($render);
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
                                 $(\'typedata\').value = $F(\'pmplugin_checklist\') ;
                                 closeTypeData();
                             }';
        return $saveTypeDataFunc;
    }

    static function getTypeHtml($field)
    {
        Loader::loadClass('CategoryUtil');
        Loader::loadClass('CategoryRegistryUtil');

        // TODO: Work based on a Category Registry
        $rootCat = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules/pagemaster/lists');
        $cats    = CategoryUtil::getCategoriesByParentID($rootCat['id']);

        $html = '<div class="pn-formrow">
                 <label for="pmplugin_checklist">'._CATEGORY.':</label><select id="pmplugin_checklist" name="pmplugin_checklist">';

        foreach ($cats as $cat) {
            $html .= '<option value="'.$cat['id'].'">'.$cat['name'].'</option>';
        }

        $html .= '</select>
                  </div>';

        return $html;
    }
}
