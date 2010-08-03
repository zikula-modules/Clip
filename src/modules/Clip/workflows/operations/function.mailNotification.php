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
 * updatePub operation
 *
 * @param  array  $pub                    publication to update
 * @param  int    $params['online']       (optional) online value for the publication
 * @param  bool   $params['newrevision']  (optional) flag to create a new revision or not, default: true
 * @param  string $params['nextstate']    (optional) state of the updated publication
 * @param  bool   $params['silent']       (optional) hide or display a status/error message, default: false
 * @return array  publication id as index with boolean value: true if success, false otherwise
 */
function PageMaster_operation_mailNotification(&$pub, $params)
{
    $dom = ZLanguage::getModuleDomain('PageMaster');

    $silent = isset($params['silent']) ? (bool)$params['silent'] : false;
    $group = isset($params['group']) ? $params['group'] : 'editors';
    $type = isset($params['type']) ? $params['type'] : 'contentnew';
    $template = isset($params['template']) ? $params['template'] : "{$group}_{$type}";

    $ok = false;

    $render = Zikula_View::getInstance('PageMaster');

    if ($render->template_exists("emails/$template.tpl")) {
        $render->assign('pub', $pub);

        $message = $render->fetch($template);

        // convention: first line is the subject
        //$subject = 

        // TODO Configuration of recipient groups
        //$recipients = PageMasterUtil::getPubTypeRecipients($group);

        if (ModUtil::available('Mailer')) {
            $ok = ModUtil::apiFunc('Mailer', 'user', 'sendmessage',
                                   array('toaddress' => $recipients,
                                         'subject'   => $subject,
                                         'body'      => $message,
                                         'html'      => true));
        } else {
            $ok = mail($recipients, $subject, $message);
        }

        // output message
        if (!$silent) {
            if ($ok) {
                LogUtil::registerStatus(__f("Mailing new content to '%s' failed.", $group, $dom));
            } else {
                LogUtil::registerError(__('Error! Failed to update publication.', $dom));
            }
        }
    }

    return true;//$ok;
}
