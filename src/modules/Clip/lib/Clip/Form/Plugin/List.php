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

class Clip_Form_Plugin_List extends Zikula_Form_Plugin_CategorySelector
{
    public $pluginTitle;
    public $columnDef   = 'I4';
    public $filterClass = 'cliplist';

    public $config = array();

    function setup()
    {
        $this->setDomain(ZLanguage::getModuleDomain('Clip'));
        
        //! field type name
        $this->pluginTitle = $this->__('List');
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

        $params['category'] = isset($params['category']) ? $params['category'] : $this->config[0];
        $params['includeEmptyElement'] = isset($params['includeEmptyElement']) ? $params['includeEmptyElement'] : $this->config[1];
        $params['editLink'] = isset($params['editLink']) ? $params['editLink'] : $this->config[2];

        parent::readParameters($view, $params);
    }

    /**
     * Clip processing methods.
     */
    static function postRead($data, $field)
    {
        // this plugin return an array
        $cat = array();

        // if there's a value extract the category
        if (!empty($data) && is_numeric($data)) {
            $cat = CategoryUtil::getCategoryByID($data);

            if (!$cat) {
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

    public function getRootCategoryID($typedata)
    {
        $this->parseConfig($typedata);

        return $this->config[0];
    }

    static function getPluginOutput($field)
    {
        $full = '    {if !empty($pubdata.'.$field['name'].')}'."\n".
                '        <div class="z-formrow">'."\n".
                '            <span class="z-label">{gt text=\''.$field['title'].'\'}:</span>'."\n".
                '            <span class="z-formnote">{$pubdata.'.$field['name'].'.fullTitle}<span>'."\n".
                '            <pre>{clip_array array=$pubdata.'.$field['name'].'}</pre>'."\n".
                '        </div>'."\n".
                '    {/if}';

        return array('full' => $full);
    }

    /**
     * Clip admin methods.
     */
    static function getSaveTypeDataFunc($field)
    {
        $saveTypeDataFunc = 'function saveTypeData()
                             {
                                 if ($F(\'clipplugin_categorylist\') != null) {
                                     $(\'typedata\').value = $F(\'clipplugin_categorylist\');
                                 } else {
                                     $(\'typedata\').value = 30;
                                 }
                                 $(\'typedata\').value += \',\';
                                 if ($F(\'clipplugin_categoryempty\') == \'on\') {
                                     $(\'typedata\').value += 1;
                                 } else {
                                     $(\'typedata\').value += 0;
                                 }
                                 $(\'typedata\').value += \',\';
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

        // category selector
        $registered = CategoryRegistryUtil::getRegisteredModuleCategories('Clip', 'clip_pubtypes');

        $html = ' <div class="z-formrow">
                      <label for="clipplugin_categorylist">'.$this->__('Category').':</label>
                      <select id="clipplugin_categorylist" name="clipplugin_categorylist">';

        $lang = ZLanguage::getLanguageCode();

        foreach ($registered as $property => $catID) {
            $cat = CategoryUtil::getCategoryByID($catID);
            $cat['fullTitle'] = isset($cat['display_name'][$lang]) ? $cat['display_name'][$lang] : $cat['name'];
            $selectedText     = ($this->config[0] == $catID) ? ' selected="selected"' : '';

            $html .= "    <option{$selectedText} value=\"{$cat['id']}\">{$cat['fullTitle']} [{$property}]</option>";
        }

        $html .= '    </select>
                  </div>';

        // empty item checkbox
        $checked = $this->config[1] ? 'checked="checked"' : '';
        $html .= '<div class="z-formrow">
                      <label for="clipplugin_categoryempty">'.$this->__('Include an empty item?').'</label>
                      <input type="checkbox" id="clipplugin_categoryempty" name="clipplugin_categoryempty" '.$checked.' />
                  </div>';

        // edit link checkbox
        $checked = $this->config[2] ? 'checked="checked"' : '';
        $html .= '<div class="z-formrow">
                      <label for="clipplugin_editlink">'.$this->__('Edit link').':</label>
                      <input type="checkbox" id="clipplugin_editlink" name="clipplugin_editlink" '.$checked.' />
                  </div>';

        return $html;
    }

    /**
     * Parse configuration
     */
    function parseConfig($typedata='')
    {
        // config string: "(int)categoryID,(bool)includeEmpty,(bool)editLink"
        $typedata = explode(',', $typedata);

        $this->config = array(
            0 => !empty($typedata[0]) ? (int)$typedata[0] : Clip_Util::getDefaultCategoryID(),
            1 => isset($typedata[1]) ? (bool)$typedata[1] : false,
            2 => isset($typedata[2]) ? (bool)$typedata[2] : false
        );
    }
}
