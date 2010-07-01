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

class PageMaster_Api_Admin extends Zikula_Api
{
    /**
     * Updates the database tables (DDL), based on pubfields.
     *
     * @author kundi
     * @param  $args['tid']  tid of publication
     * @return bool          true on success, false otherwise
     */
    public function updatetabledef($args)
    {
        if (!isset($args['tid'])) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'tid'));
        }

        $tablename = 'pagemaster_pubdata'.$args['tid'];

        $tables = DBUtil::getTables();

        if (!isset($tables[$tablename])) {
            $urlfields = ModUtil::url('PageMaster', 'admin', 'pubfields', array('tid' => $args['tid']));
            return LogUtil::registerError($this->__f('Error! No table definitions found. Please <a href="%s">define the fields</a> of your publication.', $urlfields));
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
    public function getlinks()
    {
        $links = array ();

        if (SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
            $links[] = array (
                'url'  => ModUtil::url('PageMaster', 'admin', 'pubtypes'),
                'text' => $this->__('List publication types')
            );
            $links[] = array (
                'url'  => ModUtil::url('PageMaster', 'admin', 'pubtype'),
                'text' => $this->__('New publication type')
            );
            $links[] = array (
                'url'  => ModUtil::url('PageMaster', 'admin', 'pubeditlist'),
                'text' => $this->__('Edit publications')
            );
            $links[] = array (
                'url'  => ModUtil::url('PageMaster', 'admin', 'modifyconfig'),
                'text' => $this->__('Settings')
            );
        }

        return $links;
    }
}
