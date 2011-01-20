<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Form_Handler_Admin
 */

/**
 * Form handler to manage data import.
 */
class Clip_Form_Handler_Admin_Import extends Form_Handler
{
    private $returnurl;

    /**
     * Initialize function
     */
    function initialize($view)
    {
        // stores the return URL
        if (empty($this->returnurl)) {
            $adminurl = ModUtil::url('Clip', 'admin');
            $this->returnurl = System::serverGetVar('HTTP_REFERER', $adminurl);
        }

        return true;
    }

    /**
     * Command handler
     */
    function handleCommand($view, &$args)
    {
        if ($args['commandName'] == 'cancel') {
            return $view->redirect($this->returnurl);
        }

        if (!$view->isValid()) {
            return false;
        }

        $data = $view->getValues();

        // handle the commands
        switch ($args['commandName'])
        {
            // import
            case 'import':
                if (isset($data['file']['error']) && $data['file']['error'] !== 0) {
                    return $view->setErrorMsg(FileUtil::uploadErrorMsg($data['file']['error']));
                }

                // build the import instance
                $batch = new Clip_Import_Batch($data);

                if (!$batch->execute()) {
                    $view->errorMsgSet = true;
                } else {
                    LogUtil::registerStatus($this->__('Import done successfully.'));
                }
                break;
        }

        return $view->redirect($this->returnurl);
    }
}
