<?php
require_once ('system/pnForm/plugins/function.pnformintinput.php');

class pmformintinput extends pnFormIntInput {
	
	var $columnDef = 'I (9,0)';
	var $title = 'Integer Value';
	
	function getFilename() {
		return __FILE__; // FIXME: may be found in smarty's data???
	}

}

function smarty_function_pmformintinput($params, & $render) {
	return $render->pnFormRegisterPlugin('pmformintinput', $params);
}