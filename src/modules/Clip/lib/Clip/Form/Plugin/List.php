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

class Clip_Form_Plugin_List extends Form_Plugin_CategorySelector
{
    public $pluginTitle;
    public $columnDef   = 'I4';
    public $filterClass = 'ClipList';

    public $config;

    function setup()
    {
        $dom = ZLanguage::getModuleDomain('Clip');
        $this->setDomain($dom);

        //! field type name
        $this->pluginTitle = $this->__('List');
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
                '            <span class="z-formnote">{$pubdata.'.$field['name'].'.fullTitle}<span>'."\n".
                '            <pre>{clip_array array=$pubdata.'.$field['name'].'}</pre>'."\n".
                '        </div>'."\n".
                '    {/if}';

        return array('full' => $full);
    }

    static function postRead($data, $field)
    {
        // this plugin return an array
        $cat = array();

        // if there's a value extract the category
        if (!empty($data) && is_numeric($data)) {
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

    function render($view)
    {
        $mand = ($this->mandatory == '1') ? ' <span class="z-mandatorysym">*</span>' : '';

        return parent::render($view).$mand;
    }

    function load($view, &$params)
    {
        $typedata = $view->eventHandler->getPubfieldData($this->id, 'typedata');
        if (!empty($typedata)) {
            $this->parseConfig($typedata, (int)$params['mandatory']);

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

        parent::load($view, $params);
    }

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
                                 closeTypeData();
                             }';

        return $saveTypeDataFunc;
    }

    function getTypeHtml($field, $view)
    {
        $typedata = isset($view->_tpl_vars['typedata']) ? $view->_tpl_vars['typedata'] : serialize(array(30, true));
        $this->parseConfig($typedata);

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

        $checked = $this->config[1] ? 'checked="checked"' : '';
        $html .= '<div class="z-formrow">
                      <label for="clipplugin_categoryempty">'.$this->__('Include an empty item?').'</label>
                      <input type="checkbox" id="clipplugin_categoryempty" name="clipplugin_categoryempty" '.$checked.' />
                  </div>';

        return $html;
    }

    /**
     * Parse configuration
     */
    function parseConfig($typedata='', $args=array())
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
