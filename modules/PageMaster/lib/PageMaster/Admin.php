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

class PageMaster_Admin extends Zikula_Controller
{
    /**
     * Main admin screen
     */
    public function main()
    {
        return $this->pubtypes();
    }

    /**
     * Module configuration
     */
    public function modifyconfig()
    {
        if (!SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // build the output
        $render = FormUtil::newForm('PageMaster');

        return $render->execute('pagemaster_admin_modifyconfig.tpl', new PageMaster_Form_Handler_AdminModifyConfig());
    }

    /**
     * Publication types list
     */
    public function pubtypes()
    {
        if (!SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $pubtypes = DBUtil::selectObjectArray('pagemaster_pubtypes', null, 'title');
        $this->renderer->assign('pubtypes', $pubtypes);

        return $this->renderer->fetch('pagemaster_admin_pubtypes.tpl');
    }

    /**
     * Publication type edition
     * @author gf
     */
    public function pubtype()
    {
        if (!SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // build the output
        $render = FormUtil::newForm('PageMaster');

        return $render->execute('pagemaster_admin_pubtype.tpl', new PageMaster_Form_Handler_AdminPubtypes());
    }

    /**
     * Publication fields management
     */
    public function pubfields()
    {
        if (!SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // build the output
        $render = FormUtil::newForm('PageMaster');

        return $render->execute('pagemaster_admin_pubfields.tpl', new PageMaster_Form_Handler_AdminPubfields());
    }


    /**
     * DB pubtype table update method
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
     * Admin publist screen
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

        // db table check
        $tablename = 'pagemaster_pubdata'.$tid;
        if (!in_array(DBUtil::getLimitedTablename($tablename), DBUtil::metaTables())) {
            return LogUtil::registerError($this->__('Error! The table of this publication type seems not to exist. Please, update the DB Tables at the bottom of this form.'),
                                          null,
                                          ModUtil::url('PageMaster', 'admin', 'pubtype', array('tid' => $tid), null, 'pn-maincontent'));
        }

        $pubtype = PageMaster_Util::getPubType($tid);

        // set the order
        $old_orderby = $orderby;
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

        $core_title  = PageMaster_Util::getTitleField($tid);
        if (substr($orderby, 0, 10) == 'core_title') {
            $orderby = str_replace('core_title', $core_title, $orderby);
        }
        $orderby = PageMaster_Util::createOrderBy($orderby);

        // query the list
        $publist  = DBUtil::selectObjectArray($tablename, 'pm_indepot = 0', $orderby, $startnum-1, $itemsperpage);

        if ($publist !== false) {
            $pubcount = (int)DBUtil::selectObjectCount($tablename, 'pm_indepot = 0');
            // add the workflow information for each publication
            foreach (array_keys($publist) as $key) {
                WorkflowUtil::getWorkflowForObject($publist[$key], $tablename, 'id', 'PageMaster');
            }
        } else {
            $publist  = array();
            $pubcount = 0;
        }

        // build the output
        $this->renderer->assign('core_tid',   $tid)
                       ->assign('core_title', $core_title)
                       ->assign('publist',    $publist)
                       ->assign('orderby',    $old_orderby)
                       ->assign('pager',      array('numitems'     => $pubcount,
                                                    'itemsperpage' => $itemsperpage));

        return $this->renderer->fetch('pagemaster_admin_publist.tpl');
    }

    /**
     * History screen
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

        $tablename = 'pagemaster_pubdata'.$tid;

        $publist = DBUtil::selectObjectArray($tablename, "pm_pid = '$pid'", 'pm_revision desc');

        foreach (array_keys($publist) as $key) {
            WorkflowUtil::getWorkflowForObject($publist[$key], $tablename, 'id', 'PageMaster');
        }

        $core_title = PageMaster_Util::getTitleField($tid);

        // build the output
        $this->renderer->assign('core_tid',   $tid)
                       ->assign('core_title', $core_title)
                       ->assign('publist',    $publist);

        return $this->renderer->fetch('pagemaster_admin_history.tpl');
    }

    /**
     * Code generation
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
                $code = PageMaster_Generator::editpub($tid);
                break;

            case 'outputfull':
                $tablename = 'pagemaster_pubdata'.$tid;
                $id = DBUtil::selectFieldMax($tablename, 'id', 'MAX');
                if ($id <= 0) {
                    return LogUtil::registerError($this->__('There has to be at least one publication to generate the template code.'), null,
                    System::serverGetVar('HTTP_REFERER', ModUtil::url('PageMaster', 'admin', 'main')));
                }
                $pubdata = ModUtil::apiFunc('PageMaster', 'user', 'getPub',
                                    array('tid' => $tid,
                                          'id'  => $id,
                                          'handlePluginFields' => true));

                $code = PageMaster_Generator::viewpub($tid, $pubdata);
                break;

            case 'outputlist':
                $path = $this->renderer->get_template_path('pagemaster_generic_publist.tpl');
                $code = file_get_contents($path.'/pagemaster_generic_publist.tpl');
                break;
        }

        // code cleaning
        $code = DataUtil::formatForDisplay($code);
        $code = str_replace("\n", '<br />', $code);

        $this->renderer->assign('code',    $code)
                       ->assign('mode',    $mode)
                       ->assign('pubtype', PageMaster_Util::getPubType($tid));

        return $this->renderer->fetch('pagemaster_admin_showcode.tpl');
    }

    /**
     * Pagesetter import
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
        $numpubtypes = DBUtil::selectObjectCount('pagemaster_pubtypes');

        // build the output
        $this->renderer->add_core_data();

        $this->renderer->assign('alreadyexists', $numpubtypes > 0 ? true : false);

        return $this->renderer->fetch('pagemaster_admin_importps.tpl');
    }

    /**
     * Generate a javascript hierarchical menu of edit links
     */
    public function pubeditlist($args=array())
    {
        if (!SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $args = array(
            'menu'       => 1,
            'returntype' => 'admin'
        );

        return ModUtil::func('PageMaster', 'user', 'pubeditlist', $args);
    }
}
