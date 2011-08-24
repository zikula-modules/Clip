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

class Clip_Form_Plugin_MultiList extends Zikula_Form_Plugin_CategorySelector
{
    public $pluginTitle;
    public $columnDef   = 'C(512)';
    public $filterClass = 'clipmlist';

    public $config = array();

    public function setup()
    {
        $this->setDomain(ZLanguage::getModuleDomain('Clip'));
        
        //! field type name
        $this->pluginTitle = $this->__('Multiple Selector');
    }

    public function getFilename()
    {
        return __FILE__;
    }

    /**
     * Form Framework methods.
     */
    public function readParameters($view, &$params)
    {
        $this->parseConfig($view->eventHandler->getPubfieldData($params['id'], 'typedata'));

        $params['category'] = isset($params['category']) ? $params['category'] : $this->config[0];
        $params['size']     = isset($params['size']) ? $params['size'] : $this->config[1];
        $params['editLink'] = isset($params['editLink']) ? $params['editLink'] : $this->config[2];

        parent::readParameters($view, $params);

        $this->saveAsString  = 1;
        $this->selectionMode = 'multiple';
    }

    public function load($view, &$params)
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
            // the data is on the format:
            // :cid1:cid2:cid3:cid4:
            $catIds = array_filter(explode(':', $data));
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

                $lang = ZLanguage::getLanguageCode();

                foreach ($cat_arr as &$cat) {
                    CategoryUtil::buildRelativePathsForCategory($rootCat, $cat);

                    // map the local display name
                    $cat['fullTitle'] = isset($cat['display_name'][$lang]) ? $cat['display_name'][$lang] : $cat['name'];
                }
            }
        }

        $pub[$fieldname] = $cat_arr;
    }

    public function getRootCategoryID($typedata)
    {
        $this->parseConfig($typedata);

        return $this->config[0];
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
                    var config = new Array()
                    config.push($F(\'clipplugin_categorylist\'))

                    if (parseInt($F(\'clipplugin_multisize\')) != NaN && parseInt($F(\'clipplugin_multisize\')) > 0) {
                        config.push($F(\'clipplugin_multisize\'));
                    } else {
                        config.push(\'~\');
                    }
                    if ($(\'clipplugin_editlink\') && $F(\'clipplugin_editlink\') == \'on\') {
                        config.push(\'1\');
                    } else {
                        config.push(\'0\');
                    }
                    $(\'typedata\').value = config.join(\'|\')

                    Zikula.Clip.Pubfields.ConfigClose();
                }';
    }

    public function getConfigHtml($field, $view)
    {
        // parse the configuration
        $this->parseConfig($view->_tpl_vars['field']['typedata']);

        $registered = CategoryRegistryUtil::getRegisteredModuleCategories('Clip', 'clip_pubtypes');

        // category selector
        $html = '<div class="z-formrow">
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

        // size input
        $html .= '<div class="z-formrow">
                      <label for="clipplugin_multisize">'.$this->__('Size').':</label>
                      <input type="text" id="clipplugin_multisize" name="clipplugin_multisize" size="2" maxlength="2" value="'.$this->config[1].'" />
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
    public function parseConfig($typedata='', $args=array())
    {
        // config string: "(int)categoryID|(int)size"
        $typedata = explode('|', $typedata);

        $this->config = array(
            0 => !empty($typedata[0]) ? (int)$typedata[0] : Clip_Util::getDefaultCategoryID(),
            1 => (isset($typedata[1]) && !empty($typedata[1]) && $typedata[1] != '~') ? (int)$typedata[1] : null,
            2 => isset($typedata[2]) ? (bool)$typedata[2] : false
        );
    }

    /**
     * These two methods are others in CategoryCheckboxList
     * then CategorySelector (original Form classes).
     * To be able to switch form multilsit to checkbox
     * it is important that both act the same way.
     */
    public function getSelectedValue()
    {
        return ':'.parent::getSelectedValue().':';
    }

    public function setSelectedValue($value)
    {
        if (is_string($value)) {
            $value = explode(':', $value);
        }

        $this->selectedValue = $value;
        $this->selectedIndex = 0;
    }
}