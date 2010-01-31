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

class pmformmultilistinput extends pnFormCategorySelector
{
    var $columnDef   = 'C(512)';
    var $title;
    var $filterClass = 'pmMultiList';

    function __construct()
    {
        $dom = ZLanguage::getModuleDomain('pagemaster');
        //! field type name
        $this->title = __('Multiple selector', $dom);

        parent::__construct();
    }

    function getFilename()
    {
        return __FILE__; // FIXME: may be found in smarty's data???
    }

    static function postRead($data, $field)
    {
        // this plugin return an array by default
        $cat_arr = array();

        // if the data is not empty, process it
        if (!empty($data) && $data <> '::') {
            $lang = ZLanguage::getLanguageCode();

            // the data is of the form:
            // :cid1:cid2:cid3:cid4:
            if (strpos($data, ':') === 0) {
                $data = substr($data, 1, -1);
            }

            $catIds = explode(':', $data);
            if (!empty($catIds)) {
                Loader::loadClass('CategoryUtil');
                pnModDBInfoLoad('Categories');

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
        // extract the configuration {category, size}
        $config = array(30, '~');
        if (isset($render->pnFormEventHandler->pubfields[$this->inputName])) {
            $config = explode('|', $render->pnFormEventHandler->pubfields[$this->inputName]['typedata']);
            if (!isset($config[1])) {
                $config[1] = '~';
            }
        }

        if ($config[1] != '~') {
            $this->size = $config[1];
        }

        return parent::render($render);
    }

    function create(&$render, &$params)
    {
        $this->saveAsString = 1;
        $this->selectionMode = 'multiple';

        parent::create($render, $params);
    }

    function load(&$render, $params)
    {
        if (isset($render->pnFormEventHandler->pubfields[$this->id])) {
            $config = explode('|', $render->pnFormEventHandler->pubfields[$this->id]['typedata']);
            $params['category'] = $config[0];
        }

        parent::load(&$render, $params);

        array_shift($this->items); //pnFormCategorySelector makes a "- - -" entry for mandatory field, what makes no sense for checkboxes
    }

    static function getSaveTypeDataFunc($field)
    {
        $saveTypeDataFunc = 'function saveTypeData()
                             {
                                 var config = new Array()
                                 config.push($F(\'pmplugin_categorylist\'))

                                 if (parseInt($F(\'pmplugin_multisize\')) != NaN && parseInt($F(\'pmplugin_multisize\')) > 0) {
                                     config.push($F(\'pmplugin_multisize\'));
                                 } else {
                                     config.push(\'~\');
                                 }

                                 $(\'typedata\').value = config.join(\'|\')
                                 closeTypeData();
                             }';

        return $saveTypeDataFunc;
    }

    static function getTypeHtml($field, $render)
    {
        $dom = ZLanguage::getModuleDomain('pagemaster');

        // parse the configuration
        if (isset($render->_tpl_vars['typedata'])) {
            $vars = explode('|', $render->_tpl_vars['typedata']);
        } else {
            $vars = array();
        }

        $size = null;
        if (!empty($vars) && isset($vars[1]) && $vars[1] > 0) {
            $size = $vars[1];
        }

        Loader::loadClass('CategoryUtil');
        Loader::loadClass('CategoryRegistryUtil');

        $registered = CategoryRegistryUtil::getRegisteredModuleCategories('pagemaster', 'pagemaster_pubtypes');

        $html = '<div class="z-formrow">
                     <label for="pmplugin_categorylist">'.__('Category', $dom).':</label>
                     <select id="pmplugin_categorylist" name="pmplugin_categorylist">';

        $lang = ZLanguage::getLanguageCode();

        foreach ($registered as $property => $catID) {
            $cat = CategoryUtil::getCategoryByID($catID);
            $cat['fullTitle'] = isset($cat['display_name'][$lang]) ? $cat['display_name'][$lang] : $cat['name'];

            $html .= "    <option value=\"{$cat['id']}\">{$cat['fullTitle']} [{$property}]</option>";
        }

        $html .= '    </select>
                  </div>
                  <div class="z-formrow">
                      <label for="pmplugin_multisize">'.__('Size', $dom).':</label>
                      <input type="text" id="pmplugin_multisize" name="pmplugin_multisize" size="2" maxlength="2" value="'.$size.'" />
                  </div>';

        return $html;
    }

    /**
     * these two methods are others in pnFormCategoryCheckboxList
     * then pnFormCategorySelector(original pnForm classes).
     * To be able to switch form multilsit to checkbox
     * it is important that both act the same way.
     */
    function getSelectedValue()
    {
        return ':'.parent::getSelectedValue().':';
    }

    function setSelectedValue($value)
    {
        if (is_string($value)) {
            $value = split(':', $value);
        }

        $this->selectedValue = $value;
        $this->selectedIndex = 0;
    }
}
