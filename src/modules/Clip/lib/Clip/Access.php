<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Lib
 */

/**
 * Clip Security Util.
 */
class Clip_Access
{
    /**
     * Checks if the current user has access to Clip.
     *
     * @param integer $permlvl  Required permission level to have (default: ACCESS_OVERVIEW).
     * @param string  $instance Instance to check (default: '::').
     *
     * @return boolean True if allowed, false otherwise.
     */
    public static function toClip($permlvl = ACCESS_OVERVIEW, $instance = '::')
    {
        // fill default values if needed
        $permlvl = $permlvl ? $permlvl : ACCESS_OVERVIEW;
        $instance = $instance ? $instance : '::';

        // evaluate the access
        return SecurityUtil::checkPermission('Clip::', $instance, $permlvl);
    }

    /**
     * Checks if the current user has access to a grouptype.
     *
     * @param integer $gid     Grouptype ID.
     * @param integer $permlvl Required permission level to have (default: ACCESS_OVERVIEW).
     *
     * @return boolean True if allowed, false otherwise.
     */
    public static function toGrouptype($gid, $permlvl = ACCESS_OVERVIEW)
    {
        // fill default values if needed
        $permlvl = $permlvl ? $permlvl : ACCESS_OVERVIEW;

        // evaluate the access
        return SecurityUtil::checkPermission("Clip:{$gid}:", '::', $permlvl);
    }

    /**
     * Checks if the current user has access to a pubtype.
     *
     * @param integer $pubtype Publication type instance or ID.
     * @param string  $context Context to check the access for (default: admin).
     * @param string  $tplid   Template ID required for permission check on main/list (default: '').
     * @param integer $uid     User ID of the user to check permission for (default: current user).
     *
     * @return boolean True if allowed, false otherwise.
     */
    public static function toPubtype($pubtype, $context = 'admin', $tplid = '', $uid = null)
    {
        // fill default values if needed
        $context = $context ? strtolower($context) : 'admin';

        // be sure to have a Clip_Model_Pubtype instance
        if (!$pubtype instanceof Clip_Model_Pubtype) {
            if (!Clip_Util::validateTid($pubtype)) {
                return LogUtil::registerError($this->__f('Error! Invalid publication type ID passed [%s].', DataUtil::formatForDisplay($pubtype)));
            }

            $pubtype = Clip_Util::getPubType($pubtype);
        }

        // evaluate the access depending of the required context
        switch ($context)
        {
            case 'admin':
                $allowed = SecurityUtil::checkPermission("Clip:{$pubtype->grouptype}:", "{$pubtype->tid}::", ACCESS_ADMIN, $uid);
                break;

            case 'editor': // panel
                // TODO consider edit.own
                // FIXME use workflow initial maxlevel action
                $allowed = SecurityUtil::checkPermission("Clip:{$pubtype->grouptype}:edit", "{$pubtype->tid}::", ACCESS_EDIT, $uid);
                break;

            case 'submit': // submit new content
                // FIXME workflow initial minlevel action permcheck
                $allowed = SecurityUtil::checkPermission("Clip:{$pubtype->grouptype}:edit", "{$pubtype->tid}::", ACCESS_COMMENT, $uid);
                break;

            case 'list':
            case 'main':
                $allowed = SecurityUtil::checkPermission("Clip:{$pubtype->grouptype}:$context", "{$pubtype->tid}::$tplid", ACCESS_OVERVIEW, $uid);
                break;

            default:
                $allowed = false;
        }

        return $allowed;
    }

    /**
     * Checks if the current user has access to a publication.
     *
     * @param integer $pubtype Publication type instance or ID.
     * @param integer $pub     Publication instance or ID.
     * @param integer $permlvl Required permission level to have, used on edit context only (default: ACCESS_EDIT).
     * @param integer $uid     User ID of the user to check permission for (default: current user).
     * @param string  $context Context to check the access for (default: edit).
     * @param string  $tplid   Template ID required for permission check on display (default: '').
     *
     * @return boolean True if allowed, false otherwise.
     */
    public static function toPub($pubtype, $pub, $permlvl = ACCESS_EDIT, $uid = null, $context = 'edit', $tplid = '')
    {
        // fill default values if needed
        $permlvl = $permlvl ? $permlvl : ACCESS_EDIT;
        $context = $context ? strtolower($context) : 'edit';

        // be sure to have a Clip_Model_Pubtype instance
        if (!$pubtype instanceof Clip_Model_Pubtype) {
            if (!Clip_Util::validateTid($pubtype)) {
                return LogUtil::registerError($this->__f('Error! Invalid publication type ID passed [%s].', DataUtil::formatForDisplay($pubtype)));
            }

            $pubtype = Clip_Util::getPubType($pubtype);
        }

        // needs at least overview permission for the pubtype
        if (!self::toPubtype($pubtype, 'main', '', $uid)) {
            return false;
        }

        // when it's an instance we can do a complete check
        if ($pub instanceof Clip_Base_Pubdata) {
            // check an already stored record
            if ($pub->exists()) {
                if (!isset($pub['core_pid'])) {
                    $pub->clipValues();
                }
                $pid = $pub['core_pid'];

                $state = $pub->clipWorkflow('state');
            } else {
                // the user may wants to save a new record
                $pid = $state = '';
            }
        } else {
            // plain PID means initial access check
            $pid = $pub;
        }

        // evaluate the access depending of the required context
        switch ($context)
        {
            case 'edit':
                $allowed = SecurityUtil::checkPermission("Clip:{$pubtype->grouptype}:$context", "{$pubtype->tid}:$pid:$state", $permlvl, $uid);
                // TODO consider edit.own, state permissions
                break;

            case 'display':
                // TODO check core_online + normal user = false (relations check, etc)
                $allowed = SecurityUtil::checkPermission("Clip:{$pubtype->grouptype}:$context", "{$pubtype->tid}:$pid:$tplid", $permlvl, $uid);
                break;

            default:
                $allowed = false;
        }

        return $allowed;
    }
}
