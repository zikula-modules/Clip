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
 * pnForm handler for updating module vars
 *
 * @author kundi
 */
class PageMaster_Form_Handler_Admin_ModifyConfig extends Form_Handler
{
    /**
     * Initialize function
     */
    function initialize(&$render)
    {
        $modvars = ModUtil::getVar('PageMaster');

        $render->assign($modvars);

        // upload dir check
        $siteroot = System::serverGetVar('DOCUMENT_ROOT');
        if (substr($siteroot, -1) == DIRECTORY_SEPARATOR) {
            $siteroot = substr($siteroot, 0, -1);
        }
        $siteroot .= System::getBaseUri().DIRECTORY_SEPARATOR;

        $render->assign('siteroot', DataUtil::formatForDisplay($siteroot));

        // fills the directory state
        if (file_exists($modvars['uploadpath'].'/')) {
            $render->assign('updirstatus', 1); // exists
            if (is_dir($modvars['uploadpath'].'/')) {
                $render->assign('updirstatus', 2); // is a directory
                if (is_writable($modvars['uploadpath'].'/')) {
                    $render->assign('updirstatus', 3); // is writable
                }
            }
        } else {
            $render->assign('updirstatus', 0); // doesn't exists
        }

        return true;
    }

    /**
     * Command handler
     */
    function handleCommand(&$render, &$args)
    {
        $dom = ZLanguage::getModuleDomain('PageMaster');

        $data = $render->getValues();

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

                LogUtil::registerStatus(__('Done! Module configuration updated.', $dom));

                return System::redirect(ModUtil::url('PageMaster', 'admin', 'modifyconfig'));

            // cancel
            case 'cancel':
                return System::redirect(ModUtil::url('PageMaster', 'admin'));
        }

        return true;
    }
}
