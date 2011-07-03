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
class Clip_Form_Handler_Admin_Import extends Zikula_Form_AbstractHandler
{
    protected $referer;

    /**
     * Initialize function.
     */
    public function initialize(Zikula_Form_View $view)
    {
        // stores the return URL
        if (!$view->getStateData('referer')) {
            $adminurl = ModUtil::url('Clip', 'admin', 'main');
            $view->setStateData('referer', System::serverGetVar('HTTP_REFERER', $adminurl));
            // default values
            $view->assign('redirect', 0);
        }

        return true;
    }

    /**
     * Command handler.
     */
    public function handleCommand(Zikula_Form_View $view, &$args)
    {
        $this->referer = $view->getStateData('referer');

        // cancel processing
        if ($args['commandName'] == 'cancel') {
            return $view->redirect($this->referer);
        }

        // validates the input
        if (!$view->isValid()) {
            return false;
        }

        // get the data set in the form
        $data = $view->getValues();

        // handle the commands
        switch ($args['commandName'])
        {
            // import
            case 'import':
                // check for a problem uploading the file
                if (isset($data['file']['error']) && $data['file']['error'] !== 0) {
                    return $view->setErrorMsg(FileUtil::uploadErrorMsg($data['file']['error']));
                }

                // build the import instance
                $batch = new Clip_Import_Batch();

                $batch->setup($data);

                $result = $batch->execute();

                if (!$result) {
                    LogUtil::registerError($this->__('Import attempt failed.'));
                    return $view->registerError(true);
                }

                // check if the user wants to be redirected to the newly created pubtype
                if ($data['redirect'] == 1) {
                    $this->referer = $result;
                }

                LogUtil::registerStatus($this->__('Import done successfully.'));

                break;
        }

        return $view->redirect($this->referer);
    }
}