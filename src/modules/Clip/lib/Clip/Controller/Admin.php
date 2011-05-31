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
        $this->throwForbiddenUnless(Clip_Access::toClip(ACCESS_ADMIN));

        $treejscode = Clip_Util_Grouptypes::getTreeJS(null, false, true, array('sortable' => true), ACCESS_ADMIN);

        //// Output
        $this->view->assign('treejscode', $treejscode);

        return $this->view->fetch('clip_admin_main.tpl');
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
     * Publication types list.
     */
    public function pubtypeinfo()
    {
        //// Pubtype
        // validate and get the publication type first
        $tid = FormUtil::getPassedValue('tid', null, 'GETPOST', FILTER_SANITIZE_NUMBER_INT);;

        if (!Clip_Util::validateTid($tid)) {
            return LogUtil::registerError($this->__f('Error! Invalid publication type ID passed [%s].', DataUtil::formatForDisplay($tid)));
        }

        $pubtype = Clip_Util::getPubType($tid);

        //// Security
        $this->throwForbiddenUnless(Clip_Access::toPubtype($pubtype));

        //// Output
        return $this->view->assign('pubtype', $pubtype)
                          ->fetch("clip_base_pubtypeinfo.tpl");
    }

    /**
     * Publication type edition.
     */
    public function pubtype()
    {
        //// Pubtype
        // validate and get the publication type first
        $tid = FormUtil::getPassedValue('tid', null, 'GETPOST', FILTER_SANITIZE_NUMBER_INT);;

        if (!Clip_Util::validateTid($tid)) {
            return LogUtil::registerError($this->__f('Error! Invalid publication type ID passed [%s].', DataUtil::formatForDisplay($tid)));
        }

        //// Security
        $this->throwForbiddenUnless(Clip_Access::toPubtype($tid));

        //// Output
        return FormUtil::newForm('Clip', $this)
               ->execute('clip_admin_pubtype.tpl',
                         new Clip_Form_Handler_Admin_Pubtypes());
    }

    /**
     * Publication fields management.
     */
    public function pubfields()
    {
        //// Pubtype
        // validate and get the publication type first
        $tid = FormUtil::getPassedValue('tid', null, 'GETPOST', FILTER_SANITIZE_NUMBER_INT);;

        if (!Clip_Util::validateTid($tid)) {
            return LogUtil::registerError($this->__f('Error! Invalid publication type ID passed [%s].', DataUtil::formatForDisplay($tid)));
        }

        //// Security
        $this->throwForbiddenUnless(Clip_Access::toPubtype($tid));

        //// Output
        return FormUtil::newForm('Clip', $this)
               ->execute('clip_admin_pubfields.tpl',
                         new Clip_Form_Handler_Admin_Pubfields());
    }

    /**
     * Relations management.
     */
    public function relations()
    {
        //// Security
        $this->throwForbiddenUnless(Clip_Access::toClip(ACCESS_ADMIN));

        //// Output
        return FormUtil::newForm('Clip', $this)
               ->execute('clip_admin_relations.tpl',
                         new Clip_Form_Handler_Admin_Relations());
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
     * Admin publist screen.
     * FIXME move this to editors panel.
     */
    public function publist($args = array())
    {
        //// Pubtype
        // validate and get the publication type first
        $args['tid'] = isset($args['tid']) ? $args['tid'] : FormUtil::getPassedValue('tid');

        if (!Clip_Util::validateTid($args['tid'])) {
            return LogUtil::registerError($this->__f('Error! Invalid publication type ID passed [%s].', DataUtil::formatForDisplay($args['tid'])));
        }

        $pubtype = Clip_Util::getPubType($args['tid'])->mapTitleField();

        //// Security
        $this->throwForbiddenUnless(Clip_Access::toPubtype($pubtype));

        // define the arguments
        $apiargs = array(
            'tid'           => $args['tid'],
            'filter'        => isset($args['filter']) ? $args['filter'] : (FormUtil::getPassedValue('filter') ? null : 'core_online:eq:1'),
            'orderby'       => isset($args['orderby']) ? $args['orderby'] : FormUtil::getPassedValue('orderby'),
            'itemsperpage'  => (isset($args['itemsperpage']) && is_numeric($args['itemsperpage']) && $args['itemsperpage'] >= 0) ? (int)$args['itemsperpage'] : abs((int)FormUtil::getPassedValue('itemsperpage', $pubtype['itemsperpage'])),
            'handleplugins' => isset($args['handleplugins']) ? (bool)$args['handleplugins'] : false,
            'loadworkflow'  => isset($args['loadworkflow']) ? (bool)$args['loadworkflow'] : true,
            'checkperm'     => false,
            'countmode'     => 'both'
        );
        $args = array(
            'startnum'      => (isset($args['startnum']) && is_numeric($args['startnum'])) ? (int)$args['startnum'] : (int)FormUtil::getPassedValue('startnum', 0),
            'page'          => (isset($args['page']) && is_numeric($args['page'])) ? (int)$args['page'] : (int)abs(FormUtil::getPassedValue('page', 1))
        );

        //// Misc values
        if ($apiargs['itemsperpage'] == 0) {
            $apiargs['itemsperpage'] = $pubtype['itemsperpage'] > 0 ? $pubtype['itemsperpage'] : 15;
        }

        if ($args['page'] > 1) {
            $apiargs['startnum'] = ($args['page']-1)*$apiargs['itemsperpage']+1;
        }

        Clip_Util::setArgs('adminlist', $args);

        //// Execution
        // uses the API to get the list of publications
        $result = ModUtil::apiFunc('Clip', 'user', 'getall', $apiargs);

        //// Output
        // assign the output variables
        $this->view->assign('pubtype',  $pubtype)
                   ->assign('publist',  $result['publist'])
                   ->assign('clipargs', Clip_Util::getArgs());

        // assign the pager values
        $this->view->assign('pager', array('numitems'     => $result['pubcount'],
                                           'itemsperpage' => $apiargs['itemsperpage']));

        // custom pubtype template check
        $customtpl = $pubtype['outputset'].'/adminlist.tpl';

        if ($this->view->template_exists($customtpl)) {
            return $this->view->fetch($customtpl);
        }

        return $this->view->fetch('clip_base_publist.tpl');
    }

    /**
     * History screen.
     */
    public function history($args = array())
    {
        //// Pubtype
        // validate and get the publication type first
        $args['tid'] = isset($args['tid']) ? $args['tid'] : FormUtil::getPassedValue('tid');

        if (!Clip_Util::validateTid($args['tid'])) {
            return LogUtil::registerError($this->__f('Error! Invalid publication type ID passed [%s].', DataUtil::formatForDisplay($args['tid'])));
        }

        $pubtype = Clip_Util::getPubType($args['tid'])->mapTitleField();

        //// Parameters
        // define the arguments
        $args = array(
            'tid' => $args['tid'],
            'pid' => isset($args['pid']) ? (int)$args['pid'] : (int)FormUtil::getPassedValue('pid')
        );

        //// Validation
        // validate the passed publication ID
        if (empty($args['pid']) || !is_numeric($args['pid'])) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'pid'));
        }

        //// Security
        // FIXME SECURITY rework with Clip_Access to check the online/latest revision state access
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Clip:edit:', "{$args['tid']}:{$args['pid']}:", ACCESS_EDIT));

        //// Execution
        // get the collection of pubs
        $publist = Doctrine_Core::getTable('Clip_Model_Pubdata'.$args['tid'])
                       ->selectCollection("core_pid = '{$args['pid']}'", 'core_revision DESC');

        for ($i = 0; $i < count($publist); $i++) {
            $publist[$i]->clipProcess(array('handleplugins' => true, 'loadworkflow' => true));
        }

        //// Output
        $this->view->assign('pubtype', $pubtype)
                   ->assign('publist', $publist);

        return $this->view->fetch('clip_base_history.tpl');
    }

    /**
     * Code generation.
     */
    public function generator($args = array())
    {
        //// Security
        $this->throwForbiddenUnless(Clip_Access::toClip(ACCESS_ADMIN));

        //// Pubtype
        // validate and get the publication type
        $args['tid'] = isset($args['tid']) ? $args['tid'] : FormUtil::getPassedValue('tid');

        if (!Clip_Util::validateTid($args['tid'])) {
            return LogUtil::registerError($this->__f('Error! Invalid publication type ID passed [%s].', DataUtil::formatForDisplay($args['tid'])));
        }

        //// Parameters
        $args = array(
            'tid'  => $args['tid'],
            'code' => isset($args['code']) ? $args['code'] : FormUtil::getPassedValue('code', 'form')
        );

        //// Validation
        if (!$args['code']) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'code'));
        }

        //// Execution
        // get the required output code
        switch ($args['code'])
        {
            case 'form':
                $output = Clip_Generator::pubedit($args['tid']);
                break;

            case 'list':
                $path = $this->view->get_template_path('clip_generic_list.tpl');
                $output = file_get_contents($path.'/clip_generic_list.tpl');
                break;

            case 'display':
                $output = Clip_Generator::pubdisplay($args['tid'], false);
                break;

            case 'blocklist':
                $path = $this->view->get_template_path('clip_generic_blocklist.tpl');
                $output = file_get_contents($path.'/clip_generic_blocklist.tpl');
                break;

            case 'blockpub':
                $output = Clip_Generator::pubdisplay($args['tid'], false, true);
                break;
        }

        // code cleaning
        $output = DataUtil::formatForDisplay($output);
        $output = str_replace("\n", '<br />', $output);

        //// Output
        $this->view->assign('code',    $args['code'])
                   ->assign('output',  $output)
                   ->assign('pubtype', Clip_Util::getPubType($args['tid']));

        return $this->view->fetch("clip_base_generator.tpl");
    }

    /**
     * Javascript hierarchical menu of edit links.
     */
    public function editlist()
    {
        //// Security
        $this->throwForbiddenUnless(Clip_Access::toClip(ACCESS_ADMIN));

        $args = array(
            'menu'       => 1,
            'returntype' => 'admin',
            'orderby'    => 'core_title'
        );

        return ModUtil::func('Clip', 'user', 'editlist', $args);
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
