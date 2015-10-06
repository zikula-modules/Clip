<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://github.com/zikula-modules/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Controller
 */

namespace Matheo\Clip\Controller;

use ServiceUtil;
use Matheo\Clip\Access;
use Matheo\Clip\Util;
use ModUtil;
use FormUtil;
use Doctrine_Core;

/**
 * Import Controller.
 */
class ImportController extends \Zikula_AbstractController
{
    /**
     * Setup to behave as the Admin controller.
     */
    public function postInitialize()
    {
        $serviceManager = ServiceUtil::getManager();
        $themeInstance = $serviceManager->getService('zikula.theme');
        $themeInstance->type = 'admin';
        $themeInstance->load_config();
        $this->view->addPluginDir('system/Admin/templates/plugins');
        $this->view->load_filter('output', 'admintitle');
    }
    
    /**
     * Installer of default pubtypes Blog and Pages.
     */
    public function defaultypesAction()
    {
        $this->throwForbiddenUnless(Access::toClip(ACCESS_ADMIN));
        Util::installDefaultypes();
        $this->redirect(ModUtil::url('Clip', 'admin', 'modifyconfig'));
    }
    
    /**
     * Pagesetter import.
     */
    public function importpsAction()
    {
        $this->throwForbiddenUnless(Access::toClip(ACCESS_ADMIN));
        $step = FormUtil::getPassedValue('step');
        if (!empty($step)) {
            ModUtil::apiFunc('Clip', 'import', 'importps' . $step);
        }
        // check if there are pubtypes already
        $numpubtypes = Doctrine_Core::getTable('Clip_Model_Pubtype')->selectCount();
        // build and return the output
        $this->view->assign('alreadyexists', $numpubtypes > 0 ? true : false);
        return $this->view->fetch('import_pagesetter.tpl');
    }
}
