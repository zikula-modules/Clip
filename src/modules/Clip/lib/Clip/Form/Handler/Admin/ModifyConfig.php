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
    protected $siteroot;

    /**
     * Initialize function.
     */
    public function initialize(Zikula_Form_View $view)
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

        // fill the output
        $view->assign('siteroot', DataUtil::formatForDisplay($this->siteroot))
             ->assign('updirstatus', $updirstatus)
             ->assign($modvars);

        return true;
    }

    /**
     * Command handler.
     */
    public function handleCommand(Zikula_Form_View $view, &$args)
    {
        // validates the input
        if (!$view->isValid()) {
            return false;
        }

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

                // shorturls default template
                $data['shorturls'] = preg_replace(Clip_Util::REGEX_TEMPLATE, '', $data['shorturls']);
                ModUtil::setVar('Clip', 'shorturls', $data['shorturls']);

                // development mode
                ModUtil::setVar('Clip', 'devmode', $data['devmode']);

                // max items per page
                ModUtil::setVar('Clip', 'maxperpage', $data['maxperpage']);

                LogUtil::registerStatus($this->__('Done! Module configuration updated.'));

                $view->redirect(ModUtil::url('Clip', 'admin', 'modifyconfig'));
                break;

            // cancel
            case 'cancel':
                $view->redirect(ModUtil::url('Clip', 'admin', 'main'));
        }

        return true;
    }
}
