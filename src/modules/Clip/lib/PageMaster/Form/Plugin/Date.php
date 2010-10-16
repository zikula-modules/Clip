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

class Clip_Form_Plugin_Date extends Form_Plugin_DateInput
{
    public $pluginTitle;
    public $columnDef = 'T';
    public $filterClass = 'date';

    public $config;

    function setup()
    {
        $dom = ZLanguage::getModuleDomain('Clip');
        $this->setDomain($dom);

        //! field type name
        $this->pluginTitle = $this->__('Date');
    }

    function getFilename()
    {
        return __FILE__;
    }

    static function getPluginOutput($field)
    {
        $body = '{$pubdata.'.$field['name'].'|dateformat:\'datetimelong\'}';

        return array('body' => $body);
    }

    static function getSaveTypeDataFunc($field)
    {
        $saveTypeDataFunc = 'function saveTypeData()
                             {
                                 if ($F(\'clipplugin_usedatetime\') == \'on\') {
                                     $(\'typedata\').value = 1;
                                 } else {
                                     $(\'typedata\').value = 0;
                                 }
                                 closeTypeData();
                             }';

        return $saveTypeDataFunc;
    }

    function getTypeHtml($field, $view)
    {
        $checked = '';
        if (isset($view->_tpl_vars['typedata'])) {
            $this->parseConfig($view->_tpl_vars['typedata']);
            $checked = $this->config['includeTime'] ? 'checked="checked"' : '';
        }

        $html = '<div class="z-formrow">
                     <label for="clipplugin_usedatetime">'.$this->__('Include time').':</label>
                     <input type="checkbox" id="clipplugin_usedatetime" name="clipplugin_usedatetime" '.$checked.' />
                 </div>';

        return $html;
    }

    function create($view, &$params)
    {
        $this->parseConfig($view->eventHandler->getPubfieldData($this->id, 'typedata'));
        $params['includeTime'] = $this->config['includeTime'];

        parent::create($view, $params);
    }

    /**
     * Parse configuration
     */
    function parseConfig($typedata='', $args=array())
    {
        $this->config = array();

        $this->config['includeTime'] = (bool)$typedata;
    }
}
