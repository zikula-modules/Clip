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

class PageMaster_Form_Plugin_List extends Form_Plugin_CategorySelector
{
    public $columnDef   = 'I4';
    public $title;
    public $filterClass = 'pmList';

    public $config;

    function __construct()
    {
        $dom = ZLanguage::getModuleDomain('PageMaster');
        //! field type name
        $this->title = __('List', $dom);

        parent::__construct();
    }

    function getFilename()
    {
        return __FILE__;
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
        if (!empty($render->EventHandler->pubfields[$this->id]['typedata'])) {
            $this->parseConfig($render->EventHandler->pubfields[$this->id]['typedata'], (int)$params['mandatory']);

            $params['category'] = $this->config[0];

            if (!isset($params['includeEmptyElement'])) {
                $this->includeEmptyElement = $this->config[1];
            } else {
                $this->includeEmptyElement = $params['includeEmptyElement'];
            }
        } else {
            // TODO Extract the List property category root?
            $params['category'] = 30; // Global category
        }

        parent::load($render, $params);
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

    function getTypeHtml($field, $render)
    {
        $dom = ZLanguage::getModuleDomain('PageMaster');

        $typedata = isset($render->_tpl_vars['typedata']) ? $render->_tpl_vars['typedata'] : array(30, true);
        $this->parseConfig($typedata);

        Loader::loadClass('CategoryUtil');

        Loader::loadClass('CategoryUtil');
        Loader::loadClass('CategoryRegistryUtil');

        $registered = CategoryRegistryUtil::getRegisteredModuleCategories('PageMaster', 'pagemaster_pubtypes');

        $html = ' <div class="z-formrow">
                      <label for="pmplugin_categorylist">'.__('Category', $dom).':</label>
                      <select id="pmplugin_categorylist" name="pmplugin_categorylist">';

        $lang = ZLanguage::getLanguageCode();

        foreach ($registered as $property => $catID) {
            $cat = CategoryUtil::getCategoryByID($catID);
            $cat['fullTitle'] = isset($cat['display_name'][$lang]) ? $cat['display_name'][$lang] : $cat['name'];
            $selectedText     = ($this->config[0] == $catID) ? ' selected="selected"' : '';

            $html .= "    <option{$selectedText} value=\"{$cat['id']}\">{$cat['fullTitle']} [{$property}]</option>";
        }

        $html .= '    </select>
                  </div>';

        $checked = $this->config[1] ? 'checked="checked"' : '';
        $html .= '<div class="z-formrow">
                      <label for="pmplugin_categoryempty">'.__('Include an empty item?', $dom).'</label>
                      <input type="checkbox" id="pmplugin_categoryempty" name="pmplugin_categoryempty" '.$checked.' />
                  </div>';

        return $html;
    }

    /**
     * Parse configuration
     */
    function parseConfig($typedata = '', $args = array())
    {
        // config string: "(int)categoryID, (bool)includeEmpty"
        $this->config = array();

        $this->config = explode(',', $typedata);
        $this->config = array(
            0 => (int)$this->config[0],
            1 => isset($this->config[1]) ? (bool)$this->config[1] : (bool)$args
        );
    }
}
