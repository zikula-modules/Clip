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
        $cat['fullTitle'] = (isset($cat['display_name'][$lang]) ? $cat['display_name'][$lang] : $cat['name']);
        $cat['value']     = $cat['name'];
        $cat['title']     = $cat['name'];

        return $cat;
    }

    function load(&$render, $params)
    {
        if (!empty($render->pnFormEventHandler->pubfields[$this->id]['typedata'])) {
            // config is: {categoryID, (bool)includeEmpty}
            $config = explode(',', $render->pnFormEventHandler->pubfields[$this->id]['typedata']);
            $params['category'] = $config[0];
            $this->includeEmptyElement = isset($config[1]) ? (bool)$config[1] : true;
        } else {
            $params['category'] = 30; // Global category
            $this->includeEmptyElement = true;
        }

        $params['dummyEntry'] = true;
        parent::load(&$render, $params);
    }

    function getSaveTypeDataFunc($field)
    {
        $saveTypeDataFunc = 'function saveTypeData()
                             {
                                 if ($F(\'pmplugin_categorylist\') != null) {
                                     $(\'typedata\').value = $F(\'pmplugin_categorylist\');
                                 } else {
                                     $(\'typedata\').value = 30; 
                                 }
                                 $(\'typedata\').value += \',\';
                                 if ($F(\'pmplugin_categoryempty\') == \'on\') {
                                     $(\'typedata\').value += 1;
                                 } else {
                                     $(\'typedata\').value += 0;
                                 }
                                 closeTypeData();
                             }';
        return $saveTypeDataFunc;
    }

    function getTypeHtml($field, $render)
    {
        Loader::loadClass('CategoryUtil');
        $rootCat = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules/pagemaster/lists');
        $cats    = CategoryUtil::getCategoriesByParentID($rootCat['id']);

        $html = '<div class="pn-formrow">
                  <label for="pmplugin_categorylist">'._CATEGORY.':</label><select id="pmplugin_categorylist" name="pmplugin_categorylist">';

        $ak = array_keys($cats);
        foreach ($ak as $key) {
            $html .= '<option value="'.$cats[$key]['id'].'">'.$cats[$key]['name'].'</option>';
        }

        $html .= '</select>
                  </div>';

        // get the include empty element config value
        if (isset($render->_tpl_vars['typedata'])) {
            $config = explode(',', $render->_tpl_vars['typedata']);
            $this->includeEmptyElement = isset($config[1]) ? (bool)$config[1] : true;
        } else {
            $this->includeEmptyElement = true;
        }

        $checked = $this->includeEmptyElement ? 'checked="checked"' : '';
        $html .= '<div class="pn-formrow">
                    <label for="pmplugin_categoryempty">'._PAGEMASTER_INCLUDEEMPTYITEM.'</label> <input type="checkbox" id="pmplugin_categoryempty" name="pmplugin_categoryempty" '.$checked.' />
                  </div>';

        return $html;
    }
}

function smarty_function_pmformlistinput($params, &$render) {
    return $render->pnFormRegisterPlugin('pmformlistinput', $params);
}
