<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Api
 */

/**
 * Admin Model.
 */
class Clip_Api_Admin extends Zikula_AbstractApi
{
    /**
     * Get admin panel links.
     *
     * @return array Array of admin links.
     */
    public function getlinks()
    {
        $links = array ();

        if (Clip_Access::toClip(ACCESS_ADMIN, 'ANY') /*Clip_Access::toPubtype(null, 'anyadmin')*/) {
            $links[] = array (
                'url'  => ModUtil::url('Clip', 'admin', 'main'),
                'text' => $this->__('Index')
            );
        }

        if (Clip_Access::toClip(ACCESS_ADMIN)) {
            $links[] = array (
                'url'  => ModUtil::url('Clip', 'admin', 'relations'),
                'text' => $this->__('Relations')
            );
            $links[] = array (
                'url'  => ModUtil::url('Clip', 'admin', 'clipexport'),
                'text' => $this->__('Export')
            );
            $links[] = array (
                'url'  => ModUtil::url('Clip', 'admin', 'clipimport'),
                'text' => $this->__('Import')
            );
            $links[] = array (
                'url'  => ModUtil::url('Clip', 'admin', 'modifyconfig'),
                'text' => $this->__('Settings')
            );
        }

        return $links;
    }
}
