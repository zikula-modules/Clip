<?php
require_once ('system/pnForm/plugins/function.pnformurlinput.php');

class pmformurlinput extends pnFormURLInput {
	
	var $columnDef = 'C(500)';
	var $title = 'Url';
	
	
	function getFilename() {
		return __FILE__; // FIXME: may be found in smarty's data???
	}

}

function smarty_function_pmformurlinput($params, & $render) {
	return $render->pnFormRegisterPlugin('pmformurlinput', $params);
}