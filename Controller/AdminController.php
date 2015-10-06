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

use Zikula_View;
use Matheo\Clip\Access;
use Matheo\Clip\Util\GrouptypesUtil;
use FormUtil;
use DataUtil;
use LogUtil;
use Matheo\Clip\Util;
use Clip_Form_Handler_Admin_Pubtypes;
use Clip_Form_Handler_Admin_Pubfields;
use Clip_Form_Handler_Admin_Relations;
use Matheo\Clip\Generator;
use Clip_Form_Handler_Admin_Export;
use Clip_Form_Handler_Admin_Import;
use ModUtil;
use System;
use Clip_Form_Handler_Admin_ModifyConfig;

/**
 * Admin Controller.
 */
class AdminController extends \Zikula_AbstractController
{
    /**
     * Post initialise.
     *
     * @return void
     */
    protected function postInitialize()
    {
        // In this controller we do not want caching.
        $this->view->setCaching(Zikula_View::CACHE_DISABLED);
    }
    
    /**
     * Grouptypes list screen with the existing pubtypes.
     */
    public function indexAction()
    {
        //// Security
        $this->throwForbiddenUnless(Access::toClip(ACCESS_ADMIN));
        $treejscode = GrouptypesUtil::getTreeJS(null, false, true, array('sortable' => true), 'admin');
        //// Output
        $this->view->assign('treejscode', $treejscode);
        return $this->view->fetch('clip_admin_main.tpl');
    }
    
    /**
     * Publication types list.
     */
    public function pubtypeinfoAction()
    {
        //// Pubtype
        // validate and get the publication type first
        $tid = FormUtil::getPassedValue('tid', null, 'GETPOST', FILTER_SANITIZE_NUMBER_INT);
        if (!Util::validateTid($tid)) {
            return LogUtil::registerError($this->__f('Error! Invalid publication type ID passed [%s].', DataUtil::formatForDisplay($tid)));
        }
        $pubtype = Util::getPubType($tid);
        //// Security
        $this->throwForbiddenUnless(Access::toPubtype($pubtype, 'admin'));
        // sort the relations by alias
        $relations = $pubtype->getRelations(false);
        uasort($relations, function ($a, $b) {
            return strcasecmp($a['alias'], $b['alias']);
        });
        //// Output
        return $this->view->assign('pubtype', $pubtype)->assign('relations', $relations)->fetch('clip_base_pubtypeinfo.tpl');
    }
    
    /**
     * Publication type edition.
     */
    public function pubtypeAction()
    {
        //// Pubtype
        // validate and get the publication type first
        $tid = FormUtil::getPassedValue('tid', null, 'GETPOST', FILTER_SANITIZE_NUMBER_INT);
        if ($tid && !Util::validateTid($tid)) {
            return LogUtil::registerError($this->__f('Error! Invalid publication type ID passed [%s].', DataUtil::formatForDisplay($tid)));
        }
        //// Security
        $this->throwForbiddenUnless(Access::toClip(ACCESS_ADMIN) || Access::toPubtype($tid, 'admin'));
        //// Output
        return Util::newForm($this, true)->execute('clip_base_pubtype.tpl', new Clip_Form_Handler_Admin_Pubtypes());
    }
    
    /**
     * Publication fields management.
     */
    public function pubfieldsAction()
    {
        //// Pubtype
        // validate and get the publication type first
        $tid = FormUtil::getPassedValue('tid', null, 'GETPOST', FILTER_SANITIZE_NUMBER_INT);
        if (!Util::validateTid($tid)) {
            return LogUtil::registerError($this->__f('Error! Invalid publication type ID passed [%s].', DataUtil::formatForDisplay($tid)));
        }
        //// Security
        $this->throwForbiddenUnless(Access::toClip(ACCESS_ADMIN) || Access::toPubtype($tid, 'admin'));
        //// Output
        return Util::newForm($this, true)->execute('clip_base_pubfields.tpl', new Clip_Form_Handler_Admin_Pubfields());
    }
    
    /**
     * Relations management.
     */
    public function relationsAction()
    {
        //// Pubtype
        // validate and get the publication type first
        $tid = FormUtil::getPassedValue('tid', null, 'GETPOST', FILTER_SANITIZE_NUMBER_INT);
        if ($tid && !Util::validateTid($tid)) {
            return LogUtil::registerError($this->__f('Error! Invalid publication type ID passed [%s].', DataUtil::formatForDisplay($tid)));
        }
        //// Security
        $this->throwForbiddenUnless(Access::toClip(ACCESS_ADMIN) || $tid && Access::toPubtype($tid, 'admin'));
        //// Output
        return Util::newForm($this, true)->execute('clip_base_relations.tpl', new Clip_Form_Handler_Admin_Relations());
    }
    
    /**
     * Code generation.
     */
    public function generatorAction($args = array())
    {
        //// Pubtype
        // validate and get the publication type
        $args['tid'] = isset($args['tid']) ? $args['tid'] : FormUtil::getPassedValue('tid');
        if (!Util::validateTid($args['tid'])) {
            return LogUtil::registerError($this->__f('Error! Invalid publication type ID passed [%s].', DataUtil::formatForDisplay($args['tid'])));
        }
        //// Security
        $this->throwForbiddenUnless(Access::toClip(ACCESS_ADMIN) || Access::toPubtype($args['tid'], 'admin'));
        //// Parameters
        $args = array('tid' => $args['tid'], 'code' => isset($args['code']) ? $args['code'] : FormUtil::getPassedValue('code', 'edit'));
        //// Validation
        if (!$args['code']) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'code'));
        }
        //// Execution
        // get the required output code
        switch ($args['code']) {
            case 'main':
                $path = $this->view->get_template_path('generic_main.tpl');
                $output = file_get_contents($path . '/generic_main.tpl');
                break;
            case 'list':
                $path = $this->view->get_template_path('generic_list.tpl');
                $output = file_get_contents($path . '/generic_list.tpl');
                break;
            case 'filter':
                $pubfields = Util::getPubFields($args['tid'])->toArray();
                foreach ($pubfields as $k => &$pubfield) {
                    // check that the field be filterable and has a default template at least
                    $tpl = "pubfields/filters/{$pubfield['fieldplugin']}_default.tpl";
                    if (!$pubfield['isfilterable'] || !$this->view->template_exists($tpl)) {
                        unset($pubfields[$k]);
                        continue;
                    }
                    // assign the input parameters
                    $pubfield['gen'] = (bool) $this->request->getPost()->filter("gen_{$pubfield['name']}");
                    $pubfield['tpl'] = $this->request->getPost()->filter(
                        "tpl_{$pubfield['name']}",
                        'default',
                        FILTER_SANITIZE_STRING
                    );
                }
                // include the core fields
                $pubfields['core_author'] = array('name' => 'core_author', 'title' => $this->__('Publication author'), 'fieldplugin' => 'core', 'tpl' => 'author', 'gen' => (bool) $this->request->getPost()->filter('gen_core_author'));
                $output = Generator::listfilter($args['tid'], $pubfields);
                $this->view->assign('pubfields', $pubfields);
                break;
            case 'display':
                $output = Generator::pubdisplay($args['tid'], false);
                break;
            case 'edit':
                $output = Generator::pubedit($args['tid']);
                break;
            case 'blocklist':
                $path = $this->view->get_template_path('generic_blocklist.tpl');
                $output = file_get_contents($path . '/generic_blocklist.tpl');
                break;
            case 'blockpub':
                $output = Generator::pubdisplay($args['tid'], false, true);
                break;
        }
        // code cleaning
        $output = str_replace("\n", '', $output);
        //// Output
        $this->view->assign('code', $args['code'])->assign('output', $output)->assign('pubtype', Util::getPubType($args['tid']));
        return $this->view->fetch('clip_base_generator.tpl');
    }
    
    /**
     * Export process.
     */
    public function clipexportAction()
    {
        //// Security
        $this->throwForbiddenUnless(Access::toClip(ACCESS_ADMIN));
        //// Output
        return FormUtil::newForm('Clip', $this)->execute('clip_admin_export.tpl', new Clip_Form_Handler_Admin_Export());
    }
    
    /**
     * Import process.
     */
    public function clipimportAction()
    {
        //// Security
        $this->throwForbiddenUnless(Access::toClip(ACCESS_ADMIN));
        //// Output
        return FormUtil::newForm('Clip', $this)->execute('clip_admin_import.tpl', new Clip_Form_Handler_Admin_Import());
    }
    
    /**
     * Reset models.
     */
    public function clipresetAction()
    {
        //// Security
        $this->throwForbiddenUnless(Access::toClip(ACCESS_ADMIN));
        //// Cleanup
        if (Generator::resetModels()) {
            LogUtil::registerStatus($this->__('The models were cleaned.'));
        } else {
            LogUtil::registerError($this->__('Error! The models could not be cleaned.'));
        }
        //// Redirect
        System::redirect(ModUtil::url('Clip', 'admin', 'modifyconfig'));
    }
    
    /**
     * Module configuration.
     */
    public function modifyconfigAction()
    {
        //// Security
        $this->throwForbiddenUnless(Access::toClip(ACCESS_ADMIN));
        //// Output
        return FormUtil::newForm('Clip', $this)->execute('clip_admin_modifyconfig.tpl', new Clip_Form_Handler_Admin_ModifyConfig());
    }
}
