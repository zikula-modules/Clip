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
     *
     * @return boolean True if allowed, false otherwise.
     */
    public static function toGrouptype($gid)
    {
        $dom = ZLanguage::getModuleDomain('Clip');

        if (!$gid) {
            return LogUtil::registerError(__f('%1$s: Invalid grouptype ID passed [%2$s].', array('Clip_Access::toGrouptype', DataUtil::formatForDisplay($gid)), $dom));
        }

        // evaluate the access
        return SecurityUtil::checkPermission("Clip:g{$gid}:", '::', ACCESS_OVERVIEW);
    }

    /**
     * Checks if the current user has access to a pubtype.
     *
     * @param integer $pubtype Publication type instance or ID.
     * @param string  $context Context to check the access for (default: admin).
     * @param string  $tplid   Template ID required for permission check on main/list (default: '').
     * @param integer $uid     User ID of the user to check permission for (default: current user).
     *
     * @throws Exception If an invalid context was passed.
     *
     * @return boolean True if allowed, false otherwise.
     */
    public static function toPubtype($pubtype = null, $context = null, $tplid = null, $uid = null)
    {
        $dom = ZLanguage::getModuleDomain('Clip');

        // fill default values if needed
        $context = $context ? strtolower($context) : 'admin';
        $tplid   = $tplid ? $tplid : '';
        $uid     = $uid ? $uid : (int)UserUtil::getVar('uid');

        $component = "Clip::";
        $instance  = "::";
        $permlvl   = ACCESS_ADMIN;

        // be sure to have a Clip_Model_Pubtype instance
        if ($pubtype) {
            if (!$pubtype instanceof Clip_Model_Pubtype) {
                if (!Clip_Util::validateTid($pubtype)) {
                    return LogUtil::registerError(__f('%1$s: Invalid publication type ID passed [%2$s].', array('Clip_Access::toPubtype', DataUtil::formatForDisplay($pubtype)), $dom));
                }

                $pubtype = Clip_Util::getPubType($pubtype);
            }

            $component = "Clip:{$pubtype->tid}:";

        } else if (in_array($context, array('editor', 'submit'))) {
            return false;
        }

        // evaluate the access depending of the required context
        switch ($context)
        {
            case 'access':
                $permlvl = ACCESS_OVERVIEW;
                break;

            case 'main':
            case 'list':
            case 'edit':
                $component .= $context;
                $instance  .= $tplid;
                $permlvl    = ACCESS_OVERVIEW;
                break;

            case 'editor': // panel
                $component .= 'edit';
                // TODO consider edit.own and not for pubs submitted by guests
                $workflow = new Clip_Workflow($pubtype);
                // assumes level 1 as the first moderator permission
                $permlvl = $workflow->getPermissionLevel(1, 'initial');
                if (!$permlvl) {
                    // if there isn't a level 1, assumes the level 0 as editor
                    $permlvl = $workflow->getPermissionLevel(0, 'initial');
                }
                break;

            case 'submit': // submit new content
                $component .= 'edit';
                $workflow = new Clip_Workflow($pubtype);
                // assumes level 0 as the basic submit permission
                $permlvl = $workflow->getPermissionLevel(0, 'initial');
                break;

            case 'admin':
                break;

            case 'anyeditor':
                $permlvl = ACCESS_EDITOR;
            case 'anyadmin':
                //$component = "Clip::";
                //$instance = 'ANY';
                // TODO Use a reverse method to detect the available permissions rules
                break;

            default:
                throw new Exception(__f('%1$s: Invalid context passed [%2$s].', array('Clip_Access::toPubtype', DataUtil::formatForDisplay($context)), $dom));
        }

        // check the cached results
        static $cache = array();

        if (!isset($cache[$context][$uid][$component][$instance])) {
            $cache[$context][$uid][$component][$instance] = $permlvl ? SecurityUtil::checkPermission($component, $instance, $permlvl, $uid) : false;
        }

        return $cache[$context][$uid][$component][$instance];
    }

    /**
     * Checks if the current user has access to a publication.
     *
     * @param integer $pubtype Publication type instance or ID.
     * @param integer $pub     Publication instance or ID.
     * @param integer $id      Publication revision ID (optional if $pub is a instance) (default: null).
     * @param string  $context Context to check the access for (default: access).
     * @param string  $tplid   Template ID required for permission check on display (default: null).
     * @param integer $permlvl Required permission level to have, used on edit context only (default: null).
     * @param integer $uid     User ID of the user to check permission for (default: current user).
     * @param string  $action  Action ID required for permission check on exec (default: null).
     *
     * @return boolean True if allowed, false otherwise.
     */
    public static function toPub($pubtype, $pub, $id = null, $context = null, $tplid = null, $permlvl = null, $uid = null, $action = null)
    {
        $dom = ZLanguage::getModuleDomain('Clip');

        // fill default values if needed
        $context = $context ? strtolower($context) : 'edit';
        $tplid   = $tplid ? $tplid : '';
        $state   = '';
        $action  = $action ? $action : '';

        // be sure to have a Clip_Model_Pubtype instance
        if (!$pubtype instanceof Clip_Model_Pubtype) {
            if (!Clip_Util::validateTid($pubtype)) {
                return LogUtil::registerError(__f('%1$s: Invalid publication type ID passed [%2$s].', array('Clip_Access::toPub', DataUtil::formatForDisplay($pubtype)), $dom));
            }

            $pubtype = Clip_Util::getPubType($pubtype);
        }

        // when it's an instance we can do a complete check
        if ($pub instanceof Clip_Doctrine_Pubdata) {
            // check an already stored record
            if ($pub->exists()) {
                $pid = $pub['core_pid'];

                // state only needed on edit* context
                if (self::isPubStateNeeded($context)) {
                    $state = $pub->clipWorkflow('state');
                }
            } else {
                // the user may wants to save a new record
                $pid = '';
            }
        } else {
            if (is_numeric($pub)) {
                // direct pid
                $pid = (int)$pub;
            } else if (is_array($pub) && isset($pub['core_pid'])) {
                // pub as array
                $pid = (int)$pub['core_pid'];
            } else {
                return false;
            }

            // check if we need additional information
            if (self::isPubStateNeeded($context)) {
                // gets the online/latest revision state, if $id not specified
                if (!$id) {
                    $id = (int)ModUtil::apiFunc('Clip', 'user', 'getId', array('tid' => $pubtype->tid, 'pid' => $pid, 'lastrev'  => true));
                }

                // query for the state
                $dbtables = DBUtil::getTables();
                $wfcolumn = $dbtables['workflows_column'];
                $where = "WHERE $wfcolumn[module] = 'Clip' AND $wfcolumn[obj_table] = '{$pubtype->getTableName()}'
                            AND $wfcolumn[obj_idcolumn] = 'id' AND $wfcolumn[obj_id] = '" . DataUtil::formatForStore($id) . "'";

                $state = (string)DBUtil::selectField('workflows', 'state', $where);
            }
        }

        // evaluate the access depending of the required context
        $allowed = false;

        switch ($context)
        {
            case 'access':
                $permlvl = $permlvl ? $permlvl : ACCESS_READ;
                // TODO adjust with display/edit changes
                $allowed = SecurityUtil::checkPermission("Clip:{$pubtype->tid}:edit", "$pid:$state:$tplid", $permlvl, $uid);
                if (!$allowed) {
                    $allowed = SecurityUtil::checkPermission("Clip:{$pubtype->tid}:display", "$pid::$tplid", $permlvl, $uid);
                }
                break;

            case 'display':
                $permlvl = $permlvl ? $permlvl : ACCESS_READ;
                // TODO check core_online + normal user = false (relations check, etc)
                $allowed = SecurityUtil::checkPermission("Clip:{$pubtype->tid}:display", "$pid::$tplid", $permlvl, $uid);
                break;

            case 'form':
                // TODO consider edit.own
                $permlvl = $permlvl ? $permlvl : ACCESS_READ;
                $allowed = SecurityUtil::checkPermission("Clip:{$pubtype->tid}:edit", "$pid:$state:$tplid", $permlvl, $uid);
                break;

            case 'edit':
                // TODO consider edit.own
                if (!SecurityUtil::checkPermission("Clip:{$pubtype->tid}:edit", "$pid:$state:$tplid", ACCESS_READ, $uid)) {
                    // direct discard if do not have permission to the form
                    break;
                }

            case 'exec':
            case 'execinline':
                // TODO consider edit.own
                if (!$permlvl) {
                    // gets the minimum state permission level
                    $workflow = $pub instanceof Clip_Doctrine_Pubdata ? new Clip_Workflow($pubtype, $pub) : new Clip_Workflow($pubtype);
                    $mode     = $context == 'execinline' ? Clip_Workflow::ACTIONS_ALL : Clip_Workflow::ACTIONS_FORM;
                    $permlvl  = $workflow->getPermissionLevel(0, $state, $mode);
                }
                if ($permlvl) {
                    $allowed = SecurityUtil::checkPermission("Clip:{$pubtype->tid}:exec", "$pid:$state:$action", $permlvl, $uid);
                }
                break;
        }

        return $allowed;
    }

    public static function isPubStateNeeded($context)
    {
        return in_array($context, array('access', 'exec'))  || strpos($context, 'edit') === 0;
    } 
}
