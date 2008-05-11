<?php
require_once ('system/pnForm/plugins/function.pnformcategoryselector.php');

class pmformlistinput extends pnFormCategorySelector {

	function getFilename() {
		return __FILE__; // FIXME: may be found in smarty's data???
	}
	var $columnDef = 'I (9,0)';
	var $title = 'List';
	var $filterClass = 'pmList';

	function postRead($data, $field) {
		$lang = SessionUtil::getVar('lang', null);
		Loader :: loadClass('CategoryUtil');
		$cat = CategoryUtil :: getCategoryByID($data);
		//compatible mode to pagesetter
		$cat['fullTitle'] = $cat['display_name'][$lang];
		$cat['value'] = $cat['name'];
		$cat['title'] = $cat['name'];
		return $cat;
	}
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

	function getSaveTypeDataFunc($field) {
		$saveTypeDataFunc = "function saveTypeData()
																					{
																						document.getElementById('typedata').value = document.getElementById('pagemaster_list').value ;
																						document.getElementById('typeDataDiv').style.display = 'none';
																					}";
		return $saveTypeDataFunc;
	}
	function getTypeHtml($field) {
		Loader :: loadClass('CategoryUtil');
		$rootCat = CategoryUtil :: getCategoryByPath('/__SYSTEM__/Modules/pagemaster/lists');
		$cats = CategoryUtil :: getCategoriesByParentID($rootCat['id']);
		$html .= '<select name="pagemaster_list" id="pagemaster_list">';
		foreach ($cats as $cat) {
			$html .= '<option value="' . $cat['id'] . '">' . $cat['name'] . '</option>';
		}
		$html .= '</select>';
		return $html;
	}

}

function smarty_function_pmformlistinput($params, & $render) {
	return $render->pnFormRegisterPlugin('pmformlistinput', $params);
}