<?php
/**
 * PageMaster
 *
 * @copyright (c) 2008, PageMaster Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @version     $ Id $
 * @package     Zikula_3rd_party_Modules
 * @subpackage  pagemaster
 */

require_once('system/pnForm/plugins/function.pnformdropdownlist.php');

class pnFormPluginType extends pnFormDropdownList
{
    function getFilename()
    {
        return __FILE__; // FIXME: may be found in smarty's data???
    }

    function __construct()
    {
        $this->autoPostBack = true;
        $plugins = pagemasterGetPluginsOptionList();

        foreach ($plugins as $plugin) {
            $items[] = array (
                'text'  => $plugin['plugin']->title,
                'value' => $plugin['file']
            );
        }
        $this->items = $items;

        parent::__construct();
    }

    function render($render)
    {
        $result = parent::render($render);
        $typeDataHtml = '';
        if ($this->selectedValue <> '' || ($this->selectedValue == '' && !empty($this->items))) {
            if ($this->selectedValue == '') {
                $this->selectedValue = $this->items[0]['value'];
            }

            $script =  "<script type=\"text/javascript\">\n//<![CDATA[\n";

            $plugin = pagemasterGetPlugin($this->selectedValue);
            if (method_exists($plugin, 'getTypeHtml'))
            {    
                if (method_exists($plugin, 'getSaveTypeDataFunc')) {
                    $script .= $plugin->getSaveTypeDataFunc($this);
                } else {
                    $script .= 'function saveTypeData(){ closeTypeData(); }';
                }
                // unobtrusive buttons functions 
                $script .= "\nfunction pm_enablebuttons(){
                    $('showTypeButton').observe('click', showTypeDiv);
                    $('saveTypeButton').observe('click', saveTypeData);
                    $('cancelTypeButton').observe('click', closeTypeData);
                }";
                $script .= "\nEvent.observe( window, 'load', pm_enablebuttons, false);";

                $typeDataHtml  = '<button type="button" id="showTypeButton" name="showTypeButton"><img src="images/icons/extrasmall/utilities.gif" alt="' . _MODIFYCONFIG .'" /></button>';

                $typeDataHtml .= '<div id="typeDataDiv" name="typeDataDiv" style="display:none;">';
                $typeDataHtml .= '<div id="typeDataContent">'.$plugin->getTypeHtml($this, $render).'</div>';
                $typeDataHtml .= '<div class="pn-formrow">
                                      <button type="button" id="saveTypeButton" name="saveTypeButton"><img src="images/icons/extrasmall/filesave.gif" alt="' . _SAVE . '" /></button>&nbsp;
                                      <button type="button" id="cancelTypeButton" name="cancelTypeButton"><img src="images/icons/extrasmall/button_cancel.gif" alt="' . _CANCEL . '" /></button>';
                $typeDataHtml .= '</div></div>';

                $typeDataHtml .= '<div id="Modalcontainer" style="display:none">&nbsp;</div>';
            } else {
                $script .= 'Event.observe( window, \'load\', function() { $(\'typedata\').hide(); }, false);';
            }
            $script .= "\n// ]]>\n</script>"; 
            PageUtil::setVar('rawtext', $script);
        }
        return $result . $typeDataHtml;
    }
}

function smarty_function_pnformplugintype($params, &$render) {
    return $render->pnFormRegisterPlugin('pnFormPluginType', $params);
}
