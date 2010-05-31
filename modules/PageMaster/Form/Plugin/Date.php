<?php
/**
 * PageMaster
 *
 * @copyright   (c) PageMaster Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @version     $ Id $
 * @package     Zikula_3rdParty_Modules
 * @subpackage  pagemaster
 */

class PageMaster_Form_Plugin_Date extends Form_Plugin_DateInput
{
    public $columnDef = 'T';
    public $title;
    public $filterClass = 'date';

    public $config;

    function __construct()
    {
        $dom = ZLanguage::getModuleDomain('PageMaster');
        //! field type name
        $this->title = __('Date', $dom);

        parent::__construct();
    }

    function getFilename()
    {
        return __FILE__;
    }

    static function getSaveTypeDataFunc($field)
    {
        $saveTypeDataFunc = 'function saveTypeData()
                             {
                                 if ($F(\'pmplugin_usedatetime\') == \'on\') {
                                     $(\'typedata\').value = 1;
                                 } else {
                                     $(\'typedata\').value = 0;
                                 }
                                 closeTypeData();
                             }';

        return $saveTypeDataFunc;
    }

    function getTypeHtml($field, $render)
    {
        $dom = ZLanguage::getModuleDomain('PageMaster');

        $this->parseConfig($render->_tpl_vars['typedata']);

        $checked = $this->config['includeTime'] ? 'checked="checked"' : '';

        $html .= '<div class="z-formrow z-warningmsg">
                      <label for="pmplugin_usedatetime">'.__('Include time', $dom).':</label>
                      <input type="checkbox" id="pmplugin_usedatetime" name="pmplugin_usedatetime" '.$checked.' />
                  </div>';

        return $html;
    }

    function create(&$render, &$params)
    {
        $this->parseConfig($render->EventHandler->pubfields[$this->id]['typedata']);
        $params['includeTime'] = $this->config['includeTime'];

        parent::create($render, $params);
    }

    /**
     * Parse configuration
     */
    function parseConfig($typedata = '', $args = array())
    {
        $this->config = array();

        $this->config['includeTime'] = (bool)$typedata;
    }
}
