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

class Clip_Form_Plugin_MultiCheck extends Zikula_Form_Plugin_CategoryCheckboxList
{
    // plugin definition
    public $pluginTitle;
    public $columnDef   = 'C(512)';
    public $filterClass = 'clipmlist';
    public $config = array();

    // Clip data handling
    public $alias;
    public $tid;
    public $rid;
    public $pid;
    public $field;

    public function setup()
    {
        $this->setDomain(ZLanguage::getModuleDomain('Clip'));

        //! field type name
        $this->pluginTitle = $this->__('MultiCheckbox List');
    }

    public function getFilename()
    {
        return __FILE__;
    }

    /**
     * Form framework overrides.
     */
    public function readParameters(Zikula_Form_View $view, &$params)
    {
        $this->parseConfig($params['fieldconfig']);
        unset($params['fieldconfig']);

        $params['category'] = isset($params['category']) ? $params['category'] : $this->config['cat'];
        $params['editLink'] = isset($params['editLink']) ? $params['editLink'] : $this->config['edit'];
        $params['includeEmptyElement'] = false;

        parent::readParameters($view, $params);

        $this->saveAsString = 1;
    }

    public function load(Zikula_Form_View $view, &$params)
    {
        parent::load($view, $params);

        if ($this->mandatory) {
            // CategorySelector makes a "- - -" entry for mandatory field, what makes no sense for checkboxes
            array_shift($this->items);
        }
    }

    public function loadValue(Zikula_Form_View $view, &$values)
    {
        if ($this->dataBased) {
            $items = null;
            $value = null;

            $data = isset($values[$this->group][$this->alias][$this->tid][$this->rid][$this->pid]) ? $values[$this->group][$this->alias][$this->tid][$this->rid][$this->pid] : null;

            if ($data && isset($data[$this->field])) {
                $value = $data[$this->field];
            }
            if ($data && $this->itemsDataField && isset($data[$this->itemsDataField])) {
                $items = $data[$this->itemsDataField];
            }

            if ($items !== null) {
                $this->setItems($items);
            }

            $this->setSelectedValue($value);
        }
    }

    public function saveValue(Zikula_Form_View $view, &$data)
    {
        if ($this->dataBased) {
            if (!array_key_exists($this->group, $data)) {
                $data[$this->group] = array($this->alias => array($this->tid => array($this->rid => array($this->pid => array()))));
            }
            $data[$this->group][$this->alias][$this->tid][$this->rid][$this->pid][$this->field] = $this->getSelectedValue();
        }
    }

    /**
     * Clip processing methods.
     */
    public function enrichFilterArgs(&$filterArgs, $field, $args)
    {
        $fieldname = $field['name'];
        $filterArgs['plugins'][$this->filterClass]['fields'][] = $fieldname;
    }

    public function postRead(&$pub, $field)
    {
        $fieldname = $field['name'];
        $data = $pub[$fieldname];

        // default
        $cat_arr = array();

        // if the data is not empty, process it
        if (!empty($data) && $data <> '::') {
            $lang = ZLanguage::getLanguageCode();

            $catIds = array_filter(explode(':', $data));;
            if (!empty($catIds)) {
                ModUtil::dbInfoLoad('Categories');

                $tables = DBUtil::getTables();
                $category_column = $tables['categories_category_column'];

                $where = array();
                foreach ($catIds as $catId) {
                    $where[] = $category_column['id'].' = \''.DataUtil::formatForStore($catId).'\'';
                }

                $cat_arr = CategoryUtil::getCategories(implode(' OR ', $where), '', 'id');
                $rootCat = $this->getRootCategoryID($field['typedata']);

                foreach ($cat_arr as &$cat) {
                    CategoryUtil::buildRelativePathsForCategory($rootCat, $cat);

                    // map the local display name
                    $cat['fullTitle'] = isset($cat['display_name'][$lang]) ? $cat['display_name'][$lang] : $cat['name'];
                    $cat['fullDesc']  = isset($cat['display_desc'][$lang]) ? $cat['display_desc'][$lang] : '';
                }
            }
        }

        $pub[$fieldname] = $cat_arr;
    }

    public function getRootCategoryID($typedata)
    {
        $this->parseConfig($typedata);

        return $this->config['cat'];
    }

    public function clipAttributes($field)
    {
        return array(
            'cid' => $this->getRootCategoryID($field['typedata'])
        );
    }

    public static function getOutputDisplay($field)
    {
        $full = '        <div class="z-formrow">'."\n".
                '            <span class="z-label">{$pubfields.'.$field['name'].'|clip_translate}:</span>'."\n".
                '            {if $pubdata.'.$field['name'].'}'."\n".
                '                {*clip_multilistdecode value=$pubdata.'.$field['name'].'*}'."\n".
                '                <ul class="z-formnote">'."\n".
                '                    {foreach from=$pubdata.'.$field['name'].' item=\'item\'}'."\n".
                '                        <li>{$item.fullTitle}</li>'."\n".
                '                    {/foreach}'."\n".
                '                </ul>'."\n".
                '            {else}'."\n".
                '                <span class="z-formnote z-sub">{gt text=\''.no__('(empty)').'\'}</span>'."\n".
                '            {/if}'."\n".
                '        </div>';

        return array('full' => $full);
    }

    /**
     * Clip admin methods.
     */
    public static function getConfigSaveJSFunc($field)
    {
        return 'function()
                {
                    $(\'typedata\').value = $F(\'clipplugin_checklist\')+\'|\'+Number($F(\'clipplugin_editlink\'));

                    Zikula.Clip.Pubfields.ConfigClose();
                }';
    }

    public function getConfigHtml($field, $view)
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
                      <input type="checkbox" value="1" id="clipplugin_editlink" name="clipplugin_editlink" '.$checked.' />
                  </div>';

        return $html;
    }

    /**
     * Parse configuration
     */
    public function parseConfig($typedata='', $args=array())
    {
        // config string: "(int)categoryID|(int)editLink"
        $typedata = explode('|', $typedata);

        $this->config = array(
            'cat'  => $typedata[0] ? (int)$typedata[0] : Clip_Util::getDefaultCategoryID(),
            'edit' => isset($typedata[1]) ? (bool)$typedata[1] : false
        );
    }
}
