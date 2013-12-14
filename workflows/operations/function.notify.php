<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://github.com/zikula-modules/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Workflows_Operations
 */
/**
 * notify operation.
 *
 * @param object $pub                Publication related with the notification.
 * @param bool   $params['silent']   Hide or display a status/error message, (optional) (default: false).
 * @param string $params['group']    Workflow group to be notified (optional) (default: editors).
 * @param string $params['action']   Type of notification, short-identifier of the event and template (optional) (default: create).
 * @param string $params['template'] Template to use for the notification message located on pubtype/notify_TPL.tpl (optional) (default: group_type).
 *
 * @return bool Always true, notification does not interrupt the workflow action execution.
 */
function Clip_operation_notify(&$pub, $params)
{
    $dom = ZLanguage::getModuleDomain('Clip');
    $params['silent'] = isset($params['silent']) ? (bool) $params['silent'] : false;
    $params['group'] = isset($params['group']) ? $params['group'] : 'editors';
    $params['action'] = isset($params['action']) ? $params['action'] : 'create';
    $params['template'] = isset($params['template']) ? $params['template'] : "{$params['group']}_{$params['action']}";
    // utility vars
    $pubtype = Clip_Util::getPubType($pub['core_tid']);
    // create the View object
    $view = Zikula_View::getInstance('Clip');
    // locate the notification template to use
    $tplpath = $pubtype['folder'] . '/notify_' . $params['template'] . '.tpl';
    if ($view->template_exists($tplpath)) {
        // get the recipients
        if ($params['group'] == 'author') {
            $classname = Clip_Util_Plugins::getAdminClassname('Recipients');
            $recipients = $classname::postRead(array('u' . $pub['core_author']));
        } else {
            $recipients = Clip_Workflow_Util::getVarValue($pubtype, 'notify_' . $params['group'], array());
        }
        if ($recipients) {
            // event: notify the operation data
            $pub = Clip_Event::notify('data.edit.operation.notify', $pub, $params)->getData();
            $message = $view->assign($params)->assign('pubtype', $pubtype)->assign('pubdata', $pub)->fetch($tplpath);
            // convention: first line is the subject
            list($subject, $message) = preg_split('/((
?
)|(
?
))/', $message, 2);
            if (ModUtil::available('Mailer')) {
                $ok = ModUtil::apiFunc('Mailer', 'user', 'sendmessage', array('toaddress' => $recipients, 'subject' => $subject, 'body' => $message, 'html' => true));
            } else {
                $ok = mail(implode(', ', $recipients), $subject, $message);
            }
            // output message
            if (!$params['silent']) {
                if ($ok) {
                    LogUtil::registerStatus(__f('Notification sent to \'%s\' group.', $params['group'], $dom));
                } else {
                    LogUtil::registerStatus(__('Notification failed.', $dom));
                }
            }
        }
    } else {
        LogUtil::log(__f('Notification template [%s] not found.', $tplpath, $dom));
    }
    return true;
}