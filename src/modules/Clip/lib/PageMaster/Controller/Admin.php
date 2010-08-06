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
     * DB pubtype table update method.
     */
    public function dbupdate($args=array())
    {
        if (!SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // get the input parameter
        $tid  = isset($args['tid']) ? $args['tid'] : FormUtil::getPassedValue('tid');
        $rurl = System::serverGetVar('HTTP_REFERER', ModUtil::url('PageMaster', 'admin', 'main'));

        if (!PageMaster_Util::getPubType($tid)) {
            return LogUtil::registerError($this->__('Error! No such publication type found.'), null, $rurl);
        }

        $result = ModUtil::apiFunc('PageMaster', 'admin', 'updatetabledef', array('tid' => $tid));

        if (!$result) {
            return LogUtil::registerError($this->__('Error! Update attempt failed.'), null, $rurl);
        }

        return LogUtil::registerStatus($this->__('Done! Database table updated.'), $rurl);
    }

    /**
     * Admin publist screen.
     */
    public function publist($args=array())
    {
        // get the input parameters
        $tid          = isset($args['tid']) ? $args['tid'] : FormUtil::getPassedValue('tid');
        $startnum     = isset($args['startnum']) ? $args['startnum'] : FormUtil::getPassedValue('startnum');
        $itemsperpage = isset($args['itemsperpage']) ? $args['itemsperpage'] : FormUtil::getPassedValue('itemsperpage', 50);
        $orderby      = isset($args['orderby']) ? $args['orderby'] : FormUtil::getPassedValue('orderby');

        // validate the essential parameters
        if (empty($tid) || !is_numeric($tid)) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'tid'));
        }

        if (!SecurityUtil::checkPermission('pagemaster::', $tid.'::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        $pubtype = PageMaster_Util::getPubType($tid);
        if (!$pubtype) {
            return LogUtil::registerError($this->__f('Error! No such publication type [%s] found.', $tid));
        }

        // get the Doctrine_Table object
        $tableObj = Doctrine_Core::getTable('PageMaster_Model_Pubdata'.$tid);

        // db table check
        // FIXME: May remove this?
        $tablename = 'pagemaster_pubdata'.$tid;
        if (!in_array(DBUtil::getLimitedTablename($tablename), DBUtil::metaTables())) {
            return LogUtil::registerError($this->__f("Error! The table of this publication type [%s] seems not to exist. Please, click the respective 'DB update' link to create it.", $pubtype['title']),
                                          null,
                                          ModUtil::url('PageMaster', 'admin', 'pubtypes'));
        }

        $pubtype = PageMaster_Util::getPubType($tid);
        $pubtype->mapValue('titlefield', PageMaster_Util::getTitleField($tid));
        $pubtype->mapValue('orderby', $orderby);

        // set the order
        if (!isset($orderby) || empty($orderby)) {
            if (!empty($pubtype['sortfield1'])) {
                if ($pubtype['sortdesc1'] == 1) {
                    $orderby = $pubtype['sortfield1'].':DESC ';
                } else {
                    $orderby = $pubtype['sortfield1'].':ASC ';
                }

                if (!empty($pubtype['sortfield2'])) {
                    if ($pubtype['sortdesc2'] == 1) {
                        $orderby .= ', '.$pubtype['sortfield2'].':DESC ';
                    } else {
                        $orderby .= ', '.$pubtype['sortfield2'].':ASC ';
                    }
                }

                if (!empty($pubtype['sortfield3'])) {
                    if ($pubtype['sortdesc3'] == 1) {
                        $orderby .= ', '.$pubtype['sortfield3'].':DESC ';
                    } else {
                        $orderby .= ', '.$pubtype['sortfield3'].':ASC ';
                    }
                }
            } else {
                $orderby = 'pm_pid';
            }
        }

        // replace any occurence of the core_title alias with the field name
        if (strpos('core_title', $orderby) !== false) {
            $orderby = str_replace('core_title', $pubtype->titlefield, $orderby);
        }
        $orderby = PageMaster_Util::createOrderBy($orderby);

        // query the list
        $publist = $tableObj->selectCollection('core_indepot = 0', $orderby, $startnum-1, $itemsperpage);

        if ($publist !== false) {
            $pubcount = (int)$tableObj->selectCount('core_indepot = 0');
            // add the workflow information for each publication
            for ($i = 0; $i < count($publist); $i++) {
                Zikula_Workflow_Util::getWorkflowForObject($publist[$i], $tablename, 'id', 'PageMaster');
            }
        } else {
            $publist  = array();
            $pubcount = 0;
        }

        // build the output
        $this->view->assign('pubtype', $pubtype)
                   ->assign('publist', $publist)
                   ->assign('pager',   array('numitems'     => $pubcount,
                                             'itemsperpage' => $itemsperpage));

        return $this->view->fetch('pagemaster_admin_publist.tpl');
    }

    /**
     * History screen.
     */
    public function history()
    {
        // get the input parameters
        $pid = FormUtil::getPassedValue('pid');
        $tid = FormUtil::getPassedValue('tid');

        if (empty($tid) || !is_numeric($tid)) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'tid'));
        }
        if (empty($pid) || !is_numeric($pid)) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'pid'));
        }

        if (!SecurityUtil::checkPermission('pagemaster::', "$tid:$pid:", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // get the Doctrine_Table object
        $publist = Doctrine_Core::getTable('PageMaster_Model_Pubdata'.$tid)
                       ->selectCollection("core_pid = '$pid'", 'core_revision DESC');

        $tablename = 'pagemaster_pubdata'.$tid;
        for ($i = 0; $i < count($publist); $i++) {
            Zikula_Workflow_Util::getWorkflowForObject($publist[$i], $tablename, 'id', 'PageMaster');
        }

        $pubtype = PageMaster_Util::getPubType($tid);
        $pubtype->mapValue('titlefield', PageMaster_Util::getTitleField($tid));

        // build the output
        $this->view->assign('pubtype', $pubtype)
                   ->assign('publist', $publist);

        return $this->view->fetch('pagemaster_admin_history.tpl');
    }

    /**
     * Code generation.
     */
    public function showcode()
    {
        if (!SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // get the input parameters
        $tid  = (int)FormUtil::getPassedValue('tid');
        $mode = FormUtil::getPassedValue('mode');

        // validate the essential parameters
        if (empty($tid) || !is_numeric($tid)) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'tid'));
        }
        if (empty($mode)) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'mode'));
        }

        // get the code depending of the mode
        switch ($mode)
        {
            case 'input':
                $code = PageMaster_Generator::pubedit($tid);
                break;

            case 'outputfull':
                $code = PageMaster_Generator::pubdisplay($tid, false);
                break;

            case 'outputlist':
                $path = $this->view->get_template_path('pagemaster_generic_list.tpl');
                $code = file_get_contents($path.'/pagemaster_generic_list.tpl');
                break;
        }

        // code cleaning
        $code = DataUtil::formatForDisplay($code);
        $code = str_replace("\n", '<br />', $code);

        $this->view->assign('code',    $code)
                   ->assign('mode',    $mode)
                   ->assign('pubtype', PageMaster_Util::getPubType($tid));

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
    public function editlist($args=array())
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
