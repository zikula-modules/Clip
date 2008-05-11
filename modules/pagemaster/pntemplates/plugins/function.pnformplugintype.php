<?php
require_once ('system/pnForm/plugins/function.pnformdropdownlist.php');

class pnFormPluginType extends pnFormDropdownList {

	function getFilename() {
		return __FILE__; // FIXME: may be found in smarty's data???
	}
	function __construct() {

		$this->autoPostBack = true;
		$plugins = pagemasterGetPluginsOptionList();
		
		foreach ($plugins as $plugin) {
			$items[] = array (
				'text' => $plugin['plugin']->title,
				'value' => $plugin['file']
			);
		}
		$this->items = $items;

		parent :: __construct();

	}
	function render($pnRender) {
		$result = parent :: render($pnRender);
		$typeDataHtml = '';
		if ($this->selectedValue <> '') {
			$plugin = pagemasterGetPlugin($this->selectedValue);
			if (method_exists($plugin,'getTypeHtml'))
			{	
				if (method_exists($plugin,'getSaveTypeDataFunc')){
					PageUtil::setVar('rawtext', "<script type=\"text/javascript\">\n".$plugin->getSaveTypeDataFunc($this)."</script>");
				}else
					PageUtil::setVar('rawtext', "<script type=\"text/javascript\">function saveTypeData(){ document.getElementById('typeDataDiv').style.display = 'none'; }</script>");
				$typeDataHtml = '<button type="button" id="showTypeButton" name="showTypeButton" onClick="showTypeDiv()">Extra</button>';
				$typeDataHtml .= '<div name="typeDataDiv" style="display:none;" id="typeDataDiv">';
				$typeDataHtml .= $plugin->getTypeHtml($this, $pnRender);
				$typeDataHtml .= '<button type="button" id="saveTypeButton" name="saveTypeButton" onClick="saveTypeData()">' . _SAVE . '</button><br/>'; 
				$typeDataHtml .= '</div>';
			}
		}
		return $result . $typeDataHtml;
	}

}

function smarty_function_pnformplugintype($params, & $render) {
	return $render->pnFormRegisterPlugin('pnFormPluginType', $params);
}