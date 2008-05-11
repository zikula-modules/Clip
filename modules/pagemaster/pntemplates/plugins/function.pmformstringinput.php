<?php
require_once ('system/pnForm/plugins/function.pnformtextinput.php');

class pmformstringinput extends pnFormTextInput {
	
	var $columnDef = 'C(512)';
	var $title = 'String';
	
	
	function getFilename() {
		return __FILE__; // FIXME: may be found in smarty's data???
	}

}

function smarty_function_pmformstringinput($params, & $render) {
	return $render->pnFormRegisterPlugin('pmformstringinput', $params);
}