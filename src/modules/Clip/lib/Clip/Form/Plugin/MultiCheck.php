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
                $rootCat = $this->getRootCategoryID($field['typedata']);

                foreach ($cat_arr as &$cat) {
                    CategoryUtil::buildRelativePathsForCategory($rootCat, $cat);

                    // map the local display name
                    $cat['fullTitle'] = isset($cat['display_name'][$lang]) ? $cat['display_name'][$lang] : $cat['name'];
                }
            }
        }

        return $cat_arr;
    }

    public function getRootCategoryID($typedata)
    {
        $this->parseConfig($typedata);

        return $this->config['cat'];
    }

    static function getOutputDisplay($field)
    {
        $full = '    <div class="z-formrow">'."\n".
                '        <span class="z-label">{$pubfields.'.$field['name'].'|clip_translate}:</span>'."\n".
                '        {if $pubdata.'.$field['name'].'}'."\n".
                '            {*clip_multilistdecode value=$pubdata.'.$field['name'].'*}'."\n".
                '            <ul class="z-formnote">'."\n".
                '                {foreach from=$pubdata.'.$field['name'].' item=\'item\'}'."\n".
                '                    <li>{$item.fullTitle}</li>'."\n".
                '                {/foreach}'."\n".
                '            </ul>'."\n".
                '        {else}'."\n".
                '            <span class="z-formnote z-sub">{gt text=\''.no__('(empty)').'\'}</span>'."\n".
                '        {/if}'."\n".
                '    </div>';

        return array('full' => $full);
    }

    /**
     * Clip admin methods.
     */
    static function getSaveTypeDataFunc($field)
    {
        $saveTypeDataFunc = 'function saveTypeData()
                             {
                                 $(\'typedata\').value = $F(\'clipplugin_checklist\') ;
                                 $(\'typedata\').value += \'|\';
                                 if ($(\'clipplugin_editlink\') && $F(\'clipplugin_editlink\') == \'on\') {
                                     $(\'typedata\').value += 1;
                                 } else {
                                     $(\'typedata\').value += 0;
                                 }

                                 closeTypeData();
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

        $this->config = array(
            'cat'  => $typedata[0] ? (int)$typedata[0] : Clip_Util::getDefaultCategoryID(),
            'edit' => isset($typedata[1]) ? (bool)$typedata[1] : false
        );
    }
}
