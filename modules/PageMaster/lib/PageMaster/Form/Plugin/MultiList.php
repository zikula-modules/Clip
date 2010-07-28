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

class PageMaster_Form_Plugin_MultiList extends Form_Plugin_CategorySelector
{
    public $pluginTitle;
    public $columnDef   = 'C(512)';
    public $filterClass = 'pmMultiList';

    public $config;

    function setup()
    {
        $dom = ZLanguage::getModuleDomain('PageMaster');
        $this->setDomain($dom);

        //! field type name
        $this->pluginTitle = $this->__('Multiple Selector');
    }

    function getFilename()
    {
        return __FILE__;
    }

    static function getPluginOutput($field)
    {
        $full = '    {if !empty($pubdata.'.$field['name'].')}'."\n".
                '        <div class="z-formrow">'."\n".
                '            <span class="z-label">{gt text=\''.$field['title'].'\'}:</span>'."\n".
                '            <span class="z-formnote">'."\n".
                '                <ul>'."\n".
                '                    {foreach from=$pubdata.'.$field['name'].' item=\'item\'}'."\n".
                '                        <li>{$item.fullTitle}</li>'."\n".
                '                    {/foreach}'."\n".
                '                </ul>'."\n".
                '            <span>'."\n".
                '        </div>'."\n".
                '    {/if}';

        return array('full' => $full);
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

    function render($view)
    {
        // extract the configuration {category, size}
        if (isset($view->eventHandler->pubfields[$this->inputName])) {
            $this->parseConfig($view->eventHandler->pubfields[$this->inputName]['typedata']);;
        } else {
            $this->parseConfig();
        }

        if (!empty($this->config[1])) {
            $this->size = $this->config[1];
        }

        return parent::render($view);
    }

    function create($view, &$params)
    {
        $this->saveAsString  = 1;
        $this->selectionMode = 'multiple';

        parent::create($view, $params);
    }

    function load($view, &$params)
    {
        if (isset($view->eventHandler->pubfields[$this->id])) {
            $this->parseConfig($view->eventHandler->pubfields[$this->id]['typedata']);
            $params['category'] = $this->config[0];
        }

        parent::load($view, $params);

        if ($this->mandatory) {
            // CategorySelector makes a "- - -" entry for mandatory field, what makes no sense for checkboxes
            array_shift($this->items);
        }
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

    function getTypeHtml($field, $view)
    {
        // parse the configuration
        $typedata = isset($view->_tpl_vars['typedata']) ? $view->_tpl_vars['typedata'] : '';
        $this->parseConfig($typedata);

        $size = '';
        if ($this->config[1] > 0) {
            $size = $this->config[1];
        }

        $registered = CategoryRegistryUtil::getRegisteredModuleCategories('PageMaster', 'pagemaster_pubtypes');

        $html = '<div class="z-formrow">
                     <label for="pmplugin_categorylist">'.$this->__('Category').':</label>
                     <select id="pmplugin_categorylist" name="pmplugin_categorylist">';

        $lang = ZLanguage::getLanguageCode();

        foreach ($registered as $property => $catID) {
            $cat = CategoryUtil::getCategoryByID($catID);
            $cat['fullTitle'] = isset($cat['display_name'][$lang]) ? $cat['display_name'][$lang] : $cat['name'];
            $selectedText     = ($this->config[0] == $catID) ? ' selected="selected"' : '';

            $html .= "    <option{$selectedText} value=\"{$cat['id']}\">{$cat['fullTitle']} [{$property}]</option>";
        }

        $html .= '    </select>
                  </div>
                  <div class="z-formrow">
                      <label for="pmplugin_multisize">'.$this->__('Size').':</label>
                      <input type="text" id="pmplugin_multisize" name="pmplugin_multisize" size="2" maxlength="2" value="'.$size.'" />
                  </div>';

        return $html;
    }

    /**
     * Parse configuration
     */
    function parseConfig($typedata='', $args=array())
    {
        $this->config = explode('|', $typedata);

        $this->config = array(
            0 => !empty($this->config[0]) ? (int)$this->config[0] : 30, // TODO Category Registry?
            1 => (isset($this->config[1]) && !empty($this->config[1]) && $this->config[1] != '~') ? (int)$this->config[1] : 0
        );
    }

    /**
     * These two methods are others in CategoryCheckboxList
     * then CategorySelector(original Form classes).
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
