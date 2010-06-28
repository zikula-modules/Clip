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
 * @param  $args['tid']  tid of publication
 * @return bool          true on success, false otherwise
 */
function PageMaster_adminapi_updatetabledef($args)
{
    $dom = ZLanguage::getModuleDomain('PageMaster');

    if (!isset($args['tid'])) {
        return LogUtil::registerError(__f('Error! Missing argument [%s].', 'tid', $dom));
    }

    $tablename = 'pagemaster_pubdata'.$args['tid'];

    $tables = pnDBGetTables();

    if (!isset($tables[$tablename])) {
        $urlfields = pnModURL('PageMaster', 'admin', 'pubfields', array('tid' => $args['tid']));
        return LogUtil::registerError(__f('Error! No table definitions found. Please <a href="%s">define the fields</a> of your publication.', $urlfields, $dom));
    }

    $existing = DBUtil::metaTables();
    if (!in_array(DBUtil::getLimitedTablename($tablename), $existing)) {
        DBUtil::createTable($tablename);
    } else {
        DBUtil::changeTable($tablename);
    }

    return true;
}

/**
 * get admin panel links
 *
 * @author       gf
 * @return       array      array of admin links
 */
function PageMaster_adminapi_getlinks()
{
    $dom = ZLanguage::getModuleDomain('PageMaster');

    $links = array ();

    if (SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
        $links[] = array (
            'url'  => pnModURL('PageMaster', 'admin', 'pubtypes'),
            'text' => __('List publication types', $dom)
        );
        $links[] = array (
            'url'  => pnModURL('PageMaster', 'admin', 'pubtype'),
            'text' => __('New publication type', $dom)
        );
        $links[] = array (
            'url'  => pnModURL('PageMaster', 'admin', 'pubeditlist'),
            'text' => __('Edit publications', $dom)
        );
        $links[] = array (
            'url'  => pnModURL('PageMaster', 'admin', 'modifyconfig'),
            'text' => __('Settings', $dom)
        );
    }

    return $links;
}
