<?php
require_once ('system/pnForm/plugins/function.pnformcategoryradiobutton.php');

class pmformradioinput extends pnFormCategoryRadioButton {
	
	var $columnDef = 'I(4)';
	var $title = 'Radio';
	
    function load(&$render, $params)
    {
        if (isset($render->pnFormEventHandler->pubfields[$this->id])) {
            $params['category'] = $render->pnFormEventHandler->pubfields[$this->id]['typedata'];
        }
        parent::load(&$render, $params);
        if ($this->mandatory)
            array_shift($this->items); //pnFormCategorySelector makes a "- - -" entry for mandatory field, what makes no sense for checkboxes
    }
	
	function getFilename() {
		return __FILE__; // FIXME: may be found in smarty's data???
	}

}