<?php
function pagemaster_init() {

	Loader :: loadClass('CategoryUtil');
	Loader :: loadClassFromModule('Categories', 'Category');
	// create table
	if (!DBUtil :: createTable('pagemaster_pubfields')) {
		return false;
	}
	if (!DBUtil :: createTable('pagemaster_pubtypes')) {
		return false;
	}
	if (!DBUtil :: createTable('pagemaster_revisions')) {
		return false;
	}
	$rootcat = CategoryUtil :: getCategoryByPath('/__SYSTEM__/Modules');
	$lang = pnUserGetLang();

	$cat = new PNCategory();
	$cat->setDataField('parent_id', $rootcat['id']);
	$cat->setDataField('name', 'pagemaster');
	$cat->setDataField('display_name', array (
		$lang => 'pagemaster'
	));
	$cat->setDataField('display_desc', array (
		$lang => 'module category for pagemaster'
	));
	$cat->insert();
	$cat->update();

	$rootcat = CategoryUtil :: getCategoryByPath('/__SYSTEM__/Modules/pagemaster');
	$cat = new PNCategory();
	$cat->setDataField('parent_id', $rootcat['id']);
	$cat->setDataField('name', 'lists');
	$cat->setDataField('display_name', array (
		$lang => 'lists'
	));
	$cat->setDataField('display_desc', array (
		$lang => 'contains lists for pagemaster publications'
	));
	$cat->insert();
	$cat->update();
	return (true);
}

function pagemaster_upgrade($oldversion) {

	$from_version = $oldversion;

	switch ($from_version) {
		case '0.1' :
			return (true);
			break;
	}

	return (true);

}

//---------------------------------------------------------------------------
// pagemaster_delete
//---------------------------------------------------------------------------
function pagemaster_delete() {

	Loader :: loadClass('CategoryUtil');
	$pubtypes = DBUtil :: selectObjectArray("pagemaster_pubtypes");
	foreach ($pubtypes as $pubtype)
		DBUtil :: dropTable('pagemaster_pubdata' . $pubtype['tid']);

	if (!DBUtil :: dropTable('pagemaster_pubfields')) {
		return false;
	}
	if (!DBUtil :: dropTable('pagemaster_pubtypes')) {
		return false;
	}
	if (!DBUtil :: dropTable('pagemaster_revisions')) {
		return false;
	}

	CategoryUtil :: deleteCategoriesByPath('/__SYSTEM__/Modules/pagemaster','path');
	pnModDelVar('pagemaster', 'uploadpath');

	return (true);

} // pagemaster_delete //