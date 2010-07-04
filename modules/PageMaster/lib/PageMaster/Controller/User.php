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

class PageMaster_Controller_User extends Zikula_Controller
{
    /**
     * List of publications
     *
     * @param $args['tid']
     * @author kundi
     */
    public function main($args)
    {
        // get the input parameters
        $tid                = isset($args['tid']) ? $args['tid'] : FormUtil::getPassedValue('tid');
        $startnum           = isset($args['startnum']) ? $args['startnum'] : FormUtil::getPassedValue('startnum');
        $filter             = isset($args['filter']) ? $args['filter'] : FormUtil::getPassedValue('filter');
        $orderby            = isset($args['orderby']) ? $args['orderby'] : FormUtil::getPassedValue('orderby');
        $template           = isset($args['template']) ? $args['template'] : FormUtil::getPassedValue('template');
        $getApprovalState   = isset($args['getApprovalState']) ? (bool)$args['getApprovalState'] : FormUtil::getPassedValue('getApprovalState', false);
        $handlePluginFields = isset($args['handlePluginFields']) ? (bool)$args['handlePluginFields'] : FormUtil::getPassedValue('handlePluginFields', true);
        $rss                = isset($args['rss']) ? (bool)$args['rss'] : (bool)FormUtil::getPassedValue('rss');
        $cachelifetime      = isset($args['cachelifetime']) ? $args['cachelifetime'] : FormUtil::getPassedValue('cachelifetime');

        // essential validation
        if (empty($tid) || !is_numeric($tid)) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'tid'));
        }

        $pubtype = PageMaster_Util::getPubType($tid);
        if (empty($pubtype)) {
            return LogUtil::registerError($this->__f('Error! No such publication type [%s] found.', $tid));
        }

        if (empty($template)) {
            if (!empty($pubtype['filename'])) {
                // template comes from pubtype
                $sec_template = $pubtype['filename'];
                $template     = 'output/publist_'.$pubtype['filename'].'.tpl';
            } else {
                // do not check permission for dynamic template
                $sec_template = '';
                // standart template
                $template     = 'pagemaster_generic_publist.tpl';
            }
        } else {
            // template comes from parameter
            $sec_template = $template;
            $template     = 'output/publist_'.$template.'.tpl';
        }

        // security check as early as possible
        if (!SecurityUtil::checkPermission('pagemaster:list:', "$tid::$sec_template", ACCESS_READ)) {
            return LogUtil::registerPermissionError();
        }

        // check if this view is cached
        if (empty($cachelifetime)) {
            $cachelifetime = $pubtype['cachelifetime'];
        }

        if (!empty($cachelifetime)) {
            $cachetid = true;
            $cacheid  = 'publist'.$tid
                        .'|'.(!empty($filter) ? $filter : 'nofilter')
                        .'|'.(!empty($orderby) ? $orderby : 'noorderby')
                        .'|'.(!empty($startnum) ? $startnum : 'nostartnum');
        } else {
            $cachetid = false;
            $cacheid  = null;
        }

        // buils the output
        $this->view->setCache_Id($cacheid)->setCaching($cachetid);

        if ($cachetid) {
            $this->view->setCache_lifetime($cachelifetime);
            if ($this->view->is_cached($template, $cacheid)) {
                return $this->view->fetch($template, $cacheid);
            }
        }

        $returnurl = System::getCurrentUrl();

        if (isset($args['itemsperpage'])) {
            $itemsperpage = (int)$args['itemsperpage'];
        } elseif (FormUtil::getPassedValue('itemsperpage') != null) {
            $itemsperpage = (int)FormUtil::getPassedValue('itemsperpage');
        } else {
            $itemsperpage = ((int)$pubtype['itemsperpage'] > 0 ? (int)$pubtype['itemsperpage'] : -1 );
        }

        $countmode = ($itemsperpage != 0) ? 'both' : 'no';

        $orderby   = PageMaster_Util::createOrderBy($orderby);

        $pubfields = PageMaster_Util::getPubFields($tid, 'pm_lineno');
        if (empty($pubfields)) {
            LogUtil::registerError($this->__('Error! No publication fields found.'));
        }

        // Uses the API to get the list of publications
        $result = ModUtil::apiFunc('PageMaster', 'user', 'pubList',
                           array('tid'                => $tid,
                                 'pubfields'          => $pubfields,
                                 'pubtype'            => $pubtype,
                                 'countmode'          => $countmode,
                                 'startnum'           => $startnum,
                                 'filter'             => $filter,
                                 'orderby'            => $orderby,
                                 'itemsperpage'       => $itemsperpage,
                                 'checkPerm'          => false, // already checked
                                 'handlePluginFields' => $handlePluginFields,
                                 'getApprovalState'   => $getApprovalState));

        // Assign the data to the output
        $this->view->assign('tid',       $tid)
                       ->assign('pubtype',   $pubtype)
                       ->assign('publist',   $result['publist'])
                       ->assign('returnurl', $returnurl)
                       ->assign('core_titlefield', PageMaster_Util::findTitleField($pubfields));

        // Assign the pager values if needed
        if ($itemsperpage != 0) {
            $this->view->assign('pager', array('numitems'     => $result['pubcount'],
                                                   'itemsperpage' => $itemsperpage));
        }

        // Check if template is available
        if ($template != 'pagemaster_generic_publist.tpl' && !$this->view->template_exists($template)) {
            $alert = SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN) && ModUtil::getVar('PageMaster', 'devmode', false);
            if ($alert) {
                LogUtil::registerStatus($this->__f('Notice: Template [%s] not found.', $template));
            }
            $template = 'pagemaster_generic_publist.tpl';
        }

        if ($rss) {
            echo $this->view->display($template, $cacheid);
            System::shutdown();
        }

        return $this->view->fetch($template, $cacheid);
    }

    /**
     * View a publication
     *
     * @param $args['tid']
     * @param $args['pid']
     * @param $args['id'] (optional)
     * @param $args['template'] (optional)
     *
     * @return publication view output
     */
    public function viewpub($args)
    {
        // get the input parameters
        $tid      = isset($args['tid']) ? $args['tid'] : FormUtil::getPassedValue('tid');
        $pid      = isset($args['pid']) ? $args['pid'] : FormUtil::getPassedValue('pid');
        $id       = isset($args['id']) ? $args['id'] : FormUtil::getPassedValue('id');
        $template = isset($args['template']) ? $args['template'] : FormUtil::getPassedValue('template');
        $cachelt  = isset($args['cachelifetime']) ? $args['cachelifetime'] : FormUtil::getPassedValue('cachelifetime');

        // essential validation
        if (empty($tid) || !is_numeric($tid)) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'tid'));
        }
        if ((empty($pid) || !is_numeric($pid)) && (empty($id) || !is_numeric($id))) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'id | pid'));
        }

        $pubtype = PageMaster_Util::getPubType($tid);
        if (empty($pubtype)) {
            return LogUtil::registerError($this->__f('Error! No such publication type [%s] found.', $tid));
        }

        // get the pid if it was not passed
        if (empty($pid)) {
            $pid = ModUtil::apiFunc('PageMaster', 'user', 'getPid',
                                    array('tid' => $tid,
                                          'id'  => $id));
        }

        // determine the template to use
        if (empty($template)) {
            if (!empty($pubtype['filename'])) {
                // template for the security check
                $sec_template = $pubtype['filename'];
                // template comes from pubtype
                $template     = 'output/viewpub_'.$pubtype['filename'].'.tpl';
            } else {
                // do not check permission for dynamic template
                $sec_template = '';
                // standart template
                $template     = 'var:viewpub_template_code';
            }
        } else {
            // template for the security check
            $sec_template = $template;
            // template comes from parameter
            $template     = 'output/viewpub_'.$template.'.tpl';

            // workaround for related plain templates
            if (in_array($sec_template, array('pending'))) {
                $simpletemplate = "output/viewpub_{$pubtype['filename']}_{$sec_template}.tpl";
            }
        }

        // security check as early as possible
        if (!SecurityUtil::checkPermission('pagemaster:full:', "$tid:$pid:$sec_template", ACCESS_READ)) {
            return LogUtil::registerPermissionError();
        }

        // check if this view is cached
        if (empty($cachelt)) {
            $cachelt = $pubtype['cachelifetime'];
        }

        if (!empty($cachelt) && !SecurityUtil::checkPermission('pagemaster:input:', "$tid:$pid:", ACCESS_ADMIN)) {
            // second clause allow developer to add an edit button on the "viewpub" template
            $cachetid = true;
            $cacheid = 'viewpub'.$tid.'|'.$pid;
        } else {
            $cachetid = false;
            $cacheid  = null;
        }

        // build the output
        $this->view->setCaching($cachetid)->setCache_Id($cacheid)->add_core_data();

        if ($cachetid) {
            $this->view->setCache_lifetime($cachelt);
            if ($this->view->is_cached($template, $cacheid)) {
                return $this->view->fetch($template, $cacheid);
            }
        }

        // fetch plain templates
        if (isset($simpletemplate)) {
            if (!$this->view->template_exists($simpletemplate)) {
                $simpletemplate = "pagemaster_generic_{$sec_template}.tpl";
                if (!$this->view->template_exists($simpletemplate)) {
                    $simpletemplate = '';
                }
            }
            if ($simpletemplate != '') {
                $this->view->assign('pubtype', $pubtype);
                return $this->view->fetch($simpletemplate, $cacheid);
            }
        }

        // not cached or cache disabled, then get the Pub from the DB
        $pubfields = PageMaster_Util::getPubFields($tid);
        if (empty($pubfields)) {
            LogUtil::registerError($this->__('Error! No publication fields found.'));
        }

        $pubdata = ModUtil::apiFunc('PageMaster', 'user', 'getPub',
                            array('tid'                => $tid,
                                  'id'                 => $id,
                                  'pid'                => $pid,
                                  'pubtype'            => $pubtype,
                                  'pubfields'          => $pubfields,
                                  'checkPerm'          => false, //check later, together with template
                                  'getApprovalState'   => true,
                                  'handlePluginFields' => true));

        if (!$pubdata) {
            return LogUtil::registerError($this->__f('No such publication [%s - %s, %s] found.', array($tid, $pid, $id)));
        }

        $core_title            = PageMaster_Util::findTitleField($pubfields);
        $pubtype['titlefield'] = $core_title;

        // assign each field of the pubdata to the output
        $this->view->assign($pubdata);

        // process the output
        $this->view->assign('pubtype',            $pubtype)
                       ->assign('core_tid',           $tid)
                       ->assign('core_approvalstate', $pubdata['__WORKFLOW__']['state'])
                       ->assign('core_titlefield',    $core_title)
                       ->assign('core_title',         $pubdata[$core_title])
                       ->assign('core_uniqueid',      $tid.'-'.$pubdata['core_pid'])
                       ->assign('core_creator',       ($pubdata['core_author'] == UserUtil::getVar('uid')) ? true : false);

        // Check if template is available
        if ($template != 'var:viewpub_template_code' && !$this->view->template_exists($template)) {
            $alert = SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN) && ModUtil::getVar('PageMaster', 'devmode', false);
            if ($alert) {
                LogUtil::registerStatus($this->__f('Notice: Template [%s] not found.', $template));
            }
            $template = 'var:viewpub_template_code';
        }

        if ($template == 'var:viewpub_template_code') {
            $this->view->setCompile_check(true);
            $this->view->assign('viewpub_template_code', PageMaster_Generator::viewpub($tid, $pubdata));
        }

        return $this->view->fetch($template, $cacheid);
    }

    /**
     * Edit/Create a publication
     *
     * @param $args['tid']
     * @param $args['id']
     */
    public function pubedit()
    {
        // get the input parameters
        $tid = FormUtil::getPassedValue('tid');
        $id  = FormUtil::getPassedValue('id');
        $pid = FormUtil::getPassedValue('pid');

        // essential validation
        if (empty($tid) || !is_numeric($tid)) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'tid'));
        }

        $pubtype = PageMaster_Util::getPubType($tid);
        if (empty($pubtype)) {
            return LogUtil::registerError($this->__f('Error! No such publication type [%s] found.', $tid));
        }

        $pubfields = PageMaster_Util::getPubFields($tid, 'pm_lineno');
        if (empty($pubfields)) {
            LogUtil::registerError($this->__('Error! No publication fields found.'));
        }

        // no security check needed - the security check will be done by the handler class.
        // see the init-part of the handler class for details.
        $formHandler = new PageMaster_Form_Handler_UserEditpub();

        if (empty($id) && !empty($pid)) {
            $id = ModUtil::apiFunc('PageMaster', 'user', 'getId',
            array('tid' => $tid,
                                 'pid' => $pid));
            if (empty($id)) {
                return LogUtil::registerError($this->__f('Error! No such publication [%s - %s] found.', array($tid, $pid)));
            }
        }

        // cast values to ensure the type
        $id  = (int)$id;
        $pid = (int)$pid;

        $formHandler->tid       = $tid;
        $formHandler->id        = $id;
        $formHandler->pubtype   = $pubtype;
        $formHandler->pubfields = $pubfields;
        $formHandler->tablename = 'pagemaster_pubdata'.$tid;

        // get actual state for selecting form Template
        $stepname = 'initial';

        if (!empty($id)) {
            $obj = array('id' => $id);
            WorkflowUtil::getWorkflowForObject($obj, $formHandler->tablename, 'id', 'PageMaster');
            $stepname = $obj['__WORKFLOW__']['state'];
        }

        // create the output object
        $render = FormUtil::newForm('PageMaster');
        $render->add_core_data();

        $render->assign('pubtype', $pubtype);

        // resolve the template to use
        $alert = SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN) && ModUtil::getVar('PageMaster', 'devmode', false);

        // individual step
        $template_step = 'input/pubedit_'.$pubtype['formname'].'_'.$stepname.'.tpl';

        if (!empty($stepname) && $render->template_exists($template_step)) {
            return $render->execute($template_step, $formHandler);
        } elseif ($alert) {
            LogUtil::registerStatus($this->__f('Notice: Template [%s] not found.', $template_step));
        }

        // generic edit
        $template_all = 'input/pubedit_'.$pubtype['formname'].'_all.tpl';

        if ($render->template_exists($template_all)) {
            return $render->execute($template_all, $formHandler);
        } elseif ($alert) {
            LogUtil::registerStatus($this->__f('Notice: Template [%s] not found.', $template_all));
        }

        // autogenerated edit template
        $render->force_compile = true;
        $render->assign('editpub_template_code', PageMaster_Generator::editpub($tid));

        return $render->execute('var:editpub_template_code', $formHandler);
    }

    /**
     * Executes a Workflow command over a direct URL Request
     *
     * @param $args['tid']
     * @param $args['id']
     * @param $args['goto'] redirect to after execution
     * @param $args['schema'] optional workflow shema
     * @param $args['commandName'] commandName
     */
    public function executecommand()
    {
        // get the input parameters
        $tid         = FormUtil::getPassedValue('tid');
        $id          = FormUtil::getPassedValue('id');
        $commandName = FormUtil::getPassedValue('commandName');
        $schema      = FormUtil::getPassedValue('schema');
        $goto        = FormUtil::getPassedValue('goto');

        // essential validation
        if (empty($tid) || !is_numeric($tid)) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'tid'));
        }

        if (!isset($id) || empty($id) || !is_numeric($id)) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'id'));
        }

        if (empty($commandName)) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'commandName'));
        }

        if (empty($schema)) {
            $pubtype = PageMaster_Util::getPubType($tid);
            $schema  = str_replace('.xml', '', $pubtype['workflow']);
        }

        $tablename = 'pagemaster_pubdata'.$tid;

        $pub = DBUtil::selectObjectByID($tablename, $id, 'id');
        if (!$pub) {
            return LogUtil::registerError($this->__f('Error! No such publication [%s] found.', $id));
        }

        WorkflowUtil::executeAction($schema, $pub, $commandName, $tablename, 'PageMaster');

        if (!empty($goto)) {
            switch ($goto)
            {
                case 'edit':
                    return System::redirect(ModUtil::url('PageMaster', 'user', 'pubedit',
                                            array('tid' => $tid,
                                                  'id'  => $pub['id'])));
                case 'stepmode':
                    return System::redirect(ModUtil::url('PageMaster', 'user', 'pubedit',
                                            array('tid'  => $tid,
                                                  'id'   => $pub['id'],
                                                  'goto' => 'stepmode')));
                default:
                    return System::redirect($goto);
            }
        }

        return System::redirect(ModUtil::url('PageMaster', 'user', 'viewpub',
                                array('tid' => $tid,
                                      'id'  => $pub['id'])));
    }

    /**
     * Generate a javascript hierarchical menu of edit links
     *
     * @author rgasch
     * @param  $args['tid']
     * @param  $args['pid'] (optional)
     * @param  $args['edit'] (optional)
     * @param  $args['menu'] (optional)
     * @param  $args['orderby'] (optional)
     * @param  $args['returntype'] (optional)
     * @param  $args['source'] (optional)
     * @return publication menu and/or edit mask
     */
    public function pubeditlist($args=array())
    {
        $tid        = isset($args['tid']) ? $args['tid'] : FormUtil::getPassedValue('tid');
        $pid        = isset($args['pid']) ? $args['pid'] : FormUtil::getPassedValue('pid');
        $edit       = isset($args['edit']) ? $args['edit'] : FormUtil::getPassedValue('edit', 1);
        $menu       = isset($args['menu']) ? $args['menu'] : FormUtil::getPassedValue('menu', 1);
        $returntype = isset($args['returntype']) ? $args['returntype'] : FormUtil::getPassedValue('returntype', 'user');
        $source     = isset($args['source']) ? $args['source'] : FormUtil::getPassedValue('source', 'module');

        $pubData = ModUtil::apiFunc('PageMaster', 'user', 'pubeditlist', $args);

        // create the output object
        $this->view->assign('allTypes',   $pubData['allTypes'])
                       ->assign('publist',    $pubData['pubList'])
                       ->assign('tid',        $tid)
                       ->assign('pid',        $pid)
                       ->assign('edit',       $edit)
                       ->assign('menu',       $menu)
                       ->assign('returntype', $returntype)
                       ->assign('source',     $source);

        return $this->view->fetch('pagemaster_user_pubeditlist.tpl');
    }
}
