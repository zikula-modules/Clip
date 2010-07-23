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
 * Form handler to update module vars.
 */
class PageMaster_Form_Handler_Admin_ModifyConfig extends Form_Handler
{
    /**
     * Initialize function
     */
    function initialize(&$view)
    {
        $modvars = ModUtil::getVar('PageMaster');

        // upload dir check
        $siteroot = System::serverGetVar('DOCUMENT_ROOT');
        if (substr($siteroot, -1) == DIRECTORY_SEPARATOR) {
            $siteroot = substr($siteroot, 0, -1);
        }
        $siteroot .= System::getBaseUri().DIRECTORY_SEPARATOR;

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

        $view->assign('siteroot', DataUtil::formatForDisplay($siteroot))
             ->assign('updirstatus', $updirstatus)
             ->assign($modvars);

        return true;
    }

    /**
     * Command handler
     */
    function handleCommand(&$view, &$args)
    {
        $data = $view->getValues();

        // handle the commands
        switch ($args['commandName'])
        {
            // update the modvars
            case 'update':
                // upload path
                // remove the siteroot if was included
                $siteroot = substr(System::serverGetVar('DOCUMENT_ROOT'), 0, -1).System::getBaseUri().'/';
                $data['uploadpath'] = str_replace($siteroot, '', $data['uploadpath']);
                if (StringUtil::right($data['uploadpath'], 1) == '/') {
                    $data['uploadpath'] = StringUtil::left($data['uploadpath'], strlen($data['uploadpath']) - 1);
                }
                ModUtil::setVar('PageMaster', 'uploadpath', $data['uploadpath']);

                // development mode
                ModUtil::setVar('PageMaster', 'devmode', $data['devmode']);

                // let any other modules know that the modules configuration has been updated
                ModUtil::callHooks('module', 'updateconfig', 'PageMaster', array('module' => 'PageMaster'));

                LogUtil::registerStatus($this->__('Done! Module configuration updated.'));

                $view->redirect(ModUtil::url('PageMaster', 'admin', 'modifyconfig'));
                break;

            // cancel
            case 'cancel':
                $view->redirect(ModUtil::url('PageMaster', 'admin'));
        }

        return true;
    }
}
