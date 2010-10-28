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

    function getPluginOutput($field)
    {
        $this->parseConfig($field['typedata']);
        $format = $this->config['includeTime'] ? 'datetimelong' : 'datelong';

        $body = '{$pubdata.'.$field['name']."|dateformat:'$format'}";

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
