<?php

function PageMaster_operation_mailNewContentMessage(&$pub, $params)
{
    $dom = ZLanguage::getModuleDomain('PageMaster');
    $silent = isset($params['silent']) ? (bool)$params['silent'] : false;
    $editURL = ModUtil::url('PageMaster', 'user', 'pubedit',
            array('tid'    => $pub['tid'],
            'id'     => $pub['id'],
            'action' => 'edit'));

    if ($args['PUBREFID'] == 'pid') {
        $viewURL = ModUtil::url('PageMaster', 'user', 'viewpub',
                array('tid'    => $pub['tid'],
                'pid'    => $pub['core']['pid']));
    } else {
        $viewURL = ModUtil::url('PageMaster', 'user', 'viewpub',
                array('tid'    => $pub['tid'],
                'id'     => $pub['id']));
    }

    $message = $args['MESSAGE'] . "\n\nEdit: $editURL\nView: $viewURL";
    $subject = $args['SUBJECT'];
    $mailTo  = $args['RECIPIENT'];

    if (!empty($mailTo)) {
        $recipients = str_replace ("\r\n", ',', $mailTo);
        $recipients = str_replace("\n", ",", $recipients);
        $recipients = str_replace("\r", ",", $recipients);

        if (ModUtil::available('Mailer')  &&  ModUtil::loadApi('Mailer', 'user')) {
            $ok = ModUtil::apiFunc('Mailer', 'user', 'sendmessage',
                    array('toaddress' => $recipients,
                    'subject'   => $subject,
                    'body'      => $message,
                    'html'      => false));

            if ($ok !== true && $ok !== false) {
                return LogUtil::registerError(__('Error! Failed to send mail.', $dom));
            }
        } else {
            $ok = mail($recipients, $subject, $message);
        }
        // output message
        if (!$silent) {
            if ($ok) {
                LogUtil::registerStatus(__("Mailing new content to '$mailTo' failed.", $dom));
            } else {
                LogUtil::registerError(__('Error! Failed to update publication.', $dom));
            }
        }
    }

    return pagesetterWFOperationOk;
}
