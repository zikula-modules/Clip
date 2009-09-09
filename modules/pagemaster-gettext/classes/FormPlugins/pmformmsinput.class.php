<?php
require_once ('system/pnForm/plugins/function.pnformtextinput.php');

class pmformmsinput extends pnFormTextInput {

	function getFilename() {
		return __FILE__; // FIXME: may be found in smarty's data???
	}
	var $columnDef = 'C(255)';
	var $title = 'Mediashare';

	static function postRead($data, $field) {
		$lang = pnUserGetLang();
		Loader :: loadClass('CategoryUtil');
		$cat = CategoryUtil :: getCategoryByID($data);
		//compatible mode to pagesetter
		$cat['fullTitle'] = $cat['display_name'][$lang];
		$cat['value'] = $cat['name'];
		$cat['title'] = $cat['name'];
		return $cat;
	}


}