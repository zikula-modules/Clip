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
    public static function toClip($permlvl = null, $instance = null)
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
    public static function toGrouptype($gid, $permlvl = null)
    {
        $dom = ZLanguage::getModuleDomain('Clip');

        if (!$gid) {
            return LogUtil::registerError(__f('%1$s: Invalid grouptype ID passed [%2$s].', array('Clip_Access::toGrouptype', DataUtil::formatForDisplay($gid)), $dom));
        }

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
    public static function toPubtype($pubtype, $context = null, $tplid = null, $uid = null)
    {
        $dom = ZLanguage::getModuleDomain('Clip');

        // fill default values if needed
        $context = $context ? strtolower($context) : 'admin';
        $tplid   = $tplid ? $tplid : '';

        // be sure to have a Clip_Model_Pubtype instance
        if (!$pubtype instanceof Clip_Model_Pubtype) {
            if (!Clip_Util::validateTid($pubtype)) {
                return LogUtil::registerError(__f('%1$s: Invalid publication type ID passed [%2$s].', array('Clip_Access::toPubtype', DataUtil::formatForDisplay($pubtype)), $dom));
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
                $workflow = new Clip_Workflow($pubtype);
                // assumes level 1 as the first moderator permission
                $permlvl = $workflow->getPermissionLevel(1, 'initial');
                $allowed = SecurityUtil::checkPermission("Clip:{$pubtype->grouptype}:edit", "{$pubtype->tid}::", $permlvl, $uid);
                break;

            case 'submit': // submit new content
                $workflow = new Clip_Workflow($pubtype);
                // assumes level 0 as the basic submit permission
                $permlvl = $workflow->getPermissionLevel(0, 'initial');
                $allowed = SecurityUtil::checkPermission("Clip:{$pubtype->grouptype}:edit", "{$pubtype->tid}::", $permlvl, $uid);
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
     * @param integer $id      Publication revision ID.
     * @param integer $permlvl Required permission level to have, used on edit context only (default: null).
     * @param integer $uid     User ID of the user to check permission for (default: current user).
     * @param string  $context Context to check the access for (default: edit).
     * @param string  $tplid   Template ID required for permission check on display (default: '').
     *
     * @return boolean True if allowed, false otherwise.
     */
    public static function toPub($pubtype, $pub, $id = null, $permlvl = null, $uid = null, $context = null, $tplid = null)
    {
        $dom = ZLanguage::getModuleDomain('Clip');

        // fill default values if needed
        $context = $context ? strtolower($context) : 'edit';
        $tplid   = $tplid ? $tplid : '';
        $state   = '';

        // be sure to have a Clip_Model_Pubtype instance
        if (!$pubtype instanceof Clip_Model_Pubtype) {
            if (!Clip_Util::validateTid($pubtype)) {
                return LogUtil::registerError(__f('%1$s: Invalid publication type ID passed [%2$s].', array('Clip_Access::toPub', DataUtil::formatForDisplay($pubtype)), $dom));
            }

            $pubtype = Clip_Util::getPubType($pubtype);
        }

        // needs at least overview permission for the pubtype
        if (!self::toPubtype($pubtype, 'main', '', $uid)) {
            return false;
        }

        // when it's an instance we can do a complete check
        if ($pub instanceof Clip_Doctrine_Pubdata) {
            // check an already stored record
            if ($pub->exists()) {
                if (!isset($pub['core_pid'])) {
                    $pub->clipValues();
                }
                $pid = $pub['core_pid'];

                $state = $pub->clipWorkflow('state');
            } else {
                // the user may wants to save a new record
                $pid = '';
            }
        } else {
            // plain PID
            $pid = $pub;
            // check if we need additional information
            if (strpos($context, 'edit') === 0) {
                // gets the online/latest revision state, if $id not specified
                if (!$id) {
                    $id = (int)ModUtil::apiFunc('Clip', 'user', 'getId', array('tid' => $pubtype->tid, 'pid' => $pid, 'lastrev'  => true));
                }

                // query for the state
                $dbtables = DBUtil::getTables();
                $wfcolumn = $dbtables['workflows_column'];
                $where = "WHERE $wfcolumn[module] = 'Clip' AND $wfcolumn[obj_table] = '{$pubtype->getTableName()}'
                            AND $wfcolumn[obj_idcolumn] = 'id' AND $wfcolumn[obj_id] = '" . DataUtil::formatForStore($id) . "'";

                $state = DBUtil::selectField('workflows', 'state', $where);
            }
            $state = $state ? $state : '';
        }

        // evaluate the access depending of the required context
        switch ($context)
        {
            case 'edit':
            case 'editinline':
                // TODO consider edit.own
                if (!$permlvl) {
                    // gets the minimum state permission level
                    $workflow = new Clip_Workflow($pubtype);
                    $mode    = $context == 'editinline' ? Clip_Workflow::ACTIONS_ALL : Clip_Workflow::ACTIONS_FORM;
                    $permlvl = $workflow->getPermissionLevel(0, $state, $mode);
                }
                $allowed = SecurityUtil::checkPermission("Clip:{$pubtype->grouptype}:edit", "{$pubtype->tid}:$pid:$state", $permlvl, $uid);
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
