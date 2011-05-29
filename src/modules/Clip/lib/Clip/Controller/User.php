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
class Clip_Controller_User extends Zikula_AbstractController
{
    /**
     * Unobstrusive workaround to have a "list" method.
     *
     * @param string $func Name of the function invoked.
     * @param mixed  $args Arguments passed to the function.
     *
     * @return mixed Function output.
     */
    public function __call($func, $args)
    {
        switch ($func)
        {
            case 'list':
                return $this->view(isset($args[0])? $args[0]: array());
                break;
        }
    }

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
    public function view($args=array())
    {
        //// Validation
        // get the tid first
        $tid = isset($args['tid']) ? $args['tid'] : FormUtil::getPassedValue('tid');

        if (!Clip_Util::validateTid($tid)) {
            return LogUtil::registerError($this->__f('Error! Invalid publication type ID passed [%s].', DataUtil::formatForDisplay($tid)));
        }

        $pubtype = Clip_Util::getPubType($tid);

        //// Parameters
        // process itemsperpage depending it it's an API call or browser call
        $itemsperpage = (isset($args['itemsperpage']) && is_numeric($args['itemsperpage']) && (int)$args['itemsperpage'] >= 0) ? (int)$args['itemsperpage'] : (int)$pubtype['itemsperpage'];
        // old parameters (will be removed on Clip 1.0)
        $args['handlePluginF'] = isset($args['handlePluginFields']) ? (bool)$args['handlePluginFields'] : FormUtil::getPassedValue('handlePluginFields', true);
        $args['getApprovalS']  = isset($args['getApprovalState']) ? (bool)$args['getApprovalState'] : FormUtil::getPassedValue('getApprovalState', false);
        // define the arguments
        $apiargs = array(
            'tid'           => $tid,
            'itemsperpage'  => $itemsperpage,
            'filter'        => isset($args['filter']) ? $args['filter'] : null,
            'orderby'       => isset($args['orderby']) ? $args['orderby'] : FormUtil::getPassedValue('orderby'),
            'handleplugins' => isset($args['handleplugins']) ? (bool)$args['handleplugins'] : $args['handlePluginF'],
            'loadworkflow'  => isset($args['loadworkflow']) ? (bool)$args['loadworkflow'] : $args['getApprovalS'],
            'checkperm'     => false,
            'countmode'     => 'both'
        );
        $args = array(
            'template'      => isset($args['template']) ? $args['template'] : FormUtil::getPassedValue('template'),
            'startnum'      => (isset($args['startnum']) && is_numeric($args['startnum'])) ? (int)$args['startnum'] : (int)FormUtil::getPassedValue('startnum', 0),
            'page'          => (isset($args['page']) && is_numeric($args['page'])) ? (int)$args['page'] : (int)abs(FormUtil::getPassedValue('page', 1)),
            'cachelifetime' => isset($args['cachelifetime']) ? (int)$args['cachelifetime'] : (int)FormUtil::getPassedValue('cachelifetime', $pubtype['cachelifetime']),
        );

        if ($apiargs['itemsperpage'] == 0) {
            $apiargs['itemsperpage'] = $this->getVar('maxperpage', 100);
        }

        if ($args['page'] > 1) {
            $apiargs['startnum'] = ($args['page']-1)*$apiargs['itemsperpage']+1;
        }

        // template comes from parameter
        $args['template']   = DataUtil::formatForOS($args['template']);
        // cleans it of not desired parameters
        $args['template']   = preg_replace('#[^a-zA-Z0-9_]+#', '', $args['template']);
        if (empty($args['template'])) {
            // template comes from pubtype
            $args['templateid'] = '';
            $args['template']   = $pubtype['outputset'].'/list.tpl';
        } else {
            $args['templateid'] = "/{$args['template']}";
            $args['template']   = $pubtype['outputset']."/list_{$args['template']}.tpl";
        }

        //// Security check
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Clip:list:', "{$apiargs['tid']}::list{$args['templateid']}", ACCESS_READ));

        //// Output setup
        // check if cache is enabled and this view is cached
        if (!empty($args['cachelifetime']) && $this->view->template_exists($args['template'])) {
            $this->view->setCacheLifetime($args['cachelifetime']);

            $cacheid = 'tid_'.$apiargs['tid'].'/list'.$args['templateid'] // templateid = (/template)?
                       .'/'.Clip_Util::getUserGIdentifier()
            // FIXME Add plugin specific cache sections
            // $cacheid .= '|field'.id.'|'.output
                       .'/'.'perpage_'.$apiargs['itemsperpage']
                       .(!empty($apiargs['filter']) ? '/filter_'.$apiargs['filter'] : '')
                       .(!empty($orderby) ? '/order_'.Clip_Util::createOrderBy($apiargs['orderby']) : '')
                       .(!empty($apiargs['startnum']) ? '/start_'.$apiargs['startnum'] : '');

            // set the output info
            $this->view->setCaching(Zikula_View::CACHE_INDIVIDUAL)
                       ->setCacheId($cacheid);

            if ($this->view->is_cached($args['template'])) {
                return $this->view->fetch($args['template']);
            }
        } else {
            $cacheid = null;
            $this->view->setCaching(Zikula_View::CACHE_DISABLED);
        }

        //// API call
        // uses the API to get the list of publications
        $result = ModUtil::apiFunc('Clip', 'user', 'getall', $apiargs);

        // stored the used arguments
        Clip_Util::setArgs('list', $args);

        // resolve the permalink
        $returnurl = ModUtil::url('Clip', 'user', 'view',
                                  array('tid' => $pubtype['tid']),
                                  null, null, true, true);

        //// Build the output
        // assign the data to the output
        $this->view->assign('pubtype',   $pubtype)
                   ->assign('publist',   $result['publist'])
                   ->assign('clipargs',  Clip_Util::getArgs())
                   ->assign('returnurl', $returnurl);

        // assign the pager values
        $this->view->assign('pager', array('numitems'     => $result['pubcount'],
                                           'itemsperpage' => $apiargs['itemsperpage']));

        // check if template is available
        if (!$this->view->template_exists($args['template'])) {
            $alert = SecurityUtil::checkPermission('Clip::', '::', ACCESS_ADMIN) && ModUtil::getVar('Clip', 'devmode', false);

            if ($alert) {
                LogUtil::registerStatus($this->__f('Notice: Template [%s] not found.', $args['template']));
            }

            // check the generic template to use
            if (strpos($args['templateid'], $pubtype['outputset'].'_block_') === 0) {
                $args['template'] = 'clip_generic_blocklist.tpl';
            } else {
                $args['template'] = 'clip_generic_list.tpl';
            }

            $this->view->setForceCompile(true)
                       ->setCaching(Zikula_View::CACHE_DISABLED)
                       ->assign('clip_generic_tpl', true);
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
            'pid'           => isset($args['pid']) ? $args['pid'] : FormUtil::getPassedValue('pid'),
            'id'            => isset($args['id']) ? $args['id'] : FormUtil::getPassedValue('id'),
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
            return LogUtil::registerError($this->__f('Error! Missing or wrong argument [%s].', 'id | pid'));
        }

        // get the pid if it was not passed
        if (empty($apiargs['pid'])) {
            $apiargs['pid'] = ModUtil::apiFunc('Clip', 'user', 'getPid', $apiargs);
        }

        // template comes from parameter
        $args['template']   = DataUtil::formatForOS($args['template']);
        // cleans it of not desired parameters
        $args['template']   = preg_replace('#[^a-zA-Z0-9_]+#', '', $args['template']);
        // determine the template to use
        if (empty($args['template'])) {
            // template for the security check
            $args['templateid'] = 'display';
            // template comes from pubtype
            $args['template']   = $pubtype['outputset'].'/display.tpl';
        } else {
            $args['templateid'] = "display/{$args['template']}";
            // check for related plain templates
            if (in_array($args['template'], array('pending'))) {
                $args['templatesimple'] = $pubtype['outputset']."/display_{$args['template']}.tpl";
            } else {
                $args['template'] = $pubtype['outputset']."/display_{$args['template']}.tpl";
            }
        }

        //// Security check
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Clip:display:', "{$apiargs['tid']}:{$apiargs['pid']}:{$args['templateid']}", ACCESS_READ));

        //// Output setup
        // fetch simple templates (notifications, etc)
        if (isset($args['templatesimple'])) {
            if (!$this->view->template_exists($args['templatesimple'])) {
                $args['templatesimple'] = "clip_general_{$args['template']}.tpl";
                if (!$this->view->template_exists($args['templatesimple'])) {
                    $args['templatesimple'] = '';
                }
            }
            if ($args['templatesimple'] != '') {
                $this->view->assign('pubtype', $pubtype);
                return $this->view->fetch($args['templatesimple']);
            }
        }

        // check if cache is enabled and this view is cached
        if (!empty($args['cachelifetime']) && $this->view->template_exists($args['template'])) {
            $this->view->setCacheLifetime($args['cachelifetime']);

            $dispath = str_replace('display', 'display/pid'.$apiargs['pid'].'/id'.$apiargs['id'], $args['templateid']);
            $cacheid = 'tid_'.$apiargs['tid'].'/'.$dispath // dispath = display/pidX/idY(/template)?
                       .'/'.Clip_Util::getUserGIdentifier();
            // FIXME Add plugin specific cache sections
            // $cacheid .= '|field'.id.'|'.output

            // set the output info
            $this->view->setCaching(Zikula_View::CACHE_INDIVIDUAL)
                       ->setCacheId($cacheid);

            if ($this->view->is_cached($args['template'])) {
                return $this->view->fetch($args['template']);
            }
        } else {
            $cacheid = null;
            $this->view->setCaching(Zikula_View::CACHE_DISABLED);
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

        // stored the used arguments
        Clip_Util::setArgs('display', $args);

        // resolve the permalink
        $apiargs = Clip_Util::getArgs('getapi');
        $returnurl = ModUtil::url('Clip', 'user', 'display',
                                  array('tid' => $apiargs['tid'],
                                        'pid' => $apiargs['pid']),
                                  null, null, true, true);

        // assign the pubdata and pubtype to the output
        $this->view->assign('pubdata',   $pubdata)
                   ->assign('pubtype',   $pubtype)
                   ->assign('returnurl', $returnurl)
                   ->assign('clipargs',  Clip_Util::getArgs());

        //// Build the output
        // check if template is available
        if (!$this->view->template_exists($args['template'])) {
            $alert = SecurityUtil::checkPermission('Clip::', '::', ACCESS_ADMIN) && ModUtil::getVar('Clip', 'devmode', false);

            if ($alert) {
                LogUtil::registerStatus($this->__f('Notice: Template [%s] not found.', $args['template']));
            }

            // check the generic template to use
            if (strpos($args['templateid'], $pubtype['outputset'].'_block_') === 0) {
                $isblock = true;
            } else {
                $isblock = false;
            }

            $args['template'] = 'var:template_generic_code';

            // settings for the autogenerated display template
            $this->view->setForceCompile(true)
                       ->setCaching(Zikula_View::CACHE_DISABLED)
                       ->assign('clip_generic_tpl', true)
                       ->assign('template_generic_code', Clip_Generator::pubdisplay($apiargs['tid'], true, $isblock));
        }

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
            'lastrev'  => true
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

        // process a new or existing pub, and it's available actions
        if ($args['id']) {
            $pubdata = ModUtil::apiFunc('Clip', 'user', 'get', array(
                'tid' => $args['tid'],
                'id'  => $args['id'],
                'checkperm'     => true,
                'handleplugins' => false, // do not interfer with selected values on edit form
                'loadworkflow'  => false
            ));

            // validate the pudblication
            if (!$pubdata) {
                LogUtil::registerError($this->__f('Error! No such publication [%s - %s] found.', array($args['tid'], $args['id'])));

                return $view->redirect(ModUtil::url('Clip', 'user', 'view', array('tid' => $args['tid'])));
            }
        } else {
            // initial values
            $classname = 'Clip_Model_Pubdata'.$args['tid'];
            $pubdata = new $classname();
        }

        $workflow = new Clip_Workflow($pubtype, $pubdata);

        $args['state'] = $workflow->getWorkflow('state');

        //// Form Handler Instance
        // no security check needed
        // the security check will be done for workflow actions and userapi.get
        $handler = new Clip_Form_Handler_User_Pubedit();
        // setup the form handler
        $handler->ClipSetUp($args['id'], $args['tid'], $pubdata, $workflow, $pubtype, $pubfields);

        //// Build the output
        // create the output object
        $render = Clip_Util::newUserForm($this);

        // resolve the template to use
        // 1. custom template
        if (!empty($args['template'])) {
            $args['template'] = DataUtil::formatForOS($args['template']);
            $template = $pubtype['inputset']."/form_custom_{$args['template']}.tpl";

            if ($render->template_exists($template)) {
                return $render->execute($template, $handler);
            } else {
                LogUtil::registerStatus($this->__f('Notice: Template [%s] not found.', $template));
            }
        }

        // 2. individual state
        $template = $pubtype['inputset']."/form_{$args['state']}.tpl";

        if (!empty($args['state']) && $render->template_exists($template)) {
            return $render->execute($template, $handler);
        }

        // 3. generic edit
        $alert = SecurityUtil::checkPermission('Clip::', '::', ACCESS_ADMIN) && ModUtil::getVar('Clip', 'devmode', false);

        $template = $pubtype['inputset'].'/form_all.tpl';

        if (!$render->template_exists($template)) {
            if ($alert) {
                LogUtil::registerStatus($this->__f('Notice: Template [%s] not found.', $pubtype['inputset']."/form_{$args['state']}.tpl"));
                LogUtil::registerStatus($this->__f('Notice: Template [%s] not found.', $template));
            }

            $template = 'var:template_generic_code';

            // settings for the autogenerated edit template
            $render->setForceCompile(true)
                   ->assign('clip_generic_tpl', true)
                   ->assign('template_generic_code', Clip_Generator::pubedit($args['tid']));
        }

        // stored the used arguments and assign them to the view
        Clip_Util::setArgs('edit', $args);

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
