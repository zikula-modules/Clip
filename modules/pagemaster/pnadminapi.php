<?php
/**
 * PageMaster
 *
 * @copyright   (c) PageMaster Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package     Zikula_3rdParty_Modules
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
    $dom = ZLanguage::getModuleDomain('pagemaster');
    if (!isset($args['tid'])) {
        return LogUtil::registerError(__f('%s not set', 'tid', $dom));
    }

    $tablename = 'pagemaster_pubdata'.$args['tid'];

    $pntable = &pnDBGetTables();
    if (!isset ($pntable[$tablename])) {
        return LogUtil::registerError(__('No table definitions found. Please define fields for your publication.', $dom));
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
    $dom = ZLanguage::getModuleDomain('pagemaster');
    pnModLangLoad('pagemaster', 'admin');

    $links = array ();
    if (SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
        $links[] = array (
            'url'  => pnModURL('pagemaster', 'admin', 'main'),
            'text' => pnML(__('List Publication Types', $dom))
        );
        $links[] = array (
            'url'  => pnModURL('pagemaster', 'admin', 'create_tid'),
            'text' => pnML(__('Create New Publication Type', $dom))
        );
        $links[] = array (
            'url'  => pnModURL('pagemaster', 'admin', 'modifyconfig'),
            'text' => pnML(__('Settings', $dom))
        );
    }
    return $links;
}


