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
 * Admin Controller.
 */
class PageMaster_Controller_Admin extends Zikula_Controller
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
        if (!SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // return the form output
        return FormUtil::newForm('PageMaster')
               ->execute('pagemaster_admin_modifyconfig.tpl',
                         new PageMaster_Form_Handler_Admin_ModifyConfig());
    }

    /**
     * Publication types list.
     */
    public function pubtypes()
    {
        if (!SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $pubtypes = Doctrine_Core::getTable('PageMaster_Model_Pubtype')->getPubtypes();

        return $this->view->assign('pubtypes', $pubtypes)
                          ->fetch('pagemaster_admin_pubtypes.tpl');
    }

    /**
     * Publication type edition.
     */
    public function pubtype()
    {
        if (!SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // return the form output
        return FormUtil::newForm('PageMaster')
               ->execute('pagemaster_admin_pubtype.tpl',
                         new PageMaster_Form_Handler_Admin_Pubtypes());
    }

    /**
     * Publication fields management.
     */
    public function pubfields()
    {
        if (!SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // return the form output
        return FormUtil::newForm('PageMaster')
               ->execute('pagemaster_admin_pubfields.tpl',
                         new PageMaster_Form_Handler_Admin_Pubfields());
    }

    /**
     * Relations management.
     */
    public function relations()
    {
        if (!SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // return the form output
        return FormUtil::newForm('PageMaster')
               ->execute('pagemaster_admin_relations.tpl',
                         new PageMaster_Form_Handler_Admin_Relations());
    }

    /**
     * Admin publist screen.
     */
    public function publist($args=array())
    {
        //// Parameters
        $args = array(
            'tid'          => isset($args['tid']) ? (int)$args['tid'] : (int)FormUtil::getPassedValue('tid'),
            'startnum'     => isset($args['startnum']) ? (int)$args['startnum'] : (int)FormUtil::getPassedValue('startnum'),
            'itemsperpage' => isset($args['itemsperpage']) ? (int)$args['itemsperpage'] : (int)FormUtil::getPassedValue('itemsperpage'),
            'orderby'      => isset($args['orderby']) ? $args['orderby'] : FormUtil::getPassedValue('orderby')
        );

        //// Validation
        if ($args['tid'] <= 0) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'tid'));
        }

        //// Security check
        if (!SecurityUtil::checkPermission('pagemaster::', "{$args['tid']}::", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        //// Misc values
        $pubtype = PageMaster_Util::getPubType($args['tid']);
        if (!$pubtype) {
            return LogUtil::registerError($this->__f('Error! No such publication type [%s] found.', $args['tid']));
        }

        $tableObj = Doctrine_Core::getTable('PageMaster_Model_Pubdata'.$args['tid']);

        $pubtype = PageMaster_Util::getPubType($args['tid']);

        // set the order
        if (empty($args['orderby'])) {
            if (!empty($pubtype['sortfield1'])) {
                if ($pubtype['sortdesc1'] == 1) {
                    $args['orderby'] = $pubtype['sortfield1'].':DESC ';
                } else {
                    $args['orderby'] = $pubtype['sortfield1'].':ASC ';
                }

                if (!empty($pubtype['sortfield2'])) {
                    if ($pubtype['sortdesc2'] == 1) {
                        $args['orderby'] .= ', '.$pubtype['sortfield2'].':DESC ';
                    } else {
                        $args['orderby'] .= ', '.$pubtype['sortfield2'].':ASC ';
                    }
                }

                if (!empty($pubtype['sortfield3'])) {
                    if ($pubtype['sortdesc3'] == 1) {
                        $args['orderby'] .= ', '.$pubtype['sortfield3'].':DESC ';
                    } else {
                        $args['orderby'] .= ', '.$pubtype['sortfield3'].':ASC ';
                    }
                }
            } else {
                $args['orderby'] = 'core_pid';
            }
        }

        $pubtype->mapValue('titlefield', PageMaster_Util::getTitleField($args['tid']));
        $pubtype->mapValue('orderby', $args['orderby']);

        // replace any occurence of the core_title alias with the field name
        if (strpos('core_title', $args['orderby']) !== false) {
            $args['orderby'] = str_replace('core_title', $pubtype->titlefield, $args['orderby']);
        }
        $args['orderby'] = PageMaster_Util::createOrderBy($args['orderby']);

        //// Execution
        $publist = $tableObj->selectCollection('core_indepot = 0', $args['orderby'], $args['startnum']-1, $args['itemsperpage']);

        if ($publist !== false) {
            $pubcount = (int)$tableObj->selectCount('core_indepot = 0');
            // add the workflow information for each publication
            for ($i = 0; $i < count($publist); $i++) {
                $publist[$i]->pubPostProcess(array('loadworkflow' => true));
            }
        } else {
            $publist  = array();
            $pubcount = 0;
        }

        //// Output
        $this->view->assign('pubtype', $pubtype)
                   ->assign('publist', $publist)
                   ->assign('pager',   array('numitems'     => $pubcount,
                                             'itemsperpage' => $args['itemsperpage']));

        return $this->view->fetch('pagemaster_admin_publist.tpl');
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

        if (!SecurityUtil::checkPermission('pagemaster::', "{$args['tid']}:{$args['pid']}:", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $pubtype = PageMaster_Util::getPubType($args['tid']);
        if (!$pubtype) {
            return LogUtil::registerError($this->__f('Error! No such publication type [%s] found.', $args['tid']));
        }

        $pubtype->mapValue('titlefield', PageMaster_Util::getTitleField($args['tid']));

        //// Execution
        // get the Doctrine_Table object
        $publist = Doctrine_Core::getTable('PageMaster_Model_Pubdata'.$args['tid'])
                       ->selectCollection("core_pid = '{$args['pid']}'", 'core_revision DESC');

        for ($i = 0; $i < count($publist); $i++) {
            $publist[$i]->pubPostProcess(array('loadworkflow' => true));
        }

        //// Output
        $this->view->assign('pubtype', $pubtype)
                   ->assign('publist', $publist);

        return $this->view->fetch('pagemaster_admin_history.tpl');
    }

    /**
     * Code generation.
     */
    public function showcode($args=array())
    {
        //// Security check
        if (!SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

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
                $code = PageMaster_Generator::pubedit($args['tid']);
                break;

            case 'outputfull':
                $code = PageMaster_Generator::pubdisplay($args['tid'], false);
                break;

            case 'outputlist':
                $path = $this->view->get_template_path('pagemaster_generic_list.tpl');
                $code = file_get_contents($path.'/pagemaster_generic_list.tpl');
                break;
        }

        // code cleaning
        $code = DataUtil::formatForDisplay($code);
        $code = str_replace("\n", '<br />', $code);

        //// Output
        $this->view->assign('code',    $code)
                   ->assign('mode',    $mode)
                   ->assign('pubtype', PageMaster_Util::getPubType($args['tid']));

        return $this->view->fetch('pagemaster_admin_showcode.tpl');
    }

    /**
     * Pagesetter import.
     */
    public function importps()
    {
        if (!SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $step = FormUtil::getPassedValue('step');
        if (!empty($step)) {
            ModUtil::apiFunc('PageMaster', 'import', 'importps'.$step);
        }

        // check if there are pubtypes already
        $numpubtypes = Doctrine_Core::getTable('PageMaster_Model_Pubtype')->selectCount();

        // build and return the output
        $this->view->assign('alreadyexists', $numpubtypes > 0 ? true : false)
                   ->add_core_data()
                   ->fetch('pagemaster_admin_importps.tpl');
    }

    /**
     * Javascript hierarchical menu of edit links.
     */
    public function editlist()
    {
        if (!SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $args = array(
            'menu'       => 1,
            'returntype' => 'admin',
            'orderby'    => 'core_title'
        );

        return ModUtil::func('PageMaster', 'user', 'editlist', $args);
    }
}
