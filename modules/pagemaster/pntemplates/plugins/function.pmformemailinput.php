<?php
require_once ('system/pnForm/plugins/function.pnformemailinput.php');

class pmformemailinput extends pnFormEMailInput {
	
	var $columnDef = 'C(100)';
	var $title = 'Email';
	
	
	function getFilename() {
		return __FILE__; // FIXME: may be found in smarty's data???
	}

}

function smarty_function_pmformemailinput($params, & $render) {
	return $render->pnFormRegisterPlugin('pmformemailinput', $params);
}