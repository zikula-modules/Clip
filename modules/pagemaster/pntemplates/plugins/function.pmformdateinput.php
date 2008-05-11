<?
require_once ('system/pnForm/plugins/function.pnformdateinput.php');

class pmformdateinput extends pnFormDateInput {
	
	var $columnDef = 'D';
	var $title = 'Date';
	
	function getFilename() {
		return __FILE__; // FIXME: may be found in smarty's data???
	}

}

function smarty_function_pmformdateinput($params, & $render) {
	return $render->pnFormRegisterPlugin('pmformdateinput', $params);
}