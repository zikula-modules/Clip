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

class PageMaster_Form_Plugin_MultiCheck extends Form_Plugin_CategoryCheckboxList
{
    public $columnDef   = 'C(512)';
    public $title;
    public $filterClass = 'pmMultiList';

    public $config;

    function __construct()
    {
        $dom = ZLanguage::getModuleDomain('PageMaster');
        //! field type name
        $this->title = __('MultiCheckbox List', $dom);

        parent::__construct();
    }

    function getFilename()
    {
        return __FILE__;
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
                ModUtil::dbInfoLoad('Categories');

                $tables          = DBUtil::getTables();
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

    function render($view)
    {
        return parent::render($view);
    }

    function create($view, &$params)
    {
        $this->saveAsString = 1;

        parent::create($view, $params);
    }

    function load($view, &$params)
    {
        if (isset($view->eventHandler->pubfields[$this->id])) {
            $this->parseConfig($view->eventHandler->pubfields[$this->id]['typedata']);
            $params['category'] = $this->config['category'];
        }

        parent::load($view, $params);

        if ($this->mandatory) {
            array_shift($this->items); //CategorySelector makes a "- - -" entry for mandatory field, what makes no sense for checkboxes
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
        
        $typedata = isset($view->_tpl_vars['typedata']) ? $view->_tpl_vars['typedata'] : 30;
        $this->parseConfig($typedata);

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
    function parseConfig($typedata='', $args=array())
    {
        $this->config = array();

        $this->config['category'] = (int)$typedata;
    }
}
