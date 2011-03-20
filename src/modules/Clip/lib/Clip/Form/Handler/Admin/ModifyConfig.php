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
 * Form handler to update module vars.
 */
class Clip_Form_Handler_Admin_ModifyConfig extends Zikula_Form_AbstractHandler
{
    private $siteroot;

    /**
     * Initialize function
     */
    function initialize($view)
    {
        $modvars = ModUtil::getVar('Clip');

        // upload dir check
        $this->siteroot = System::serverGetVar('DOCUMENT_ROOT');
        if (substr($this->siteroot, -1) == DIRECTORY_SEPARATOR) {
            $this->siteroot = substr($this->siteroot, 0, -1);
        }
        $this->siteroot .= System::getBaseUri().DIRECTORY_SEPARATOR;

        // fills the directory state
        $updirstatus = 0;// doesn't exists
        if (file_exists($modvars['uploadpath'].'/')) {
            $updirstatus = 1; // exists
            if (is_dir($modvars['uploadpath'].'/')) {
                $updirstatus = 2; // is a directory
                if (is_writable($modvars['uploadpath'].'/')) {
                    $updirstatus = 3; // is writable
                }
            }
        }

        $view->assign('siteroot', DataUtil::formatForDisplay($this->siteroot))
             ->assign('updirstatus', $updirstatus)
             ->assign($modvars);

        return true;
    }

    /**
     * Command handler
     */
    function handleCommand($view, &$args)
    {
        $data = $view->getValues();

        // handle the commands
        switch ($args['commandName'])
        {
            // update the modvars
            case 'update':
                // upload path
                // remove the siteroot if was included
                $data['uploadpath'] = str_replace($this->siteroot, '', $data['uploadpath']);
                if (StringUtil::right($data['uploadpath'], 1) == DIRECTORY_SEPARATOR) {
                    $data['uploadpath'] = StringUtil::left($data['uploadpath'], strlen($data['uploadpath']) - 1);
                }
                ModUtil::setVar('Clip', 'uploadpath', $data['uploadpath']);

                // development mode
                ModUtil::setVar('Clip', 'devmode', $data['devmode']);

                // max items per page
                ModUtil::setVar('Clip', 'maxperpage', $data['maxperpage']);

                // let any other modules know that the modules configuration has been updated
                ModUtil::callHooks('module', 'updateconfig', 'Clip', array('module' => 'Clip'));

                LogUtil::registerStatus($this->__('Done! Module configuration updated.'));

                $view->redirect(ModUtil::url('Clip', 'admin', 'modifyconfig'));
                break;

            // cancel
            case 'cancel':
                $view->redirect(ModUtil::url('Clip', 'admin'));
        }

        return true;
    }
}
