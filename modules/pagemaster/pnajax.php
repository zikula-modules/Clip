<?php
function pagemaster_ajax_changedlistorder()
{
    if (!SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
        AjaxUtil::error(DataUtil::formatForDisplayHTML(_MODULENOAUTH));
    }

//    if (!SecurityUtil::confirmAuthKey()) {
//        AjaxUtil::error(_BADAUTHKEY);
//    }

    $pubfieldlist = FormUtil::getPassedValue('pubfieldlist');
    $tid = FormUtil::getPassedValue('tid');
    
	foreach($pubfieldlist as $key => $value)
	{
		$data[lineno] = $key;
	    $res = DBUtil :: updateObject($data, 'pagemaster_pubfields', "pm_id = " . $value . " AND pm_tid = " . $tid);
    	if (!$res) {
            AjaxUtil::error(_UPDATEFAILED);
    	}
		
	}
	

    
        
    return array('result' => true);
}

