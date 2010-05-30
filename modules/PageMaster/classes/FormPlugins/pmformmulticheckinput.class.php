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
    var $title;
    var $filterClass = 'pmMultiList';

    var $config;

    function __construct()
    {
        $dom = ZLanguage::getModuleDomain('PageMaster');
        //! field type name
        $this->title = __('MultiCheckbox List', $dom);

        parent::__construct();
    }

    function getFilename()
    {
        return __FILE__; // FIXME: may be found in smarty's data???
    }

    static function postRead($data, $field)
    {
        if (!empty($data) && $data <> '::') {
            $lang = ZLanguage::getLanguageCode();

            if (strpos($data, ':') === 0) {
                $data = substr($data, 1, -1);
            }

            $catIds = explode(':', $data);
            if (!empty($catIds)) {
                Loader::loadClass('CategoryUtil');
                pnModDBInfoLoad('Categories');

                $tables          = pnDBGetTables();
                $category_column = $tables['categories_category_column'];

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
        return parent::render($render);
    }

    function create(&$render, &$params)
    {
        $this->saveAsString = 1;

        parent::create($render, $params);
    }

    function load(&$render, $params)
    {
        if (isset($render->pnFormEventHandler->pubfields[$this->id])) {
            $this->parseConfig($render->pnFormEventHandler->pubfields[$this->id]['typedata']);
            $params['category'] = $this->config['category'];
        }

        parent::load($render, $params);

        if ($this->mandatory) {
            array_shift($this->items); //pnFormCategorySelector makes a "- - -" entry for mandatory field, what makes no sense for checkboxes
        }
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

    function getTypeHtml($field)
    {
        $dom = ZLanguage::getModuleDomain('PageMaster');
        
        $typedata = isset($render->_tpl_vars['typedata']) ? $render->_tpl_vars['typedata'] : 30;
        $this->parseConfig($typedata);

        Loader::loadClass('CategoryUtil');
        Loader::loadClass('CategoryRegistryUtil');

        $registered = CategoryRegistryUtil::getRegisteredModuleCategories('PageMaster', 'pagemaster_pubtypes');

        $html = ' <div class="z-formrow">
                      <label for="pmplugin_checklist">'.__('Category', $dom).':</label>
                      <select id="pmplugin_checklist" name="pmplugin_checklist">';

        $lang = ZLanguage::getLanguageCode();

        foreach ($registered as $property => $catID) {
            $cat = CategoryUtil::getCategoryByID($catID);
            $cat['fullTitle'] = isset($cat['display_name'][$lang]) ? $cat['display_name'][$lang] : $cat['name'];
            $selectedText     = ($this->config['category'] == $catID) ? ' selected="selected"' : '';

            $html .= "    <option{$selectedText} value=\"{$cat['id']}\">{$cat['fullTitle']} [{$property}]</option>";
        }

        $html .= '   </select>
                  </div>';

        return $html;
    }

    /**
     * Parse configuration
     */
    function parseConfig($typedata = '', $args = array())
    {
        $this->config = array();

        $this->config['category'] = (int)$typedata;
    }
}
