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

class Clip_Form_Plugin_RadioList extends Zikula_Form_Plugin_CategorySelector
{
    // plugin definition
    public $pluginTitle;
    public $columnDef   = 'I4';
    public $filterClass = 'cliplist';
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
        $this->pluginTitle = $this->__('Radio List');
    }

    public function getFilename()
    {
        return __FILE__;
    }

    /**
     * Form framework overrides.
     */
    public function pluginRegister(&$params, &$view)
    {
        $this->setDomain($view->getDomain());
        $this->setup();

        // copy parameters to member variables and attribute set
        $this->readParameters($view, $params);
        $this->create($view, $params);
        $this->load($view, $params);

        $this->dataBound($view, $params);

        return $this->renderRadioList($view, $params);
    }

    public function readParameters(Zikula_Form_View $view, &$params)
    {
        $this->parseConfig($params['fieldconfig']);
        unset($params['fieldconfig']);

        $params['category'] = isset($params['category']) ? $params['category'] : $this->config['cat'];
        $params['editLink'] = isset($params['editLink']) ? $params['editLink'] : $this->config['edit'];
        $params['includeEmptyElement'] = false;

        parent::readParameters($view, $params);
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

    public function renderRadioList(&$view, $params)
    {
        $id = $params['id'];
        unset($params['maxLength']);
        unset($params['category']);

        $output = '';
        foreach ($this->items as $item) {
            $output .= '<div class="z-formlist">'."\n";

            $params['id']        = 'radio_'.$id.$item['value'];
            $params['dataField'] = $id;
            $params['groupName'] = $this->inputName;
            $params['value']     = $item['value'];

            $output .= $view->registerPlugin('Clip_Form_Plugin_RadioButton', $params);

            $args = array(
                'for'  => $params['id'],
                'text' => $item['text']
            );

            $output .= $view->registerPlugin('Zikula_Form_Plugin_Label', $args);

            $output .= '</div>'."\n";
        }

        if ($this->editLink && !empty($this->category) && SecurityUtil::checkPermission('Categories::', "{$this->category['id']}::", ACCESS_EDIT)) {
            $url = DataUtil::formatForDisplay(ModUtil::url('Categories', 'user', 'edit', array('dr' => $this->category['id'])));
            $output .= "<a href=\"{$url}\"><img src=\"images/icons/extrasmall/xedit.png\" title=\"" . __('Edit') . '" alt="' . __('Edit') . '" /></a>';
        }

        return $output;
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
        $cat = array('id' => 0);

        // if there's a value extract the category
        if (!empty($data) && is_numeric($data)) {
            $cat = CategoryUtil::getCategoryByID($data);

            if ($cat) {
                CategoryUtil::buildRelativePathsForCategory($this->getRootCategoryID($field['typedata']), $cat);

                // map the local display name
                $lang = ZLanguage::getLanguageCode();
                $cat['fullTitle'] = isset($cat['display_name'][$lang]) ? $cat['display_name'][$lang] : $cat['name'];
                $cat['fullDesc']  = isset($cat['display_desc'][$lang]) ? $cat['display_desc'][$lang] : '';
            }
        }

        $pub[$fieldname] = $cat;
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
                '            {if $pubdata.'.$field['name'].'.id}'."\n".
                '                <span class="z-formnote">{$pubdata.'.$field['name'].'.fullTitle}</span>'."\n".
                '            {/if}'."\n".
                '            <pre class="z-formnote">{clip_dump var=$pubdata.'.$field['name'].'}</pre>'."\n".
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
                    if ($F(\'clipplugin_categorylist\') != null) {
                        $(\'typedata\').value = $F(\'clipplugin_categorylist\');
                    } else {
                        $(\'typedata\').value = '.Clip_Util::getDefaultCategoryID().';
                    }
                    $(\'typedata\').value += \'|\'+Number($F(\'clipplugin_editlink\'));

                    Zikula.Clip.Pubfields.ConfigClose();
                }';
    }

    public function getConfigHtml($field, $view)
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
                      <input type="checkbox" value="1" id="clipplugin_editlink" name="clipplugin_editlink" '.$checked.' />
                  </div>';

        return $html;
    }

    /**
     * Parse configuration
     */
    public function parseConfig($typedata='')
    {
        // config string: "(int)categoryID|(int)editLink"
        $typedata = explode('|', $typedata);

        $this->config = array(
            'cat'  => $typedata[0] ? (int)$typedata[0] : Clip_Util::getDefaultCategoryID(),
            'edit' => isset($typedata[1]) ? (bool)$typedata[1] : false
        );
    }
}
