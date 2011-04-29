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
     * Grouptypes list screen with the existing pubtypes.
     */
    public function main()
    {
        //// Security check
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Clip::', '::', ACCESS_EDIT));

        $treejscode = Clip_Util::getGrouptypesTreeJS(null, true, true);

        //// Output
        $this->view->assign('treejscode', $treejscode)
                   ->add_core_data();

        return $this->view->fetch('clip_admin_main.tpl');
    }

    /**
     * Module configuration.
     */
    public function modifyconfig()
    {
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Clip::', '::', ACCESS_ADMIN));

        // return the form output
        return FormUtil::newForm('Clip', $this)
               ->execute('clip_admin_modifyconfig.tpl',
                         new Clip_Form_Handler_Admin_ModifyConfig());
    }

    /**
     * Publication types list.
     */
    public function pubtypeinfo($args=array())
    {
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Clip::', '::', ACCESS_ADMIN));

        //// Validation
        // get the tid first
        $args['tid'] = isset($args['tid']) ? $args['tid'] : FormUtil::getPassedValue('tid');
        $type        = FormUtil::getPassedValue('type', 'admin');

        if (!Clip_Util::validateTid($args['tid'])) {
            return LogUtil::registerError($this->__f('Error! Invalid publication type ID passed [%s].', DataUtil::formatForDisplay($args['tid'])));
        }

        $pubtype = Clip_Util::getPubType($args['tid']);

        return $this->view->assign('pubtype', $pubtype)
                          ->fetch("clip_{$type}_pubtypeinfo.tpl");
    }

    /**
     * Publication type edition.
     */
    public function pubtype()
    {
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Clip::', '::', ACCESS_ADMIN));

        // return the form output
        return FormUtil::newForm('Clip', $this)
               ->execute('clip_admin_pubtype.tpl',
                         new Clip_Form_Handler_Admin_Pubtypes());
    }

    /**
     * Publication fields management.
     */
    public function pubfields()
    {
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Clip::', '::', ACCESS_ADMIN));

        // return the form output
        return FormUtil::newForm('Clip', $this)
               ->execute('clip_admin_pubfields.tpl',
                         new Clip_Form_Handler_Admin_Pubfields());
    }

    /**
     * Relations management.
     */
    public function relations()
    {
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Clip::', '::', ACCESS_ADMIN));

        // return the form output
        return FormUtil::newForm('Clip', $this)
               ->execute('clip_admin_relations.tpl',
                         new Clip_Form_Handler_Admin_Relations());
    }

    /**
     * Export process.
     */
    public function clipexport()
    {
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Clip::', '::', ACCESS_ADMIN));

        // return the form output
        return FormUtil::newForm('Clip', $this)
               ->execute('clip_admin_export.tpl',
                         new Clip_Form_Handler_Admin_Export());
    }

    /**
     * Import process.
     */
    public function clipimport()
    {
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Clip::', '::', ACCESS_ADMIN));

        // return the form output
        return FormUtil::newForm('Clip', $this)
               ->execute('clip_admin_import.tpl',
                         new Clip_Form_Handler_Admin_Import());
    }

    /**
     * Admin publist screen.
     */
    public function publist($args=array())
    {
        //// Validation
        // get the tid first
        $args['tid'] = isset($args['tid']) ? $args['tid'] : FormUtil::getPassedValue('tid');

        if (!Clip_Util::validateTid($args['tid'])) {
            return LogUtil::registerError($this->__f('Error! Invalid publication type ID passed [%s].', DataUtil::formatForDisplay($args['tid'])));
        }

        //// Security check
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Clip::', "{$args['tid']}::", ACCESS_EDIT));

        $pubtype = Clip_Util::getPubType($args['tid']);

        // define the arguments
        $filter = FormUtil::getPassedValue('filter') ? '' : 'core_online:eq:1';
        $type   = FormUtil::getPassedValue('type', 'admin');

        $apiargs = array(
            'tid'           => $args['tid'],
            'filter'        => isset($args['filter']) ? $args['filter'] : $filter,
            'orderby'       => isset($args['orderby']) ? $args['orderby'] : FormUtil::getPassedValue('orderby'),
            'itemsperpage'  => (isset($args['itemsperpage']) && is_numeric($args['itemsperpage']) && $args['itemsperpage'] >= 0) ? (int)$args['itemsperpage'] : abs((int)FormUtil::getPassedValue('itemsperpage')),
            'handleplugins' => isset($args['handleplugins']) ? (bool)$args['handleplugins'] : false,
            'loadworkflow'  => isset($args['loadworkflow']) ? (bool)$args['loadworkflow'] : false,
            'checkperm'     => false,
            'countmode'     => 'both'
        );
        $args = array(
            'startnum'      => (isset($args['startnum']) && is_numeric($args['startnum'])) ? (int)$args['startnum'] : (int)FormUtil::getPassedValue('startnum', 0),
            'page'          => (isset($args['page']) && is_numeric($args['page'])) ? (int)$args['page'] : (int)abs(FormUtil::getPassedValue('page', 1))
        );

        //// Misc values
        if ($apiargs['itemsperpage'] == 0) {
            $apiargs['itemsperpage'] = $pubtype['itemsperpage'] > 0 ? $pubtype['itemsperpage'] : 10;
        }

        if ($args['page'] > 1) {
            $apiargs['startnum'] = ($args['page']-1)*$apiargs['itemsperpage']+1;
        }

        //// Execution
        // uses the API to get the list of publications
        $result = ModUtil::apiFunc('Clip', 'user', 'getall', $apiargs);

        Clip_Util::setArgs('admin_list', $args);

        //// Output
        $this->view->assign('pubtype', $pubtype)
                   ->assign('publist', $result['publist'])
                   ->assign('clipargs',  Clip_Util::getArgs())
                   ->add_core_data();

        // assign the pager values
        $this->view->assign('pager', array('numitems'     => $result['pubcount'],
                                           'itemsperpage' => $apiargs['itemsperpage']));

        if ($this->view->template_exists("clip_{$type}_publist_{$apiargs['tid']}.tpl")) {
            return $this->view->fetch("clip_{$type}_publist_{$apiargs['tid']}.tpl");
        }

        return $this->view->fetch("clip_{$type}_publist.tpl");
    }

    /**
     * History screen.
     */
    public function history($args=array())
    {
        //// Parameters
        $args = array(
            'tid' => isset($args['tid']) ? (int)$args['tid'] : (int)FormUtil::getPassedValue('tid'),
            'pid' => isset($args['pid']) ? (int)$args['pid'] : (int)FormUtil::getPassedValue('pid')
        );

        //// Validation
        if ($args['tid'] <= 0) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'tid'));
        }
        if ($args['pid'] <= 0) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'pid'));
        }

        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Clip::', "{$args['tid']}:{$args['pid']}:", ACCESS_ADMIN));

        $pubtype = Clip_Util::getPubType($args['tid']);
        if (!$pubtype) {
            return LogUtil::registerError($this->__f('Error! No such publication type [%s] found.', $args['tid']));
        }

        $pubtype->mapValue('titlefield', Clip_Util::getTitleField($args['tid']));

        //// Execution
        // get the Doctrine_Table object
        $publist = Doctrine_Core::getTable('Clip_Model_Pubdata'.$args['tid'])
                       ->selectCollection("core_pid = '{$args['pid']}'", 'core_revision DESC');

        for ($i = 0; $i < count($publist); $i++) {
            $publist[$i]->pubPostProcess(array('loadworkflow' => true));
        }

        //// Output
        $this->view->assign('pubtype', $pubtype)
                   ->assign('publist', $publist);

        return $this->view->fetch('clip_admin_history.tpl');
    }

    /**
     * Code generation.
     */
    public function showcode($args=array())
    {
        //// Security check
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Clip::', '::', ACCESS_ADMIN));

        //// Parameters
        $args = array(
            'tid'  => isset($args['tid']) ? (int)$args['tid'] : (int)FormUtil::getPassedValue('tid'),
            'mode' => isset($args['mode']) ? $args['mode'] : FormUtil::getPassedValue('mode'),
            'type' => FormUtil::getPassedValue('type', 'admin')
        );

        //// Validation
        if ($args['tid'] <= 0) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'tid'));
        }
        if (empty($args['mode'])) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'mode'));
        }

        //// Execution
        // get the code depending of the mode
        switch ($args['mode'])
        {
            case 'input':
                $code = Clip_Generator::pubedit($args['tid']);
                break;

            case 'outputfull':
                $code = Clip_Generator::pubdisplay($args['tid'], false);
                break;

            case 'outputlist':
                $path = $this->view->get_template_path('clip_generic_list.tpl');
                $code = file_get_contents($path.'/clip_generic_list.tpl');
                break;

            case 'blockpub':
                $code = Clip_Generator::pubdisplay($args['tid'], false, true);
                break;

            case 'blocklist':
                $path = $this->view->get_template_path('clip_generic_blocklist.tpl');
                $code = file_get_contents($path.'/clip_generic_blocklist.tpl');
                break;
        }

        // code cleaning
        $code = DataUtil::formatForDisplay($code);
        $code = str_replace("\n", '<br />', $code);

        //// Output
        $this->view->assign('code',    $code)
                   ->assign('mode',    $args['mode'])
                   ->assign('pubtype', Clip_Util::getPubType($args['tid']));

        return $this->view->fetch("clip_{$args['type']}_showcode.tpl");
    }

    /**
     * Javascript hierarchical menu of edit links.
     */
    public function editlist()
    {
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Clip::', '::', ACCESS_ADMIN));

        $args = array(
            'menu'       => 1,
            'returntype' => 'admin',
            'orderby'    => 'core_title'
        );

        return ModUtil::func('Clip', 'user', 'editlist', $args);
    }
}
