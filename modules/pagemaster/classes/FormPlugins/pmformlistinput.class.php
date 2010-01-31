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
    var $columnDef   = 'I4';
    var $title;
    var $filterClass = 'pmList';

    function __construct()
    {
        $dom = ZLanguage::getModuleDomain('pagemaster');
        //! field type name
        $this->title = __('List', $dom);

        parent::__construct();
    }

    function getFilename()
    {
        return __FILE__; // FIXME: may be found in smarty's data???
    }

    static function postRead($data, $field)
    {
        // this plugin return an array
        $cat = array();

        // if there's a value extract the category
        if (!empty($data) && is_numeric($data)) {
            Loader::loadClass('CategoryUtil');

            $cat  = CategoryUtil::getCategoryByID($data);

            if (empty($cat)) {
                return array();
            }

            $lang = ZLanguage::getLanguageCode();

            // compatible mode to pagesetter
            $cat['fullTitle'] = isset($cat['display_name'][$lang]) ? $cat['display_name'][$lang] : $cat['name'];
            $cat['value']     = $cat['name'];
            $cat['title']     = $cat['name'];
        }

        return $cat;
    }

    function render(&$render)
    {
        $mand = ($this->mandatory == '1') ? ' <span class="z-mandatorysym">*</span>' : '';

        return parent::render($render).$mand;
    }

    function load(&$render, $params)
    {
        if (!empty($render->pnFormEventHandler->pubfields[$this->id]['typedata'])) {
            // config is: {categoryID, (bool)includeEmpty}
            $config = explode(',', $render->pnFormEventHandler->pubfields[$this->id]['typedata']);
            $params['category'] = $config[0];

            if (!isset($params['includeEmptyElement'])) {
                if (isset($config[1])) {
                    $this->includeEmptyElement = (bool)$config[1];
                } elseif ($params['mandatory'] == '0') {
                    $this->includeEmptyElement = 1;
                } else {
                    $this->includeEmptyElement = 0;
                }
            } else {
                $this->includeEmptyElement = $params['includeEmptyElement'];
            }
        } else {
            $params['category'] = 30; // Global category
        }

        parent::load(&$render, $params);
    }

    static function getSaveTypeDataFunc($field)
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

    static function getTypeHtml($field, $render)
    {
        $dom = ZLanguage::getModuleDomain('pagemaster');

        Loader::loadClass('CategoryUtil');

        Loader::loadClass('CategoryUtil');
        Loader::loadClass('CategoryRegistryUtil');

        $registered = CategoryRegistryUtil::getRegisteredModuleCategories('pagemaster', 'pagemaster_pubtypes');

        $html = '<div class="z-formrow">
                  <label for="pmplugin_categorylist">'.__('Category', $dom).':</label><select id="pmplugin_categorylist" name="pmplugin_categorylist">';

        $lang = ZLanguage::getLanguageCode();

        foreach ($registered as $property => $catID) {
            $cat = CategoryUtil::getCategoryByID($catID);
            $cat['fullTitle'] = isset($cat['display_name'][$lang]) ? $cat['display_name'][$lang] : $cat['name'];

            $html .= "<option value=\"{$cat['id']}\">{$cat['fullTitle']} [{$property}]</option>";
        }

        $html .= '</select>
                  </div>';

        // get the include empty element config value
        if (isset($render->_tpl_vars['typedata'])) {
            $config = explode(',', $render->_tpl_vars['typedata']);
            $includeEmptyElement = isset($config[1]) ? (bool)$config[1] : true;
        } else {
            $includeEmptyElement = true;
        }

        $checked = $includeEmptyElement ? 'checked="checked"' : '';
        $html .= '<div class="z-formrow">
                    <label for="pmplugin_categoryempty">'.__('Include an empty item?', $dom).'</label> <input type="checkbox" id="pmplugin_categoryempty" name="pmplugin_categoryempty" '.$checked.' />
                  </div>';

        return $html;
    }
}
