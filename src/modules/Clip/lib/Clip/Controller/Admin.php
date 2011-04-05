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
     * Main admin screen.
     */
    public function main()
    {
        return $this->pubtypes();
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
    public function pubtypes()
    {
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Clip::', '::', ACCESS_ADMIN));

        $pubtypes = Doctrine_Core::getTable('Clip_Model_Pubtype')->getPubtypes();

        return $this->view->assign('pubtypes', $pubtypes)
                          ->fetch('clip_admin_pubtypes.tpl');
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
        //// Parameters
        $args = array(
            'tid'           => isset($args['tid']) ? (int)$args['tid'] : (int)FormUtil::getPassedValue('tid'),
            'startnum'      => isset($args['startnum']) ? (int)$args['startnum'] : (int)FormUtil::getPassedValue('startnum'),
            'itemsperpage'  => isset($args['itemsperpage']) ? (int)$args['itemsperpage'] : (int)FormUtil::getPassedValue('itemsperpage'),
            'orderby'       => isset($args['orderby']) ? $args['orderby'] : FormUtil::getPassedValue('orderby'),
            'handleplugins' => true,  // API default
            'loadworkflow'  => true,  // API default
            'checkperm'     => false, // API default
            'countmode'     => 'both' // API default
        );

        //// Validation
        if ($args['tid'] <= 0) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'tid'));
        }

        //// Security check
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Clip::', "{$args['tid']}::", ACCESS_EDIT));

        //// Misc values
        $pubtype = Clip_Util::getPubType($args['tid']);
        if (!$pubtype) {
            return LogUtil::registerError($this->__f('Error! No such publication type [%s] found.', $args['tid']));
        }

        if (!$args['itemsperpage']) {
            $args['itemsperpage'] = $pubtype['itemsperpage'] > 0 ? $pubtype['itemsperpage'] : $this->getVar('maxperpage', 100);
        }

        System::queryStringSetVar('filter', 'core_online:eq:1');

        //// Execution
        // uses the API to get the list of publications
        $result = ModUtil::apiFunc('Clip', 'user', 'getall', $args);

        //// Output
        $this->view->assign('pubtype', $pubtype)
                   ->assign('publist', $result['publist'])
                   ->assign('pager',   array('numitems'     => $result['pubcount'],
                                             'itemsperpage' => $args['itemsperpage']));

        return $this->view->fetch('clip_admin_publist.tpl');
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
            'mode' => isset($args['mode']) ? $args['mode'] : FormUtil::getPassedValue('mode')
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

        return $this->view->fetch('clip_admin_showcode.tpl');
    }

    /**
     * Pagesetter import.
     */
    public function importps()
    {
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Clip::', '::', ACCESS_ADMIN));

        $step = FormUtil::getPassedValue('step');
        if (!empty($step)) {
            ModUtil::apiFunc('Clip', 'import', 'importps'.$step);
        }

        // check if there are pubtypes already
        $numpubtypes = Doctrine_Core::getTable('Clip_Model_Pubtype')->selectCount();

        // build and return the output
        $this->view->assign('alreadyexists', $numpubtypes > 0 ? true : false)
                   ->add_core_data();

        return $this->view->fetch('clip_admin_importps.tpl');
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
