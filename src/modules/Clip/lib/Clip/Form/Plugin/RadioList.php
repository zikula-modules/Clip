<?php
/**
 * Clip
 *
 * @copyright   (c) Clip Team
 * @link        http://code.zikula.org/clip/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @version     $ Id $
 * @package     Zikula_3rdParty_Modules
 * @subpackage  clip
 */

class Clip_Form_Plugin_RadioList extends Form_Plugin_CategorySelector
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
        $this->pluginTitle = $this->__('Radio list');
    }

    function getFilename()
    {
        return __FILE__;
    }

    public function pluginRegister(&$params, &$view)
    {
        $this->setDomain($view->getDomain());
        $this->setup();

        // Copy parameters to member variables and attribute set
        $this->readParameters($view, $params);
        $this->create($view, $params);
        $this->load($view, $params);

        $this->dataBound($view, $params);

        return $this->render($view, $params);
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

    function render(&$view, $params)
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

    function load($view, &$params)
    {
        $typedata = $view->eventHandler->getPubfieldData($this->id, 'typedata');

        if (!empty($typedata)) {
            $this->parseConfig($typedata);

            $params['category'] = $this->config[0];
        } else {
            // TODO Extract the List property category root?
            $params['category'] = $this->config[0] = 30; // Global category
        }

        parent::load($view, $params);

        $this->includeEmptyElement = false;
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
                                 closeTypeData();
                             }';

        return $saveTypeDataFunc;
    }

    function getTypeHtml($field, $view)
    {
        $typedata = isset($view->_tpl_vars['typedata']) ? $view->_tpl_vars['typedata'] : 30;
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

        return $html;
    }

    /**
     * Parse configuration
     */
    function parseConfig($typedata='')
    {
        // config string: "(int)categoryID"
        $this->config = array();

        $this->config = array(
            0 => $typedata
        );
    }
}
