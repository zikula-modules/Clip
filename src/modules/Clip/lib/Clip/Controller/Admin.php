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
 * Admin Controller.
 */
class Clip_Controller_Admin extends Zikula_AbstractController
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
    public function main()
    {
        //// Security
        $this->throwForbiddenUnless(Clip_Access::toClip(ACCESS_ADMIN) /*Clip_Access::toPubtype(null, 'anyadmin')*/);

        $treejscode = Clip_Util_Grouptypes::getTreeJS(null, false, true, array('sortable' => true), 'admin');

        //// Output
        $this->view->assign('treejscode', $treejscode);

        return $this->view->fetch('clip_admin_main.tpl');
    }

    /**
     * Publication types list.
     */
    public function pubtypeinfo()
    {
        //// Pubtype
        // validate and get the publication type first
        $tid = FormUtil::getPassedValue('tid', null, 'GETPOST', FILTER_SANITIZE_NUMBER_INT);

        if (!Clip_Util::validateTid($tid)) {
            return LogUtil::registerError($this->__f('Error! Invalid publication type ID passed [%s].', DataUtil::formatForDisplay($tid)));
        }

        $pubtype = Clip_Util::getPubType($tid);

        //// Security
        $this->throwForbiddenUnless(Clip_Access::toPubtype($pubtype, 'admin'));

        // sort the relations by alias
        $relations = $pubtype->getRelations(false);
        uasort($relations, function($a, $b) { return strcasecmp($a['alias'], $b['alias']); });

        //// Output
        return $this->view->assign('pubtype', $pubtype)
                          ->assign('relations', $relations)
                          ->fetch("clip_base_pubtypeinfo.tpl");
    }

    /**
     * Publication type edition.
     */
    public function pubtype()
    {
        //// Pubtype
        // validate and get the publication type first
        $tid = FormUtil::getPassedValue('tid', null, 'GETPOST', FILTER_SANITIZE_NUMBER_INT);

        if ($tid && !Clip_Util::validateTid($tid)) {
            return LogUtil::registerError($this->__f('Error! Invalid publication type ID passed [%s].', DataUtil::formatForDisplay($tid)));
        }

        //// Security
        $this->throwForbiddenUnless(Clip_Access::toClip(ACCESS_ADMIN) || Clip_Access::toPubtype($tid, 'admin'));

        //// Output
        return Clip_Util::newForm($this, true)
               ->execute('clip_base_pubtype.tpl',
                         new Clip_Form_Handler_Admin_Pubtypes());
    }

    /**
     * Publication fields management.
     */
    public function pubfields()
    {
        //// Pubtype
        // validate and get the publication type first
        $tid = FormUtil::getPassedValue('tid', null, 'GETPOST', FILTER_SANITIZE_NUMBER_INT);

        if (!Clip_Util::validateTid($tid)) {
            return LogUtil::registerError($this->__f('Error! Invalid publication type ID passed [%s].', DataUtil::formatForDisplay($tid)));
        }

        //// Security
        $this->throwForbiddenUnless(Clip_Access::toClip(ACCESS_ADMIN) || Clip_Access::toPubtype($tid, 'admin'));

        //// Output
        return Clip_Util::newForm($this, true)
               ->execute('clip_base_pubfields.tpl',
                         new Clip_Form_Handler_Admin_Pubfields());
    }

    /**
     * Relations management.
     */
    public function relations()
    {
        //// Pubtype
        // validate and get the publication type first
        $tid = FormUtil::getPassedValue('tid', null, 'GETPOST', FILTER_SANITIZE_NUMBER_INT);

        if ($tid && !Clip_Util::validateTid($tid)) {
            return LogUtil::registerError($this->__f('Error! Invalid publication type ID passed [%s].', DataUtil::formatForDisplay($tid)));
        }

        //// Security
        $this->throwForbiddenUnless(Clip_Access::toClip(ACCESS_ADMIN) || $tid && Clip_Access::toPubtype($tid, 'admin'));

        //// Output
        return Clip_Util::newForm($this, true)
               ->execute('clip_base_relations.tpl',
                         new Clip_Form_Handler_Admin_Relations());
    }

    /**
     * Code generation.
     */
    public function generator($args = array())
    {
        //// Pubtype
        // validate and get the publication type
        $args['tid'] = isset($args['tid']) ? $args['tid'] : FormUtil::getPassedValue('tid');

        if (!Clip_Util::validateTid($args['tid'])) {
            return LogUtil::registerError($this->__f('Error! Invalid publication type ID passed [%s].', DataUtil::formatForDisplay($args['tid'])));
        }

        //// Security
        $this->throwForbiddenUnless(Clip_Access::toClip(ACCESS_ADMIN) || Clip_Access::toPubtype($args['tid'], 'admin'));

        //// Parameters
        $args = array(
            'tid'  => $args['tid'],
            'code' => isset($args['code']) ? $args['code'] : FormUtil::getPassedValue('code', 'edit')
        );

        //// Validation
        if (!$args['code']) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'code'));
        }

        //// Execution
        // get the required output code
        switch ($args['code'])
        {
            case 'main':
                $path = $this->view->get_template_path('generic_main.tpl');
                $output = file_get_contents($path.'/generic_main.tpl');
                break;

            case 'list':
                $path = $this->view->get_template_path('generic_list.tpl');
                $output = file_get_contents($path.'/generic_list.tpl');
                break;

            case 'display':
                $output = Clip_Generator::pubdisplay($args['tid'], false);
                break;

            case 'edit':
                $output = Clip_Generator::pubedit($args['tid']);
                break;

            case 'blocklist':
                $path = $this->view->get_template_path('generic_blocklist.tpl');
                $output = file_get_contents($path.'/generic_blocklist.tpl');
                break;

            case 'blockpub':
                $output = Clip_Generator::pubdisplay($args['tid'], false, true);
                break;
        }

        // code cleaning
        $output = str_replace("\r", '', $output);

        //// Output
        $this->view->assign('code',    $args['code'])
                   ->assign('output',  $output)
                   ->assign('pubtype', Clip_Util::getPubType($args['tid']));

        return $this->view->fetch('clip_base_generator.tpl');
    }

    /**
     * Export process.
     */
    public function clipexport()
    {
        //// Security
        $this->throwForbiddenUnless(Clip_Access::toClip(ACCESS_ADMIN));

        //// Output
        return FormUtil::newForm('Clip', $this)
               ->execute('clip_admin_export.tpl',
                         new Clip_Form_Handler_Admin_Export());
    }

    /**
     * Import process.
     */
    public function clipimport()
    {
        //// Security
        $this->throwForbiddenUnless(Clip_Access::toClip(ACCESS_ADMIN));

        //// Output
        return FormUtil::newForm('Clip', $this)
               ->execute('clip_admin_import.tpl',
                         new Clip_Form_Handler_Admin_Import());
    }

    /**
     * Reset models.
     */
    public function clipreset()
    {
        //// Security
        $this->throwForbiddenUnless(Clip_Access::toClip(ACCESS_ADMIN));

        //// Cleanup
        if (Clip_Generator::resetModels()) {
            LogUtil::registerStatus($this->__('The models were cleaned.'));
        } else {
            LogUtil::registerError($this->__('Error! The models could not be cleaned.'));
        }

        //// Redirect
        return System::redirect(ModUtil::url('Clip', 'admin', 'modifyconfig'));
    }

    /**
     * Module configuration.
     */
    public function modifyconfig()
    {
        //// Security
        $this->throwForbiddenUnless(Clip_Access::toClip(ACCESS_ADMIN));

        //// Output
        return FormUtil::newForm('Clip', $this)
               ->execute('clip_admin_modifyconfig.tpl',
                         new Clip_Form_Handler_Admin_ModifyConfig());
    }

    /**
     * @see Clip_Controller_Admin::generate
     *
     * @deprecated 0.9
     */
    public function showcode($args)
    {
        return $this->generator($args);
    }
}
