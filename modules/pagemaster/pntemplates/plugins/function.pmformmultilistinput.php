<?php
require_once ('system/pnForm/plugins/function.pnformcategorycheckboxlist.php');

class pmformmultilistinput extends pnFormCategoryCheckboxList {

	function getFilename() {
		return __FILE__; // FIXME: may be found in smarty's data???
	}
	var $columnDef = 'C(512)';
	var $title = 'MultiList';
	var $filterClass = 'pmList';

	function postRead($data, $field) {
		Loader :: loadClass('CategoryUtil');
		$data = substr($data, 1);
		$data = substr($data, 0, -1);
		$catIds = explode(':', $data);
		$lang = SessionUtil::getVar('lang', null);
		
		foreach ($catIds as $catId) {
			
			$cat = CategoryUtil :: getCategoryByID($catId);
			$cat['fullTitle'] = $cat['display_name'][$lang];
			$cat_arr[] = $cat;
		}
		return $cat_arr;
	}
	function load(& $render, $params) {
		$pubfields = $render->pnFormEventHandler->pubfields;
		foreach ($pubfields as $key => $pubfield) {

			if ($pubfield['name'] == $this->id) {
				$catid = $pubfield['typedata'];
			}
		}
		$params['category'] = $catid;
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
		Loader :: loadClass('CategoryRegistryUtil');

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

function smarty_function_pmformmultilistinput($params, & $render) {
	return $render->pnFormRegisterPlugin('pmformmultilistinput', $params);
}