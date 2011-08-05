<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Controller
 */

/**
 * Import Controller.
 */
class Clip_Controller_Import extends Zikula_AbstractController
{
    /**
     * Setup to behave as the Admin controller.
     */
    public function postInitialize()
    {
        $serviceManager = ServiceUtil::getManager();
        $themeInstance  = $serviceManager->getService('zikula.theme');
        $themeInstance->type = 'admin';
        $themeInstance->load_config();

        $this->view->addPluginDir('system/Admin/templates/plugins');
        $this->view->load_filter('output', 'admintitle');
    }

    /**
     * Installer of default pubtypes Blog and Pages.
     */
    public function defaultypes()
    {
        $this->throwForbiddenUnless(Clip_Access::toClip(ACCESS_ADMIN));

        Clip_Util::installDefaultypes();

        $this->redirect(ModUtil::url('Clip', 'admin', 'modifyconfig'));
    }

    /**
     * Pagesetter import.
     */
    public function importps()
    {
        $this->throwForbiddenUnless(Clip_Access::toClip(ACCESS_ADMIN));

        $step = FormUtil::getPassedValue('step');
        if (!empty($step)) {
            ModUtil::apiFunc('Clip', 'import', 'importps'.$step);
        }

        // check if there are pubtypes already
        $numpubtypes = Doctrine_Core::getTable('Clip_Model_Pubtype')->selectCount();

        // build and return the output
        $this->view->assign('alreadyexists', $numpubtypes > 0 ? true : false);

        return $this->view->fetch('import_pagesetter.tpl');
    }
}
