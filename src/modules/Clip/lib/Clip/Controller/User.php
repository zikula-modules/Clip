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
 * User Controller.
 */
class Clip_Controller_User extends Zikula_Controller
{
    /**
     * Main user function.
     */
    public function main($args)
    {
        return $this->view($args);
    }

    /**
     * Publications list.
     *
     * @param integer $args['tid']           ID of the publication type.
     * @param string  $args['template']      Custom publication type template to use.
     * @param string  $args['filter']        Filter string.
     * @param string  $args['orderby']       OrderBy string.
     * @param integer $args['startnum']      Offset to start from.
     * @param integer $args['itemsperpage']  Number of items to retrieve.
     * @param boolean $args['handleplugins'] Whether to parse the plugin fields.
     * @param boolean $args['loadworkflow']  Whether to add the workflow information.
     * @param integer $args['cachelifetime'] Cache lifetime (empty for default config).
     *
     * @return string Publication list output.
     */
    public function view($args)
    {
        //// Validation
        // get the tid first
        $args['tid'] = isset($args['tid']) ? $args['tid'] : FormUtil::getPassedValue('tid');

        if (!Clip_Util::validateTid($args['tid'])) {
            return LogUtil::registerError($this->__f('Error! Invalid publication type ID passed [%s].', DataUtil::formatForDisplay($args['tid'])));
        }

        $pubtype = Clip_Util::getPubType($args['tid']);

        //// Parameters
        // old parameters (will be removed on Clip 1.0)
        $args['handlePluginF'] = isset($args['handlePluginFields']) ? (bool)$args['handlePluginFields'] : FormUtil::getPassedValue('handlePluginFields', true);
        $args['getApprovalS']  = isset($args['getApprovalState']) ? (bool)$args['getApprovalState'] : FormUtil::getPassedValue('getApprovalState', false);
        // define the arguments
        $apiargs = array(
            'tid'           => $args['tid'],
            'filter'        => isset($args['filter']) ? $args['filter'] : null,
            'orderby'       => isset($args['orderby']) ? $args['orderby'] : FormUtil::getPassedValue('orderby'),
            'itemsperpage'  => (isset($args['itemsperpage']) && is_numeric($args['itemsperpage']) && $args['itemsperpage'] >= 0) ? (int)$args['itemsperpage'] : (int)$pubtype['itemsperpage'],
            'handleplugins' => isset($args['handleplugins']) ? (bool)$args['handleplugins'] : $args['handlePluginF'],
            'loadworkflow'  => isset($args['loadworkflow']) ? (bool)$args['loadworkflow'] : $args['getApprovalS'],
            'checkperm'     => false,
            'countmode'     => 'both'
        );
        $args = array(
            'template'      => isset($args['template']) ? $args['template'] : FormUtil::getPassedValue('template'),
            'startnum'      => (isset($args['startnum']) && is_numeric($args['startnum'])) ? (int)$args['startnum'] : (int)FormUtil::getPassedValue('startnum', 0),
            'page'          => (isset($args['page']) && is_numeric($args['page'])) ? (int)$args['page'] : (int)abs(FormUtil::getPassedValue('page', 1)),
            'cachelifetime' => isset($args['cachelifetime']) ? $args['cachelifetime'] : FormUtil::getPassedValue('cachelifetime', $pubtype['cachelifetime']),
        );

        if ($apiargs['itemsperpage'] <= 0) {
            $apiargs['itemsperpage'] = $pubtype['itemsperpage'] > 0 ? $pubtype['itemsperpage'] : $this->getVar('maxperpage', 100);
        }

        if ($args['page'] > 1) {
            $apiargs['startnum'] = ($args['page']-1)*$apiargs['itemsperpage']+1;
        }

        if (empty($args['template'])) {
            // template comes from pubtype
            $args['templateid'] = $pubtype['outputset'];
            $args['template']   = $pubtype['outputset'].'/list.tpl';
        } else {
            // template comes from parameter
            $args['template']   = DataUtil::formatForOS($args['template']);
            $args['templateid'] = $pubtype['outputset']."_{$args['template']}";
            $args['template']   = $pubtype['outputset']."/list_{$args['template']}.tpl";
        }

        //// Security check
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Clip:list:', "{$apiargs['tid']}::{$args['templateid']}", ACCESS_READ));

        //// Output setup
        // check if this view is cached
        if (!empty($args['cachelifetime'])) {
            $this->view->setCache_lifetime($args['cachelifetime']);
            $cachetid = true;
            $cacheid  = 'view'.$apiargs['tid'].'|'.$args['templateid']
                        .'|'.(!empty($apiargs['filter']) ? $apiargs['filter'] : 'nofilter')
                        .'|'.(!empty($apiargs['orderby']) ? $apiargs['orderby'] : 'noorderby')
                        .'|'.(!empty($apiargs['itemsperpage']) ? $apiargs['itemsperpage'] : 'nolimit')
                        .'|'.(!empty($apiargs['startnum']) ? $apiargs['startnum'] : 'nostartnum');
        } else {
            $cachetid = false;
            $cacheid  = null;
        }

        // set the output info
        $this->view->setCache_Id($cacheid)
                   ->setCaching($cachetid)
                   ->add_core_data();

        if ($cachetid && $this->view->is_cached($args['template'], $cacheid)) {
            return $this->view->fetch($args['template'], $cacheid);
        }

        //// API call
        // uses the API to get the list of publications
        $result = ModUtil::apiFunc('Clip', 'user', 'getall', $apiargs);

        Clip_Util::setArgs('user.view', $args);

        //// Build the output
        // assign the data to the output
        $this->view->assign('pubtype',   $pubtype)
                   ->assign('publist',   $result['publist'])
                   ->assign('clipargs',  Clip_Util::getArgs())
                   ->assign('returnurl', System::getCurrentUrl());

        // assign the pager values
        $this->view->assign('pager', array('numitems'     => $result['pubcount'],
                                           'itemsperpage' => $apiargs['itemsperpage']));

        // check if template is available
        if (!$this->view->template_exists($args['template'])) {
            $alert = SecurityUtil::checkPermission('clip::', '::', ACCESS_ADMIN) && ModUtil::getVar('Clip', 'devmode', false);
            if ($alert) {
                LogUtil::registerStatus($this->__f('Notice: Template [%s] not found.', $args['template']));
            }

            // return an error/void if a block template does not exists
            if (strpos($args['templateid'], $pubtype['outputset'].'_block_') === 0) {
                return $alert ? LogUtil::registerError($this->__f('Notice: Template [%s] not found.', $args['template'])) : '';
            }

            $this->view->assign('clip_generic_tpl', true);

            $args['template'] = 'clip_generic_list.tpl';
        }

        return $this->view->fetch($args['template'], $cacheid);
    }

    /**
     * Display a publication.
     *
     * @param integer $args['tid']           ID of the publication type.
     * @param integer $args['pid']           ID of the publication.
     * @param integer $args['id']            ID of the publication revision (optional if pid is used).
     * @param string  $args['template']      Custom publication type template to use.
     * @param integer $args['cachelifetime'] Cache lifetime (empty for default config).
     *
     * @return Publication output.
     */
    public function display($args)
    {
        //// Validation
        // get the tid first
        $args['tid'] = isset($args['tid']) ? $args['tid'] : FormUtil::getPassedValue('tid');

        if (!Clip_Util::validateTid($args['tid'])) {
            return LogUtil::registerError($this->__f('Error! Invalid publication type ID passed [%s].', DataUtil::formatForDisplay($args['tid'])));
        }

        $pubtype = Clip_Util::getPubType($args['tid']);

        //// Parameters
        // define the arguments
        $apiargs = array(
            'tid'           => $args['tid'],
            'pid'           => isset($args['pid']) ? (int)$args['pid'] : FormUtil::getPassedValue('pid'),
            'id'            => isset($args['id']) ? (int)$args['id'] : FormUtil::getPassedValue('id'),
            'checkperm'     => false,
            'handleplugins' => true,
            'loadworkflow'  => true
        );
        $args = array(
            'template'      => isset($args['template']) ? $args['template'] : FormUtil::getPassedValue('template'),
            'cachelifetime' => isset($args['cachelifetime']) ? $args['cachelifetime'] : FormUtil::getPassedValue('cachelifetime', $pubtype['cachelifetime'])
        );

        // post validation
        if ((empty($apiargs['pid']) || !is_numeric($apiargs['pid'])) && (empty($apiargs['id']) || !is_numeric($apiargs['id']))) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'id | pid'));
        }

        // get the pid if it was not passed
        if (empty($apiargs['pid'])) {
            $apiargs['pid'] = ModUtil::apiFunc('Clip', 'user', 'getPid', $apiargs);
        }

        // determine the template to use
        if (empty($args['template'])) {
            // template for the security check
            $args['templateid'] = $pubtype['outputset'];
            // template comes from pubtype
            $args['template']   = $pubtype['outputset'].'/display.tpl';
        } else {
            // template comes from parameter
            $args['template']   = DataUtil::formatForOS($args['template']);
            $args['templateid'] = $pubtype['outputset']."_{$args['template']}";
            // check for related plain templates
            if (in_array($args['template'], array('pending'))) {
                $args['templatesimple'] = $pubtype['outputset']."/display_{$args['template']}.tpl";
            } else {
                $args['template'] = $pubtype['outputset']."/display_{$args['template']}.tpl";
            }
        }

        //// Security check
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Clip:full:', "{$apiargs['tid']}:{$apiargs['pid']}:{$args['templateid']}", ACCESS_READ));

        //// Output setup
        // check if this view is cached
        if (!empty($args['cachelifetime']) && !SecurityUtil::checkPermission('clip:input:', "{$apiargs['tid']}:{$apiargs['pid']}:", ACCESS_ADMIN)) {
            $this->view->setCache_lifetime($args['cachelifetime']);
            // second clause allow developer to add an edit button on the "display" template
            $cachetid = true;
            $cacheid = 'display'.$apiargs['tid'].'|'.$apiargs['pid'].'|'.$args['templateid'];
        } else {
            $cachetid = false;
            $cacheid  = null;
        }

        // build the output
        $this->view->setCaching($cachetid)
                   ->setCache_Id($cacheid)
                   ->add_core_data();

        if ($cachetid && $this->view->is_cached($args['template'], $cacheid)) {
            return $this->view->fetch($args['template'], $cacheid);
        }

        // fetch simple templates
        if (isset($args['templatesimple'])) {
            if (!$this->view->template_exists($args['templatesimple'])) {
                $args['templatesimple'] = "clip_general_{$args['template']}.tpl";
                if (!$this->view->template_exists($args['templatesimple'])) {
                    $args['templatesimple'] = '';
                }
            }
            if ($args['templatesimple'] != '') {
                $this->view->assign('pubtype', $pubtype);
                return $this->view->fetch($args['templatesimple'], $cacheid);
            }
        }

        //// Misc values
        // not cached or cache disabled, then get the Pub from the DB
        $pubfields = Clip_Util::getPubFields($apiargs['tid']);
        if (empty($pubfields)) {
            LogUtil::registerError($this->__('Error! No publication fields found.'));
        }

        $pubtype->mapValue('titlefield', Clip_Util::findTitleField($pubfields));

        //// API call
        $pubdata = ModUtil::apiFunc('Clip', 'user', 'get', $apiargs);

        if (!$pubdata) {
            return LogUtil::registerError($this->__f('No such publication [%s - %s, %s] found.', array($apiargs['tid'], $apiargs['pid'], $apiargs['id'])));
        }

        //// Build the output
        // check if template is available
        if (!$this->view->template_exists($args['template'])) {
            $alert = SecurityUtil::checkPermission('Clip::', '::', ACCESS_ADMIN) && ModUtil::getVar('Clip', 'devmode', false);
            if ($alert) {
                LogUtil::registerStatus($this->__f('Notice: Template [%s] not found.', $args['template']));
            }

            // return an error/void if a block template does not exists
            if (strpos($args['templateid'], $pubtype['outputset'].'_block_') === 0) {
                return $alert ? LogUtil::registerError($this->__f('Notice: Template [%s] not found.', $args['template'])) : '';
            }

            $args['template'] = 'var:template_generic_code';

            // settings for the autogenerated display template
            $this->view->setCompile_check(true)
                       ->assign('clip_generic_tpl', true)
                       ->assign('template_generic_code', Clip_Generator::pubdisplay($apiargs['tid'], $pubdata));
        }

        // stored the used arguments
        Clip_Util::setArgs('user.display', $args);

        // assign the pubdata and pubtype to the output
        $this->view->assign('pubdata', $pubdata)
                   ->assign('pubtype', $pubtype)
                   ->assign('clipargs', Clip_Util::getArgs());

        return $this->view->fetch($args['template'], $cacheid);
    }

    /**
     * Edit/Create a publication.
     *
     * @param integer $args['tid']      ID of the publication type.
     * @param integer $args['pid']      ID of the publication.
     * @param integer $args['id']       ID of the publication revision (optional if pid is used).
     * @param string  $args['template'] Custom publication type template to use.
     *
     * @return Publication output.
     */
    public function edit($args)
    {
        //// Validation
        // get the tid first
        $args['tid'] = isset($args['tid']) ? $args['tid'] : FormUtil::getPassedValue('tid');

        if (!Clip_Util::validateTid($args['tid'])) {
            return LogUtil::registerError($this->__f('Error! Invalid publication type ID passed [%s].', DataUtil::formatForDisplay($args['tid'])));
        }

        $pubtype = Clip_Util::getPubType($args['tid']);

        //// Parameters
        // define the arguments
        $args = array(
            'tid'      => $args['tid'],
            'pid'      => isset($args['pid']) ? (int)$args['pid'] : FormUtil::getPassedValue('pid'),
            'id'       => isset($args['id']) ? (int)$args['id'] : FormUtil::getPassedValue('id'),
            'template' => isset($args['template']) ? (int)$args['template'] : FormUtil::getPassedValue('template'),
        );

        //// Misc values
        $pubfields = Clip_Util::getPubFields($args['tid'], 'lineno');
        if (empty($pubfields)) {
            LogUtil::registerError($this->__('Error! No publication fields found.'));
        }

        $pubtype->mapValue('titlefield', Clip_Util::findTitleField($pubfields));

        if (empty($args['id']) && !empty($args['pid'])) {
            $args['id'] = (int)ModUtil::apiFunc('Clip', 'user', 'getId', $args);

            if (!$args['id']) {
                return LogUtil::registerError($this->__f('Error! No such publication [%s - %s] found.', array($args['tid'], $args['pid'])));
            }
        }

        $alert = SecurityUtil::checkPermission('Clip::', '::', ACCESS_ADMIN) && ModUtil::getVar('Clip', 'devmode', false);

        // get actual state for selecting form Template
        $stepname = 'initial';
        if ($args['id']) {
            $obj = array('id' => $args['id']);
            Zikula_Workflow_Util::getWorkflowForObject($obj, $pubtype->getTableName(), 'id', 'Clip');
            $stepname = $obj['__WORKFLOW__']['state'];
        }
        // adds the stepname to the pubtype
        $pubtype->mapValue('stepname', $stepname);

        //// Form Handler Instance
        // no security check needed
        // the security check will be done for workflow actions and userapi.get
        $handler = new Clip_Form_Handler_User_Pubedit();
        // setup the form handler
        $handler->ClipSetUp($args['id'], $args['tid'], $pubtype, $pubfields);

        //// Build the output
        // create the output object
        $render = Clip_Util::newUserForm($this);

        // resolve the template to use
        // 1. individual step
        $template = $pubtype['inputset']."/form_{$stepname}.tpl";

        if (!empty($stepname) && $render->template_exists($template)) {
            return $render->execute($template, $handler);
        }

        // 2. custom template
        $args['template'] = DataUtil::formatForOS($args['template']);
        $template = $pubtype['inputset']."/form_{$args['template']}.tpl";

        if (!empty($args['template']) && $render->template_exists($template)) {
            return $render->execute($template, $handler);
        }

        // 3. generic edit
        $template = $pubtype['inputset'].'/form_all.tpl';

        if (!$render->template_exists($template)) {
            if ($alert) {
                LogUtil::registerStatus($this->__f('Notice: Template [%s] not found.', $pubtype['inputset']."/form_{$stepname}.tpl"));
                if (!empty($args['template'])) {
                    LogUtil::registerStatus($this->__f('Notice: Template [%s] not found.', $pubtype['inputset']."/form_{$args['template']}.tpl"));
                }
                LogUtil::registerStatus($this->__f('Notice: Template [%s] not found.', $template));
            }

            $template = 'var:template_generic_code';

            // settings for the autogenerated edit template
            $render->setCompile_check(true)
                   ->assign('clip_generic_tpl', true)
                   ->assign('template_generic_code', Clip_Generator::pubedit($args['tid']));
        }

        // stored the used arguments and assign them to the view
        Clip_Util::setArgs('user.edit', $args);

        $render->assign('clipargs', Clip_Util::getArgs())
               ->assign('pubtype', $pubtype);

        return $render->execute($template, $handler);
    }

    /**
     * Executes a Workflow command directly.
     *
     * @param integer $args['tid']         ID of the publication type.
     * @param integer $args['id']          ID of the publication revision.
     * @param string  $args['commandName'] Command name has to be a valid workflow action for the currenct state.
     * @param string  $args['goto']        Redirect-to after execution.
     */
    public function exec($args)
    {
        //// Validation
        // get the tid first
        $args['tid'] = isset($args['tid']) ? $args['tid'] : FormUtil::getPassedValue('tid');

        if (!Clip_Util::validateTid($args['tid'])) {
            return LogUtil::registerError($this->__f('Error! Invalid publication type ID passed [%s].', DataUtil::formatForDisplay($args['tid'])));
        }

        $pubtype = Clip_Util::getPubType($args['tid']);

        //// Parameters
        // define the arguments
        $args = array(
            'tid'           => $args['tid'],
            'id'            => isset($args['id']) ? $args['id'] : FormUtil::getPassedValue('id'),
            'commandName'   => isset($args['commandName']) ? $args['commandName'] : FormUtil::getPassedValue('commandName'),
            'goto'          => isset($args['goto']) ? $args['goto'] : FormUtil::getPassedValue('goto'),
            'checkPerm'     => false, // API
            'handleplugins' => true,  // API
            'loadworkflow'  => true   // API
        );

        // post validation
        if (empty($args['id']) || !is_numeric($args['id'])) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'id'));
        }

        if (empty($args['commandName'])) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'commandName'));
        }

        //// Misc values
        // get the schema
        $args['schema'] = str_replace('.xml', '', $pubtype->workflow);

        // get the publication
        $pub = Doctrine_Core::getTable('Clip_Model_Pubdata'.$args['tid'])->find($args['id']);

        if (!$pub) {
            return LogUtil::registerError($this->__f('Error! No such publication [%s] found.', DataUtil::formatForDisplay($args['id'])));
        }

        Zikula_Workflow_Util::executeAction($args['schema'], $pub, $args['commandName'], $pubtype->getTableName(), 'Clip');

        // process the redirect
        $displayUrl = ModUtil::url('Clip', 'user', 'display',
                                   array('tid' => $args['tid'],
                                         'id'  => $args['id']));

        switch ($args['goto'])
        {
            case 'edit':
                $goto = ModUtil::url('Clip', 'user', 'edit',
                                     array('tid' => $args['tid'],
                                           'id'  => $args['id']));
                break;

            case 'stepmode':
                $goto = ModUtil::url('Clip', 'user', 'edit',
                                      array('tid'  => $args['tid'],
                                            'id'   => $args['id'],
                                            'goto' => 'stepmode'));
                break;

            case 'referer':
                $goto = System::serverGetVar('HTTP_REFERER', $displayUrl);
                break;

            case 'editlist':
                $goto = ModUtil::url('Clip', 'admin', 'editlist',
                                     array('_id' => $args['tid'].'_'.$pub['core_pid']));
                break;

            case 'admin':
                $goto = ModUtil::url('Clip', 'admin', 'publist', array('tid' => $args['tid']));
                break;

            case 'index':
                $goto = ModUtil::url('Clip', 'user', 'view', array('tid' => $args['tid']));
                break;

            case 'home':
                $goto = System::getHomepageUrl();
                break;

            default:
                $goto = $displayUrl;
        }

        return System::redirect($goto);
    }

    /**
     * Javascript hierarchical menu of edit links.
     *
     * @author rgasch
     * @param  $args['tid']
     * @param  $args['pid'] (optional)
     * @param  $args['edit'] (optional)
     * @param  $args['menu'] (optional)
     * @param  $args['orderby'] (optional)
     * @param  $args['returntype'] (optional)
     * @param  $args['source'] (optional)
     *
     * @return Publication menu and/or edit mask.
     */
    public function editlist($args=array())
    {
        $tid        = isset($args['tid']) ? $args['tid'] : FormUtil::getPassedValue('tid');
        $pid        = isset($args['pid']) ? $args['pid'] : FormUtil::getPassedValue('pid');
        $edit       = isset($args['edit']) ? $args['edit'] : FormUtil::getPassedValue('edit', 1);
        $menu       = isset($args['menu']) ? $args['menu'] : FormUtil::getPassedValue('menu', 1);
        $returntype = isset($args['returntype']) ? $args['returntype'] : FormUtil::getPassedValue('returntype', 'user');
        $source     = isset($args['source']) ? $args['source'] : FormUtil::getPassedValue('source', 'module');

        $args['orderby'] = isset($args['orderby']) ? $args['orderby'] : FormUtil::getPassedValue('orderby', 'core_title');

        $pubData = ModUtil::apiFunc('Clip', 'user', 'editlist', $args);

        // create the output object
        $this->view->assign('allTypes',   $pubData['allTypes'])
                   ->assign('publist',    $pubData['pubList'])
                   ->assign('tid',        $tid)
                   ->assign('pid',        $pid)
                   ->assign('edit',       $edit)
                   ->assign('menu',       $menu)
                   ->assign('returntype', $returntype)
                   ->assign('source',     $source);

        return $this->view->fetch('clip_user_editlist.tpl');
    }

    /**
     * @see Clip_Controller_User::display
     * @deprecated
     */
    public function viewpub($args)
    {
        return $this->display($args);
    }

    /**
     * @see Clip_Controller_User::edit
     * @deprecated
     */
    public function pubedit($args)
    {
        return $this->edit($args);
    }

    /**
     * @see Clip_Controller_User::editlist
     * @deprecated
     */
    public function pubeditlist($args)
    {
        return $this->editlist($args);
    }

    /**
     * @see Clip_Controller_User::exec
     * @deprecated
     */
    public function executecommand($args)
    {
        return $this->exec($args);
    }
}
