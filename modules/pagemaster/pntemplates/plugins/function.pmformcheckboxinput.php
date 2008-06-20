<?php
require_once ('system/pnForm/plugins/function.pnformcategorycheckboxlist.php');

class pmformcheckboxinput extends pnFormCategoryCheckboxList {

	var $columnDef = 'I(4)';
	var $title = 'Checkbox';
	function load(& $render, $params)
	{
		$pubfields = $render->pnFormEventHandler->pubfields;
		foreach ($pubfields as $key => $pubfield) {

			if ($pubfield['name'] == $this->id) {
				$params['category'] = $pubfield['typedata'];
			}
		}
		parent :: load(& $render, $params);
	}

	function getFilename() {
		return __FILE__; // FIXME: may be found in smarty's data???
	}

}

function smarty_function_pmformcheckboxinput($params, & $render) {
	return $render->pnFormRegisterPlugin('pmformcheckboxinput', $params);
}