<?php
include_once ('modules/pagemaster/common.php');

function pagemaster_import_importps() {
	if (!SecurityUtil :: checkPermission('pagemaster::', '::', ACCESS_ADMIN))
		return LogUtil :: registerError(_NOT_AUTHORIZED);
	$step = FormUtil :: getPassedValue('step');
	if ($step <> '')
		$ret = pnModAPIFunc('pagemaster', 'import', 'importps'.$step, array ());
	$pnRender = pnRender :: getInstance('pagemaster', null, null, true);

	//check if exitsts
	$pubtypes = DBUtil :: selectObjectArray("pagemaster_pubtypes");
	if (count($pubtypes)>0)
		$pnRender->assign('allreadyexists', 1);
	else
		$pnRender->assign('allreadyexists', 0);
		
	
	
	return $pnRender->fetch('pagemaster_admin_importps.htm');
}
