<?php
/**
 * PageMaster
 *
 * @copyright (c) 2008, PageMaster Team
 * @link        http://code.zikula.org/projects/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
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
        if ($this->selectedValue <> '') {
            $plugin = pagemasterGetPlugin($this->selectedValue);
            if (method_exists($plugin,'getTypeHtml'))
            {    
                if (method_exists($plugin,'getSaveTypeDataFunc')) {
                    PageUtil::setVar('rawtext', '<script type="text/javascript">'.$plugin->getSaveTypeDataFunc($this).'</script>');
                } else {
                    PageUtil::setVar('rawtext', '<script type="text/javascript">function saveTypeData(){ document.getElementById(\'typeDataDiv\').style.display = \'none\'; }</script>');
                }
                $typeDataHtml  = '<button type="button" id="showTypeButton" name="showTypeButton" onClick="showTypeDiv()">Extra</button>';
                $typeDataHtml .= '<div name="typeDataDiv" style="display:none;" id="typeDataDiv">';
                $typeDataHtml .= $plugin->getTypeHtml($this, $render);
                $typeDataHtml .= '<button type="button" id="saveTypeButton" name="saveTypeButton" onClick="saveTypeData()">' . _SAVE . '</button><br/>'; 
                $typeDataHtml .= '</div>';
            }
        }
        return $result . $typeDataHtml;
    }
}

function smarty_function_pnformplugintype($params, &$render) {
    return $render->pnFormRegisterPlugin('pnFormPluginType', $params);
}
