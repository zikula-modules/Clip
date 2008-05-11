<?php
require_once ('system/pnForm/plugins/function.pnformfloatinput.php');

class pmformfloatinput extends pnFormFloatInput {
	
	var $columnDef = 'F';
	var $title = 'Float Value';
	
	function getFilename() {
		return __FILE__; // FIXME: may be found in smarty's data???
	}

}

function smarty_function_pmformfloatinput($params, & $render) {
	return $render->pnFormRegisterPlugin('pmformfloatinput', $params);
}