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

require_once('system/pnForm/plugins/function.pnformdateinput.php');

class pmformdateinput extends pnFormDateInput
{
    var $columnDef = 'T';
    var $title;
    var $filterClass = 'date';

    var $config;

    function __construct()
    {
        $dom = ZLanguage::getModuleDomain('PageMaster');
        //! field type name
        $this->title = __('Date', $dom);

        parent::__construct();
    }

    function getFilename()
    {
        return __FILE__; // FIXME: may be found in smarty's data???
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
        $this->parseConfig($render->pnFormEventHandler->pubfields[$this->id]['typedata']);
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
