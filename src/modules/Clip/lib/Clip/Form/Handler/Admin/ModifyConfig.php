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

        // directory status on filesystems
        $status = array('upload' => 0, 'models' => 0); // doesn't exists

        // fills the directory state
        foreach (array_keys($status) as $name) {
            if (file_exists($modvars[$name.'path'].'/')) {
                $status[$name] = 1; // exists
                if (is_dir($modvars[$name.'path'].'/')) {
                    $status[$name] = 2; // is a directory
                    if (is_writable($modvars[$name.'path'].'/')) {
                        $status[$name] = 3; // is writable
                    }
                }
            }
        }

        // fill the output
        $view->assign('siteroot', DataUtil::formatForDisplay($this->siteroot))
             ->assign('status', $status)
             ->assign('pubtypes', Clip_Util_Selectors::pubtypes())
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
                // models path
                if (StringUtil::right($data['modelspath'], 1) == DIRECTORY_SEPARATOR) {
                    $data['modelspath'] = StringUtil::left($data['modelspath'], -1);
                }
                if (StringUtil::right($data['modelspath'], 10) != 'ClipModels') {
                    return $view->setPluginErrorMsg('modelspath', $this->__("The name of the temporary folder must be 'ClipModels'."));
                }
                ModUtil::setVar('Clip', 'modelspath', $data['modelspath']);

                // upload path
                // remove the siteroot if was included
                $data['uploadpath'] = str_replace($this->siteroot, '', $data['uploadpath']);
                if (StringUtil::right($data['uploadpath'], 1) == DIRECTORY_SEPARATOR) {
                    $data['uploadpath'] = StringUtil::left($data['uploadpath'], -1);
                }
                ModUtil::setVar('Clip', 'uploadpath', $data['uploadpath']);

                // shorturls default template
                $data['shorturls'] = preg_replace(Clip_Util::REGEX_TEMPLATE, '', $data['shorturls']);
                ModUtil::setVar('Clip', 'shorturls', $data['shorturls']);

                // default publication type
                ModUtil::setVar('Clip', 'pubtype', $data['pubtype']);

                // common tpls enabled
                ModUtil::setVar('Clip', 'commontpls', $data['commontpls']);

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
