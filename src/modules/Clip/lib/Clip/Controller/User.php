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
        //// Pubtype
        // validate and get the publication type first
        $args['tid'] = isset($args['tid']) ? $args['tid'] : FormUtil::getPassedValue('tid');

        if (!Clip_Util::validateTid($args['tid'])) {
            return LogUtil::registerError($this->__f('Error! Invalid publication type ID passed [%s].', DataUtil::formatForDisplay($args['tid'])));
        }

        $pubtype = Clip_Util::getPubType($args['tid']);

        //// Parameters
        // define the arguments
        $apiargs = array(
            'tid'           => $args['tid'],
            'itemsperpage'  => (isset($args['itemsperpage']) && is_numeric($args['itemsperpage']) && (int)$args['itemsperpage'] >= 0) ? (int)$args['itemsperpage'] : (int)$pubtype['itemsperpage'],
            'filter'        => isset($args['filter']) ? $args['filter'] : null,
            'orderby'       => isset($args['orderby']) ? $args['orderby'] : FormUtil::getPassedValue('orderby'),
            'handleplugins' => isset($args['handleplugins']) ? (bool)$args['handleplugins'] : true,
            'loadworkflow'  => isset($args['loadworkflow']) ? (bool)$args['loadworkflow'] : false,
            'checkperm'     => false,
            'countmode'     => 'both'
        );
        $args = array(
            'template'      => isset($args['template']) ? $args['template'] : FormUtil::getPassedValue('template'),
            'startnum'      => (isset($args['startnum']) && is_numeric($args['startnum'])) ? (int)$args['startnum'] : (int)FormUtil::getPassedValue('startnum', 0),
            'page'          => (isset($args['page']) && is_numeric($args['page'])) ? (int)$args['page'] : (int)abs(FormUtil::getPassedValue('page', 1)),
            'cachelifetime' => isset($args['cachelifetime']) ? (int)$args['cachelifetime'] : $pubtype['cachelifetime'],
        );

        // sets the function parameter (navbar)
        $this->view->assign('func', 'list');

        //// Validation
        // for public list allows a maximum of items
        if ($apiargs['itemsperpage'] == 0) {
            $apiargs['itemsperpage'] = $this->getVar('maxperpage', 100);
        }

        if ($args['page'] > 1) {
            $apiargs['startnum'] = ($args['page']-1)*$apiargs['itemsperpage']+1;
        }

        //// Template
        // checks for the input template value
        $args['template']   = DataUtil::formatForOS($args['template']);
        // cleans it of not desired parameters
        $args['template']   = preg_replace('#[^a-zA-Z0-9_]+#', '', $args['template']);
        if (empty($args['template'])) {
            $apiargs['templateid'] = '';
            $args['template']   = $pubtype['outputset'].'/list.tpl';
        } else {
            $apiargs['templateid'] = "{$args['template']}";
            $args['template']   = $pubtype['outputset']."/list_{$args['template']}.tpl";
        }

        //// Security
        $this->throwForbiddenUnless(Clip_Access::toPubtype($pubtype, 'list', $apiargs['templateid']));

        //// Cache
        // check if cache is enabled and this view is cached
        if (!empty($args['cachelifetime']) && $this->view->template_exists($args['template'])) {
            $this->view->setCacheLifetime($args['cachelifetime']);

            $filterid = $apiargs['filter'] ? Clip_Util::getFilterCacheString($apiargs['filter']) : Clip_Util::getFilterCacheId();

            $cacheid = 'tid_'.$apiargs['tid'].'/list'
                       .'/'.UserUtil::getGidCacheString()
                       .'/tpl_'.(!empty($apiargs['templateid']) ? $apiargs['templateid'] : 'clipdefault')
            // FIXME PLUGINS Add plugin specific cache sections
            // $cacheid .= '|field'.id.'|'.output
                       .'/perpage_'.$apiargs['itemsperpage']
                       .'/filter_'.(!empty($filterid) ? $filterid : 'clipnone')
                       .'/order_'.(!empty($orderby) ? Clip_Util::createOrderBy($apiargs['orderby']) : 'clipnone')
                       .'/start_'.(!empty($apiargs['startnum']) ? $apiargs['startnum'] : '0');

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

        //// Execution
        // uses the API to get the list of publications
        $result = ModUtil::apiFunc('Clip', 'user', 'getall', $apiargs);

        // stored the used arguments
        Clip_Util::setArgs('list', $args);

        // resolve the permalink
        $returnurl = ModUtil::url('Clip', 'user', 'list',
                                  array('tid' => $pubtype['tid']),
                                  null, null, true, true);

        //// Output
        // assign the data to the output
        $this->view->assign('pubtype',   $pubtype)
                   ->assign('publist',   $result['publist'])
                   ->assign('clipargs',  Clip_Util::getArgs())
                   ->assign('returnurl', $returnurl);

        // assign the pager values
        $this->view->assign('pager', array('numitems'     => $result['pubcount'],
                                           'itemsperpage' => $apiargs['itemsperpage']));

        // check if the template is not available
        if (!$this->view->template_exists($args['template'])) {
            // auto-generate it only on development mode
            if (!ModUtil::getVar('Clip', 'devmode', false)) {
                return LogUtil::registerError($this->__('The list cannot be displayed. Please contact the administrator.'));
            }

            $isadmin = Clip_Access::toPubtype($pubtype);

            if ($isadmin) {
                LogUtil::registerStatus($this->__f('Notice: Template [%s] not found.', $args['template']));
            }

            // check the generic template to use
            if (strpos($apiargs['templateid'], 'block_') === 0) {
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
        //// Pubtype
        // validate and get the publication type first
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

        // sets the function parameter (navbar)
        $this->view->assign('func', 'display');

        //// Validation
        // required the publication ID or record ID
        if ((empty($apiargs['pid']) || !is_numeric($apiargs['pid'])) && (empty($apiargs['id']) || !is_numeric($apiargs['id']))) {
            return LogUtil::registerError($this->__f('Error! Missing or wrong argument [%s].', 'id | pid'));
        }

        // get the pid if it was not passed
        if (empty($apiargs['pid'])) {
            $apiargs['pid'] = ModUtil::apiFunc('Clip', 'user', 'getPid', $apiargs);
        }

        //// Template
        // checks for the input template value
        $args['template'] = DataUtil::formatForOS($args['template']);
        // cleans it of not desired parameters
        $args['template'] = preg_replace('#[^a-zA-Z0-9_]+#', '', $args['template']);
        if (empty($args['template'])) {
            $apiargs['templateid'] = '';
            $args['template']   = $pubtype['outputset'].'/display.tpl';
        } else {
            $apiargs['templateid'] = "{$args['template']}";
            // check for simple-templates request
            if (Clip_Util::isSimpleTemplate($args['template'])) {
                $args['templatesimple'] = $pubtype['outputset']."/displaysimple_{$args['template']}.tpl";
            } else {
                $args['template'] = $pubtype['outputset']."/display_{$args['template']}.tpl";
            }
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
                $this->view->assign('clip_simple_tpl', true)
                           ->assign('pubtype', $pubtype);
                return $this->view->fetch($args['templatesimple']);
            }
        }

        //// Security
        $this->throwForbiddenUnless(Clip_Access::toPub($pubtype, $apiargs['pid'], $apiargs['id'], ACCESS_READ, null, 'display', $apiargs['templateid']));

        //// Cache
        // check if cache is enabled and this view is cached
        if (!empty($args['cachelifetime']) && $this->view->template_exists($args['template'])) {
            $this->view->setCacheLifetime($args['cachelifetime']);

            $cacheid = 'tid_'.$apiargs['tid']
                       .'/pid'.$apiargs['pid']
                       .'/id'.$apiargs['id']
                       .'/tpl_'.(!empty($apiargs['templateid']) ? $apiargs['templateid'] : 'clipdefault')
                       .'/'.UserUtil::getGidCacheString();
            // FIXME PLUGINS Add plugin specific cache sections
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

        //// Execution
        // setup an admin flag
        $isadmin = Clip_Access::toPubtype($pubtype);

        // get the publication from the database
        $pubdata = ModUtil::apiFunc('Clip', 'user', 'get', $apiargs);

        if (!$pubdata) {
            if ($isadmin) {
                // detailed error message for the admin only
                return LogUtil::registerError($this->__f('No such publication [tid: %1$s - pid: %2$s; id: %3$s] found.', array($apiargs['tid'], $apiargs['pid'], $apiargs['id'])));
            } else {
                return LogUtil::registerError($this->__('No such publication found.'));
            }
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

        //// Output
        // check if template is not available
        if (!$this->view->template_exists($args['template'])) {
            // auto-generate it only on development mode
            if (!ModUtil::getVar('Clip', 'devmode', false)) {
                return LogUtil::registerError($this->__('The publication cannot be displayed. Please contact the administrator.'));
            }

            if ($isadmin) {
                LogUtil::registerStatus($this->__f('Notice: Template [%s] not found.', $args['template']));
            }

            // check the generic template to use
            if (strpos($apiargs['templateid'], 'block_') === 0) {
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
        //// Pubtype
        // get the publication type first
        $args['tid'] = isset($args['tid']) ? $args['tid'] : FormUtil::getPassedValue('tid');

        if (!Clip_Util::validateTid($args['tid'])) {
            return LogUtil::registerError($this->__f('Error! Invalid publication type ID passed [%s].', DataUtil::formatForDisplay($args['tid'])));
        }

        $pubtype = Clip_Util::getPubType($args['tid'])->mapTitleField();

        //// Parameters
        // define the arguments
        $args = array(
            'tid'      => $args['tid'],
            'pid'      => isset($args['pid']) ? (int)$args['pid'] : FormUtil::getPassedValue('pid'),
            'id'       => isset($args['id']) ? (int)$args['id'] : FormUtil::getPassedValue('id'),
            'template' => isset($args['template']) ? (int)$args['template'] : FormUtil::getPassedValue('template'),
            'lastrev'  => true
        );

        // sets the function parameter (navbar)
        $this->view->assign('func', 'edit');

        //// Validation
        // check for the pubfields
        $pubfields = Clip_Util::getPubFields($args['tid'], 'lineno');

        if (empty($pubfields)) {
            LogUtil::registerError($this->__('Error! No publication fields found.'));
        }

        // check for the pub ID if not passed
        if (empty($args['id']) && !empty($args['pid'])) {
            $args['id'] = (int)ModUtil::apiFunc('Clip', 'user', 'getId', $args);

            if (!$args['id']) {
                return LogUtil::registerError($this->__f('Error! No such publication [%s - %s] found.', array($args['tid'], $args['pid'])));
            }
        }

        //// Publication processing
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

                return $view->redirect(ModUtil::url('Clip', 'user', 'list', array('tid' => $args['tid'])));
            }
        } else {
            // initial values
            $classname = 'Clip_Model_Pubdata'.$args['tid'];
            $pubdata = new $classname();
        }

        // get the workflow state
        $workflow = new Clip_Workflow($pubtype, $pubdata);

        $args['state'] = $workflow->getWorkflow('state');

        //// Form Handler
        // no security check needed, will be done by the workflow actions for the state
        $handler = new Clip_Form_Handler_User_Pubedit();

        // setup the form handler
        $handler->ClipSetUp($args['id'], $args['tid'], $pubdata, $workflow, $pubtype, $pubfields);

        //// Output
        // checks for the input template value
        // and cleans it of not desired parameters
        $args['template'] = DataUtil::formatForOS($args['template']);
        $args['template'] = preg_replace('#[^a-zA-Z0-9_]+#', '', $args['template']);

        // create the output object
        $render = Clip_Util::newUserForm($this);

        Clip_Util::setArgs('edit', $args);

        $render->assign('clipargs', Clip_Util::getArgs())
               ->assign('pubtype', $pubtype);

        // resolve the template to use
        // 1. custom template
        if (!empty($args['template'])) {
            $template = $pubtype['inputset']."/form_template_{$args['template']}.tpl";

            if ($render->template_exists($template)) {
                return $render->execute($template, $handler);
            }
        }

        // 2. individual state
        $template = $pubtype['inputset']."/form_{$args['state']}.tpl";

        if ($render->template_exists($template)) {
            return $render->execute($template, $handler);
        }

        // 3. generic edit
        $template = $pubtype['inputset'].'/form_all.tpl';

        if (!$render->template_exists($template)) {
            // auto-generate it only on development mode
            if (!ModUtil::getVar('Clip', 'devmode', false)) {
                return LogUtil::registerError($this->__('The form cannot be displayed. Please contact the administrator.'));
            }

            $alert = Clip_Access::toPubtype($pubtype);

            if ($alert) {
                if (!empty($args['template'])) {
                    LogUtil::registerStatus($this->__f('Notice: Template [%s] not found.', $pubtype['inputset']."/form_template_{$args['template']}.tpl"));
                }
                LogUtil::registerStatus($this->__f('Notice: Template [%s] not found.', $pubtype['inputset']."/form_{$args['state']}.tpl"));
                LogUtil::registerStatus($this->__f('Notice: Template [%s] not found.', $template));
            }

            $template = 'var:template_generic_code';

            // settings for the autogenerated edit template
            $render->setForceCompile(true)
                   ->assign('clip_generic_tpl', true)
                   ->assign('template_generic_code', Clip_Generator::pubedit($args['tid']));
        }

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
        //// Token check
        $this->checkCsrfToken($this->request->getGet()->get('csrftoken', 'notokenpresent'));

        //// Pubtype
        // get the publication type first
        $args['tid'] = isset($args['tid']) ? $args['tid'] : FormUtil::getPassedValue('tid');

        if (!Clip_Util::validateTid($args['tid'])) {
            return LogUtil::registerError($this->__f('Error! Invalid publication type ID passed [%s].', DataUtil::formatForDisplay($args['tid'])));
        }

        $pubtype = Clip_Util::getPubType($args['tid']);

        //// Parameters
        // old commandName parameter (will be removed on Clip 1.0)
        $args['commandName'] = isset($args['commandName']) ? (bool)$args['commandName'] : FormUtil::getPassedValue('commandName');
        $args['commandName'] = $args['commandName'] ? $args['commandName'] : FormUtil::getPassedValue('action');
        // define the arguments
        $args = array(
            'tid'           => $args['tid'],
            'id'            => isset($args['id']) ? $args['id'] : FormUtil::getPassedValue('id'),
            'action'        => isset($args['action']) ? $args['action'] : $args['commandName'],
            'goto'          => isset($args['goto']) ? $args['goto'] : FormUtil::getPassedValue('goto')
        );

        //// Validation
        if (empty($args['id']) || !is_numeric($args['id'])) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'id'));
        }

        //// Execution
        // get the publication
        $pub = Doctrine_Core::getTable('Clip_Model_Pubdata'.$args['tid'])->find($args['id']);

        if (!$pub) {
            return LogUtil::registerError($this->__f('Error! No such publication [%s] found.', $args['id']));
        }

        // load the publication values and workflow
        $pub->clipValues(true)
            ->clipWorkflow();

        // create the workflow object and execute the action
        $workflow = new Clip_Workflow($pubtype, $pub);

        // be sure to have a valid action
        if (empty($args['action']) || !$workflow->isValidAction($args['action'])) {
            return LogUtil::registerError($this->__('Error! Invalid action passed.'));
        }

        // execute the action and check if failed
        // permission check inside of this method
        $ret = $workflow->executeAction($args['action']);

        if ($ret === false) {
            LogUtil::hasErrors() ? false : LogUtil::registerError($this->__('Unknown workflow action error. Operation failed.'));
        }

        //// Redirect
        // figure out the redirect
        $displayUrl = ModUtil::url('Clip', 'user', 'display',
                                   array('tid' => $args['tid'],
                                         'id'  => $args['id']));

        $returnurl = System::serverGetVar('HTTP_REFERER', $displayUrl);

        switch ($args['goto'])
        {
            case 'stepmode':
                $goto = ModUtil::url('Clip', 'user', 'edit',
                                      array('tid'  => $args['tid'],
                                            'id'   => $args['id'],
                                            'goto' => 'stepmode'));
                break;

            case 'edit':
                $goto = ModUtil::url('Clip', 'user', 'edit',
                                     array('tid' => $args['tid'],
                                           'id'  => $args['id']));
                break;

            case 'list':
                $goto = ModUtil::url('Clip', 'user', 'list', array('tid' => $args['tid']));
                break;

            case 'display':
                $goto = $displayUrl;
                break;

            case 'admin':
                $goto = ModUtil::url('Clip', 'admin', 'pubtypeinfo', array('tid' => $args['tid']));
                break;

            case 'home':
                $goto = System::getHomepageUrl();
                break;

            case 'referer':
            default:
                $goto = $returnurl;
        }

        return System::redirect($goto);
    }

    /**
     * @see Clip_Controller_User::display
     *
     * @deprecated 0.9
     */
    public function viewpub($args)
    {
        return $this->display($args);
    }

    /**
     * @see Clip_Controller_User::edit
     *
     * @deprecated 0.9
     */
    public function pubedit($args)
    {
        return $this->edit($args);
    }

    /**
     * @see Clip_Controller_User::exec
     *
     * @deprecated 0.9
     */
    public function executecommand($args)
    {
        return $this->exec($args);
    }
}
