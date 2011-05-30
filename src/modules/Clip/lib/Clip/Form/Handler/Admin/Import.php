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
    protected $returnurl;

    /**
     * Initialize function
     */
    function initialize($view)
    {
        // stores the return URL
        if (!$view->getStateData('rStateeturnurl')) {
            $adminurl = ModUtil::url('Clip', 'admin');
            $view->setStateData('returnurl', System::serverGetVar('HTTP_REFERER', $adminurl));
        }

        return true;
    }

    /**
     * Command handler
     */
    function handleCommand(Zikula_Form_View $view, &$args)
    {
        $this->returnurl = $view->getStateData('returnurl');

        // cancel processing
        if ($args['commandName'] == 'cancel') {
            return $view->redirect($this->returnurl);
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
                $batch = new Clip_Import_Batch($data);

                if (!$batch->execute()) {
                    return $view->registerError(true);
                }

                LogUtil::registerStatus($this->__('Import done successfully.'));

                break;
        }

        return $view->redirect($this->returnurl);
    }
}