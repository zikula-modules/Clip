<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Form_Plugin
 */

class Clip_Form_Plugin_MultiCheck extends Form_Plugin_CategoryCheckboxList
{
    public $pluginTitle;
    public $columnDef   = 'C(512)';
    public $filterClass = 'clipmlist';

    public $config = array();

    function setup()
    {
        $this->setDomain(ZLanguage::getModuleDomain('Clip'));
        
        //! field type name
        $this->pluginTitle = $this->__('MultiCheckbox List');
    }

    function getFilename()
    {
        return __FILE__;
    }

    /**
     * Form Framework methods.
     */
    function readParameters($view, &$params)
    {
        $this->parseConfig($view->eventHandler->getPubfieldData($params['id'], 'typedata'));

        $params['category'] = isset($params['category']) ? $params['category'] : $this->config['cat'];
        $params['editLink'] = isset($params['editLink']) ? $params['editLink'] : $this->config['edit'];
        $params['includeEmptyElement'] = false;

        parent::readParameters($view, $params);

        $this->saveAsString = 1;
    }

    function load($view, &$params)
    {
        parent::load($view, $params);

        if ($this->mandatory) {
            // CategorySelector makes a "- - -" entry for mandatory field, what makes no sense for checkboxes
            array_shift($this->items);
        }
    }

    /**
     * Clip processing methods.
     */
    static function postRead($data, $field)
    {
        // this plugin return an array by default
        $cat_arr = array();

        if (!empty($data) && $data <> '::') {
            $lang = ZLanguage::getLanguageCode();

            if (strpos($data, ':') === 0) {
                $data = substr($data, 1, -1);
            }

            $catIds = explode(':', $data);
            if (!empty($catIds)) {
                ModUtil::dbInfoLoad('Categories');

                $tables = DBUtil::getTables();

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

    /**
     * Clip admin methods.
     */
    static function getSaveTypeDataFunc($field)
    {
        $saveTypeDataFunc = 'function saveTypeData()
                             {
                                 $(\'typedata\').value = $F(\'clipplugin_checklist\') ;
                                 closeTypeData();
                                 $(\'typedata\').value += \'|\';
                                 if ($(\'clipplugin_editlink\') && $F(\'clipplugin_editlink\') == \'on\') {
                                     $(\'typedata\').value += 1;
                                 } else {
                                     $(\'typedata\').value += 0;
                                 }
                             }';

        return $saveTypeDataFunc;
    }

    function getTypeHtml($field, $view)
    {
        $this->parseConfig($view->_tpl_vars['field']['typedata']);

        $registered = CategoryRegistryUtil::getRegisteredModuleCategories('Clip', 'clip_pubtypes');

        // category selector
        $html = ' <div class="z-formrow">
                      <label for="clipplugin_checklist">'.$this->__('Category').':</label>
                      <select id="clipplugin_checklist" name="clipplugin_checklist">';

        $lang = ZLanguage::getLanguageCode();

        foreach ($registered as $property => $catID) {
            $cat = CategoryUtil::getCategoryByID($catID);
            $cat['fullTitle'] = isset($cat['display_name'][$lang]) ? $cat['display_name'][$lang] : $cat['name'];
            $selectedText     = ($this->config['cat'] == $catID) ? ' selected="selected"' : '';

            $html .= "    <option{$selectedText} value=\"{$cat['id']}\">{$cat['fullTitle']} [{$property}]</option>";
        }

        $html .= '   </select>
                  </div>';

        // edit link checkbox
        $checked = $this->config['edit'] ? 'checked="checked"' : '';
        $html .= '<div class="z-formrow">
                      <label for="clipplugin_editlink">'.$this->__('Edit link').':</label>
                      <input type="checkbox" id="clipplugin_editlink" name="clipplugin_editlink" '.$checked.' />
                  </div>';

        return $html;
    }

    /**
     * Parse configuration
     */
    function parseConfig($typedata='', $args=array())
    {
        // config string: "(int)categoryID|(int)editLink"
        $typedata = explode('|', $typedata);

        $default = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules/Global');

        $this->config = array(
            'cat'  => $typedata[0] ? (int)$typedata[0] : $default['id'],
            'edit' => isset($typedata[1]) ? (bool)$typedata[1] : false
        );
    }
}
