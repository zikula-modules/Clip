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

class Clip_Form_Plugin_RadioList extends Form_Plugin_CategorySelector
{
    public $pluginTitle;
    public $columnDef   = 'I4';
    public $filterClass = 'cliplist';

    public $config = array();

    function setup()
    {
        $this->setDomain(ZLanguage::getModuleDomain('Clip'));

        //! field type name
        $this->pluginTitle = $this->__('Radio list');
    }

    function getFilename()
    {
        return __FILE__;
    }

    /**
     * Form Framework methods.
     */
    public function pluginRegister(&$params, &$view)
    {
        $this->setDomain($view->getDomain()); // TODO remove, Form_Plugin responsability
        $this->setup();

        // copy parameters to member variables and attribute set
        $this->readParameters($view, $params);
        $this->create($view, $params);
        $this->load($view, $params);

        $this->dataBound($view, $params);

        return $this->renderRadioList($view, $params);
    }

    function readParameters($view, &$params)
    {
        $this->parseConfig($view->eventHandler->getPubfieldData($params['id'], 'typedata'));

        $params['category'] = isset($params['category']) ? $params['category'] : $this->config['cat'];
        $params['editLink'] = isset($params['editLink']) ? $params['editLink'] : $this->config['edit'];
        $params['includeEmptyElement'] = false;

        parent::readParameters($view, $params);
    }

    function load($view, &$params)
    {
        parent::load($view, $params);

        if ($this->mandatory) {
            // CategorySelector makes a "- - -" entry for mandatory field, what makes no sense for checkboxes
            array_shift($this->items);
        }
    }

    function renderRadioList(&$view, $params)
    {
        $id = $params['id'];
        unset($params['maxLength']);
        unset($params['category']);

        $output = '';
        foreach ($this->items as $item) {
            $output .= '<div class="z-formlist">'."\n";

            $params['id']        = 'clip_radio_'.$id.$item['value'];
            $params['dataField'] = $id;
            $params['groupName'] = $this->inputName;
            $params['value']     = $item['value'];

            $output .= $view->registerPlugin('Form_Plugin_RadioButton', $params);

            $args = array(
                'for'  => $params['id'],
                'text' => $item['text']
            );

            $output .= $view->registerPlugin('Form_Plugin_Label', $args);

            $output .= '</div>'."\n";
        }

        if ($this->editLink && !empty($this->category) && SecurityUtil::checkPermission('Categories::', "$this->category[id]::", ACCESS_EDIT)) {
            $url = DataUtil::formatForDisplay(ModUtil::url('Categories', 'user', 'edit', array('dr' => $this->category['id'])));
            $output .= "<a href=\"{$url}\"><img src=\"images/icons/extrasmall/xedit.gif\" title=\"" . __('Edit') . '" alt="' . __('Edit') . '" /></a>';
        }

        return $output;
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

            if (empty($cat)) {
                return $cat;
            }

            $lang = ZLanguage::getLanguageCode();

            // compatible mode to pagesetter
            $cat['fullTitle'] = isset($cat['display_name'][$lang]) ? $cat['display_name'][$lang] : $cat['name'];
            $cat['value']     = $cat['name'];
            $cat['title']     = $cat['name'];
        }

        return $cat;
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
                      <label for="clipplugin_categorylist">'.$this->__('Category').':</label>
                      <select id="clipplugin_categorylist" name="clipplugin_categorylist">';

        $lang = ZLanguage::getLanguageCode();

        foreach ($registered as $property => $catID) {
            $cat = CategoryUtil::getCategoryByID($catID);
            $cat['fullTitle'] = isset($cat['display_name'][$lang]) ? $cat['display_name'][$lang] : $cat['name'];
            $selectedText     = ($this->config['cat'] == $catID) ? ' selected="selected"' : '';

            $html .= "    <option{$selectedText} value=\"{$cat['id']}\">{$cat['fullTitle']} [{$property}]</option>";
        }

        $html .= '    </select>
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
    function parseConfig($typedata='')
    {
        // config string: "(int)categoryID|(int)editLink"
        $typedata = explode('|', $typedata);

        $this->config = array(
            'cat'  => $typedata[0] ? (int)$typedata[0] : 32,
            'edit' => isset($typedata[1]) ? (bool)$typedata[1] : false
        );
    }
}
