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
 * Admin Model.
 */
class PageMaster_Api_Admin extends Zikula_Api
{
    /**
     * Get admin panel links.
     *
     * @return array Array of admin links.
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
                'url'  => ModUtil::url('PageMaster', 'admin', 'relations'),
                'text' => $this->__('Manage relations')
            );
            $links[] = array (
                'url'  => ModUtil::url('PageMaster', 'admin', 'editlist'),
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
