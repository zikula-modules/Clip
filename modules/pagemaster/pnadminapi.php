<?php
/**
 * PageMaster
 *
 * @copyright (c) 2008, PageMaster Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package     Zikula_3rd_party_Modules
 * @subpackage  pagemaster
 */

/**
 * Updates the database tables (DDL), based on pubfields.
 * 
 * @author kundi
 * @param $args['tid'] tid of publication
 * @return true or false
 */
function pagemaster_adminapi_updatetabledef($args)
{
    if (!isset($args['tid'])) {
        return LogUtil::registerError(pnML('_PAGEMASTER_VARNOTSET', array('var' => 'tid')));
    }

    $tablename = 'pagemaster_pubdata' . $args['tid'];

    $pntable = &pnDBGetTables();
    if (!isset ($pntable[$tablename])) {
        return LogUtil::registerError(_PAGEMASTER_TABLEDEFNOTFOUND);
    }

    DBUtil::createTable($tablename);
    return true;
}

/**
 * get admin panel links
 *
 * @author       gf
 * @return       array      array of admin links
 */
function pagemaster_adminapi_getlinks()
{
    pnModLangLoad('pagemaster', 'admin');

    $links = array ();
    if (SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
        $links[] = array (
            'url'  => pnModURL('pagemaster', 'admin', 'main'),
            'text' => pnML('_PAGEMASTER_PUBTYPES')
        );
        $links[] = array (
            'url'  => pnModURL('pagemaster', 'admin', 'create_tid'),
            'text' => pnML('_PAGEMASTER_CREATEPUBTYPE')
        );
        $links[] = array (
            'url'  => pnModURL('pagemaster', 'admin', 'modifyconfig'),
            'text' => pnML('_MODIFYCONFIG')
        );
    }
    return $links;
}

function pagemaster_adminapi_moveToDepot($args, $direction)
{
    if (!isset($args['tid'])) {
        return LogUtil::registerError(pnML('_PAGEMASTER_VARNOTSET', array('var' => 'tid')));
    }

    if (!isset($args['id'])) {
        return LogUtil::registerError(pnML('_PAGEMASTER_VARNOTSET', array('var' => 'id')));
    }

    $pubtype = DBUtil::selectObjectByID('pagemaster_pubtypes', $args['tid'], 'tid');

    if ($pubtype['enablerevisions']) {
        return pagesetterDepotTransportReal($args, $direction);
    } else {
        return pagesetterDepotTransportDelete($args);
    }
}
