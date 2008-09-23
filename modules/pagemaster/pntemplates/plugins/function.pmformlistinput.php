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

require_once('system/pnForm/plugins/function.pnformcategoryselector.php');

class pmformlistinput extends pnFormCategorySelector
{
    var $columnDef   = 'I (9,0)';
    var $title       = _PAGEMASTER_PLUGIN_LIST;
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
                                 $(\'typedata\').value = $F(\'pmplugin_categorylist\');
                                 closeTypeData();
                             }';
        return $saveTypeDataFunc;
    }

    function getTypeHtml($field)
    {
        Loader::loadClass('CategoryUtil');
        $rootCat = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules/pagemaster/lists');
        $cats    = CategoryUtil::getCategoriesByParentID($rootCat['id']);

        $html = '<div class="pn-formrow">
                  <label for="pmplugin_categorylist">'._CATEGORY.':</label><select id="pmplugin_categorylist" name="pmplugin_categorylist">';

        foreach ($cats as $cat) {
            $html .= '<option value="'.$cat['id'].'">'.$cat['name'].'</option>';
        }

        $html .= '</select>
                  </div>';

        return $html;
    }
}

function smarty_function_pmformlistinput($params, &$render) {
    return $render->pnFormRegisterPlugin('pmformlistinput', $params);
}
