<?php


/**
 * Updates the database tables (DDL), based on pubfields.
 * @author kundi
 * @param $args['tid'] tid of publication
 * @return true or false
 */
function pagemaster_adminapi_updatetabledef($args) {
	if (!isset ($args['tid'])) {
		LogUtil :: registerError('tid no set');
		return false;
	}
	$tablename = 'pagemaster_pubdata' . $args['tid'];
	$pntable = & pnDBGetTables();
	if (!isset ($pntable[$tablename]))
		return LogUtil :: registerError(_PAGEMASTER_TABLEDEFNOTFOUND);

	DBUtil :: createTable($tablename);

	return true;
}

/**
 * get admin panel links
 *
 * @author       gf
 * @return       array      array of admin links
 */

function pagemaster_adminapi_getlinks() {
	$links = array ();

	pnModLangLoad('pagemaster', 'admin');

	if (SecurityUtil :: checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
		$links[] = array (
			'url' => pnModURL('pagemaster', 'admin', 'main'),
			'text' => pnML('_PAGEMASTER_PUBTYPES')
		);
	}

	if (SecurityUtil :: checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
		$links[] = array (
			'url' => pnModURL('pagemaster', 'admin', 'modifyconfig'),
			'text' => pnML('_MODIFYCONFIG')
		);
	}
	if (SecurityUtil :: checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
		$links[] = array (
			'url' => pnModURL('pagemaster', 'admin', 'create_tid'),
			'text' => pnML('_PAGEMASTER_CREATEPUBTYPE')
		);
	}
	if (SecurityUtil :: checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
		$links[] = array (
			'url' => pnModURL('pagemaster', 'import', 'importps'),
			'text' => pnML('_PAGEMASTER_IMPORTFROMPAGESETTER')
		);
	}
	return $links;
}

function pagemaster_adminapi_moveToDepot($args, $direction) {
	if (!isset ($args['tid'])) {
		LogUtil :: registerError('tid no set');
		return false;
	}
	if (!isset ($args['id'])) {
		LogUtil :: registerError('id no set');
		return false;
	}
	$tid = $args['tid'];
	$id = $args['id'];

	$pubtype = DBUtil :: selectObjectByID("pagemaster_pubtypes", $tid, 'tid');
	
	if ($pubtype['enablerevisions'])
		return pagesetterDepotTransportReal($args, $direction);
	else
		return pagesetterDepotTransportDelete($args);
}