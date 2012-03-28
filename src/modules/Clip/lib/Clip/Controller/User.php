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

            default:
                return parent::__call($func, $args);
        }
    }

    /**
     * Main user function.
     *
     * @param integer $args['tid']           ID of the publication type.
     * @param string  $args['template']      Custom publication type template to use.
     * @param integer $args['cachelifetime'] Cache lifetime (empty for default pubtype config).
     *
     * @return string Publication main output.
     */
    public function main($args)
    {
        //// Pubtype
        // validate and get the publication type first
        $args['tid'] = isset($args['tid']) ? $args['tid'] : FormUtil::getPassedValue('tid');

        if (!$args['tid'] && $this->getVar('pubtype')) {
            System::redirect(ModUtil::url('Clip', 'user', 'main', array('tid' => $this->getVar('pubtype'))));
        }

        if (!Clip_Util::validateTid($args['tid'])) {
            return LogUtil::registerError($this->__f('Error! Invalid publication type ID passed [%s].', DataUtil::formatForDisplay($args['tid'])));
        }

        $pubtype = Clip_Util::getPubType($args['tid']);

        //// Parameters
        // extract the clip arguments
        $clipvalues = array();
        Clip_Util::getClipArgs($clipvalues, $this->request->getGet(), $args);

        // define the arguments
        $args = array(
            'tid'           => $args['tid'],
            'template'      => isset($args['template']) ? $args['template'] : FormUtil::getPassedValue('template'),
            'cachelifetime' => isset($args['cachelifetime']) ? (int)$args['cachelifetime'] : $pubtype['cachelifetime'],
        );

        // sets the function parameter (navbar)
        $this->view->assign('func', 'main');

        //// Template
        // checks for the input template value
        $args['template'] = DataUtil::formatForOS($args['template']);
        // cleans it of not desired parameters
        $args['template'] = preg_replace(Clip_Util::REGEX_TEMPLATE, '', $args['template']);
        if (empty($args['template'])) {
            $args['templateid']   = 'clipdefault';
            $args['templatefile'] = 'main.tpl';
        } else {
            $args['templateid'] = "{$args['template']}";
            if (Clip_Util::isSimpleTemplate($args['template'])) {
                $args['templatefile']   = Clip_Util::isSimpleTemplate($args['template']);
                $args['templatesimple'] = "simple_{$args['templatefile']}.tpl";
            } else {
                $args['templatefile'] = "main_{$args['template']}.tpl";
            }
        }

        //// Security
        $this->throwForbiddenUnless(Clip_Access::toPubtype($pubtype, 'main', $args['templateid']));

        // fetch simple templates
        if (isset($args['templatesimple'])) {
            // make sure the simple template exists
            if ($this->view->template_exists($pubtype['folder'].'/'.$args['templatesimple'])) {
                $args['templatesimple'] = $pubtype['folder'].'/'.$args['templatesimple'];
            } else if (!$this->view->template_exists($args['templatesimple'])) {
                $args['templatesimple'] = '';
            }

            if (!$args['templatesimple']) {
                return LogUtil::registerError($this->__('The requested page cannot be displayed. Please contact the administrador.'));
            }

            return $this->view->assign('clip_simple_tpl', true)
                              ->assign('clipvalues', $clipvalues)
                              ->assign('pubtype', $pubtype)
                              ->fetch($args['templatesimple']);
        }

        // alert pubtype admins only
        $alert = $this->getVar('devmode', false) && Clip_Access::toPubtype($pubtype);

        //// Cache
        // validate the template existance, if not defaults to the general one
        if (!$this->getVar('commontpls', false) || $this->view->template_exists($pubtype['folder'].'/'.$args['templatefile'])) {
            $args['templatefile'] = $pubtype['folder'].'/'.$args['templatefile'];
        } else {
            if ($alert) {
                LogUtil::registerStatus($this->__f('Notice: Template [%s] not found.', $pubtype['folder'].'/'.$args['templatefile']));
            }

            $args['templatefile'] = 'common_'.$args['templatefile'];
        }

        // check if the common does not exist
        if (!$this->view->template_exists($args['templatefile'])) {
            // skip main if there's no template
            if ($args['templateid'] == 'clipdefault') {
                return $this->redirect(Clip_Util::url($args['tid'], 'list'));
            }

            // auto-generate it only on development mode
            if (!$this->getVar('devmode', false)) {
                return LogUtil::registerError($this->__('This page cannot be displayed. Please contact the administrator.'));
            }
        }

        // check if cache is enabled and this view is cached
        if (!empty($args['cachelifetime']) && $this->view->template_exists($args['templatefile'])) {
            $this->view->setCacheLifetime($args['cachelifetime']);

            Clip_Util::register_nocache_plugins($this->view);

            $cacheid = 'tid_'.$args['tid'].'/main'
                       .'/'.UserUtil::getGidCacheString()
                       .'/tpl_'.$args['templateid'];
            // FIXME PLUGINS Add plugin specific cache sections
            // $cacheid .= '|field'.id.'|'.output

            // set the output info
            $this->view->setCaching(Zikula_View::CACHE_INDIVIDUAL)
                       ->setCacheId($cacheid);

            if ($this->view->is_cached($args['templatefile'])) {
                return $this->view->fetch($args['templatefile']);
            }
        } else {
            $cacheid = null;
            $this->view->setCaching(Zikula_View::CACHE_DISABLED);
        }

        // store the arguments used
        Clip_Util::setArgs('main', $args);

        // register clip_util
        Clip_Util::register_utilities($this->view);

        // resolve the permalink
        $returnurl = Clip_Util::url($pubtype['tid'], 'main', array(), null, null, true);

        //// Output
        // assign the data to the output
        $this->view->assign('clipvalues', $clipvalues)
                   ->assign('pubtype',    $pubtype)
                   ->assign('clipargs',   Clip_Util::getArgs())
                   ->assign('returnurl',  $returnurl);

        // notify the ui main screen
        $this->view = Clip_Event::notify('ui.main', $this->view, $args)->getData();

        // check if the template is available to render it
        if (!$this->view->template_exists($args['templatefile'])) {
            if ($alert) {
                LogUtil::registerStatus($this->__f('Notice: Template [%s] not found.', $args['templatefile']));
            }

            $args['templatefile'] = 'generic_main.tpl';

            $this->view->setForceCompile(true)
                       ->setCaching(Zikula_View::CACHE_DISABLED)
                       ->assign('clip_generic_tpl', true);
        }

        return $this->view->fetch($args['templatefile'], $cacheid);
    }

    /**
     * Publications list.
     *
     * @param integer $args['tid']           ID of the publication type.
     * @param string  $args['template']      Custom publication type template to use.
     * @param string  $args['filter']        Filter string.
     * @param string  $args['orderby']       OrderBy string.
     * @param integer $args['startnum']      Offset item to start from.
     * @param integer $args['page']          Offset page to start from.
     * @param integer $args['itemsperpage']  Number of items to retrieve.
     * @param boolean $args['handleplugins'] Whether to parse the plugin fields.
     * @param boolean $args['loadworkflow']  Whether to add the workflow information.
     * @param integer $args['cachelifetime'] Cache lifetime (empty for default pubtype config).
     *
     * @return string Publication list output.
     */
    public function view($args)
    {
        //// Pubtype
        // validate and get the publication type first
        $args['tid'] = isset($args['tid']) ? $args['tid'] : FormUtil::getPassedValue('tid');

        if (!Clip_Util::validateTid($args['tid'])) {
            return LogUtil::registerError($this->__f('Error! Invalid publication type ID passed [%s].', DataUtil::formatForDisplay($args['tid'])));
        }

        $pubtype = Clip_Util::getPubType($args['tid']);

        //// Parameters
        // extract the clip arguments
        $clipvalues = array();
        Clip_Util::getClipArgs($clipvalues, $this->request->getGet(), $args);

        // define the arguments
        $apiargs = array(
            'tid'           => $args['tid'],
            'itemsperpage'  => (isset($args['itemsperpage']) && is_numeric($args['itemsperpage']) && (int)$args['itemsperpage'] >= 0) ? (int)$args['itemsperpage'] : (int)$pubtype['itemsperpage'],
            'filter'        => isset($args['filter']) ? $args['filter'] : null,
            'orderby'       => isset($args['orderby']) ? $args['orderby'] : FormUtil::getPassedValue('orderby'),
            'handleplugins' => isset($args['handleplugins']) ? (bool)$args['handleplugins'] : true,
            'loadworkflow'  => isset($args['loadworkflow']) ? (bool)$args['loadworkflow'] : false,
            'checkperm'     => false,
            'countmode'     => 'both',
            'rel'           => $pubtype['config']['list']
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

        $apiargs['startnum'] = ($args['page'] > 1) ? ($args['page']-1)*$apiargs['itemsperpage']+1 : $args['startnum'];

        //// Template
        // checks for the input template value
        $args['template'] = DataUtil::formatForOS($args['template']);
        // cleans it of not desired parameters
        $args['template'] = preg_replace(Clip_Util::REGEX_TEMPLATE, '', $args['template']);
        if (empty($args['template'])) {
            $apiargs['templateid'] = 'clipdefault';
            $args['templatefile']  = 'list.tpl';
        } else {
            $apiargs['templateid'] = "{$args['template']}";
            $args['templatefile']  = "list_{$args['template']}.tpl";
        }

        //// Security
        $this->throwForbiddenUnless(Clip_Access::toPubtype($pubtype, 'list', $apiargs['templateid']));

        // alert pubtype admins only
        $alert = $this->getVar('devmode', false) && Clip_Access::toPubtype($pubtype);

        //// Cache
        // validate the template existance, if not defaults to the general one
        if (!$this->getVar('commontpls', false) || $this->view->template_exists($pubtype['folder'].'/'.$args['templatefile'])) {
            $args['templatefile'] = $pubtype['folder'].'/'.$args['templatefile'];
        } else {
            if ($alert) {
                LogUtil::registerStatus($this->__f('Notice: Template [%s] not found.', $pubtype['folder'].'/'.$args['templatefile']));
            }

            $args['templatefile'] = 'common_'.$args['templatefile'];
        }

        // check if the common does not exist
        if (!$this->view->template_exists($args['templatefile']) && !$this->getVar('devmode', false)) {
            // auto-generate it only on development mode
            return LogUtil::registerError($this->__('This page cannot be displayed. Please contact the administrator.'));
        }

        // check if cache is enabled and this view is cached
        if (!empty($args['cachelifetime']) && $this->view->template_exists($args['templatefile'])) {
            $this->view->setCacheLifetime($args['cachelifetime']);

            Clip_Util::register_nocache_plugins($this->view);

            $filterid = $apiargs['filter'] ? Clip_Util::getFilterCacheString($apiargs['filter']) : Clip_Util::getFilterCacheId();

            $cacheid = 'tid_'.$apiargs['tid'].'/list'
                       .'/'.UserUtil::getGidCacheString()
                       .'/tpl_'.$apiargs['templateid']
            // FIXME PLUGINS Add plugin specific cache sections
            // $cacheid .= '|field'.id.'|'.output
                       .'/perpage_'.$apiargs['itemsperpage']
                       .'/filter_'.(!empty($filterid) ? $filterid : 'clipnone')
                       .'/order_'.(!empty($orderby) ? str_replace(array(':', ','), array('', ''), $apiargs['orderby']) : 'clipnone')
                       .'/start_'.(!empty($apiargs['startnum']) ? $apiargs['startnum'] : '0');

            // set the output info
            $this->view->setCaching(Zikula_View::CACHE_INDIVIDUAL)
                       ->setCacheId($cacheid);

            if ($this->view->is_cached($args['templatefile'])) {
                return $this->view->fetch($args['templatefile']);
            }
        } else {
            $cacheid = null;
            $this->view->setCaching(Zikula_View::CACHE_DISABLED);
        }

        //// Execution
        // fill the conditions of the item to get
        $apiargs['where']   = array();
        $apiargs['where'][] = array('core_online = ?', 1);
        $apiargs['where'][] = array('core_visible = ?', 1);
        $apiargs['where'][] = array('core_intrash = ?', 0);
        $apiargs['where'][] = array('(core_publishdate IS NULL OR core_publishdate <= ?)', date('Y-m-d H:i:s', time()) /*new Doctrine_Expression('NOW()')*/);
        $apiargs['where'][] = array('(core_expiredate IS NULL OR core_expiredate >= ?)', date('Y-m-d H:i:s', time()) /*new Doctrine_Expression('NOW()')*/);

        // uses the API to get the list of publications
        list($publist, $pubcount) = array_values(ModUtil::apiFunc('Clip', 'user', 'getall', $apiargs));

        // notify the collection data
        $publist = Clip_Event::notify('data.list', $publist, array_merge($args, $apiargs))->getData();

        // store the arguments used
        Clip_Util::setArgs('list', $args);

        // register clip_util
        Clip_Util::register_utilities($this->view);

        // resolve the permalink
        $returnurl = Clip_Util::url($pubtype['tid'], 'list', array(), null, null, true);

        //// Output
        // assign the data to the output
        $this->view->assign('clipvalues', $clipvalues)
                   ->assign('pubtype',    $pubtype)
                   ->assign('publist',    $publist)
                   ->assign('pubfields',  Clip_Util::getPubFields($apiargs['tid'])->toKeyValueArray('name', 'title'))
                   ->assign('clipargs',   Clip_Util::getArgs())
                   ->assign('returnurl',  $returnurl);

        // assign the pager values
        $this->view->assign('pager', array('numitems'     => $pubcount,
                                           'itemsperpage' => $apiargs['itemsperpage']));

        // notify the ui list
        $this->view = Clip_Event::notify('ui.list', $this->view, array_merge($args, $apiargs))->getData();

        // check if the template is not available
        if (!$this->view->template_exists($args['templatefile'])) {
            if ($alert) {
                // alert pubtype admins only
                LogUtil::registerStatus($this->__f('Notice: Template [%s] not found.', $args['templatefile']));
            }

            // check the generic template to use
            if (strpos($apiargs['templateid'], 'block') === 0) {
                $args['templatefile'] = 'generic_blocklist.tpl';
            } else {
                $args['templatefile'] = 'generic_list.tpl';
            }

            $this->view->setForceCompile(true)
                       ->setCaching(Zikula_View::CACHE_DISABLED)
                       ->assign('clip_generic_tpl', true);
        }

        return $this->view->fetch($args['templatefile'], $cacheid);
    }

    /**
     * Display a publication.
     *
     * @param integer $args['tid']           ID of the publication type.
     * @param integer $args['pid']           ID of the publication.
     * @param integer $args['id']            ID of the publication revision (optional if pid is used).
     * @param string  $args['template']      Custom publication type template to use.
     * @param integer $args['cachelifetime'] Cache lifetime (empty for default pubtype config).
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
        // extract the clip arguments
        $clipvalues = array();
        Clip_Util::getClipArgs($clipvalues, $this->request->getGet(), $args);

        // define the arguments
        $apiargs = array(
            'tid'           => $args['tid'],
            'pid'           => isset($args['pid']) ? $args['pid'] : FormUtil::getPassedValue('pid'),
            'id'            => isset($args['id']) ? $args['id'] : FormUtil::getPassedValue('id'),
            'checkperm'     => false,
            'handleplugins' => true,
            'loadworkflow'  => true,
            'rel'           => $pubtype['config']['display']
        );
        $args = array(
            'template'      => isset($args['template']) ? $args['template'] : FormUtil::getPassedValue('template'),
            'cachelifetime' => isset($args['cachelifetime']) ? (int)$args['cachelifetime'] : $pubtype['cachelifetime']
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
        $args['template'] = preg_replace(Clip_Util::REGEX_TEMPLATE, '', $args['template']);
        if (empty($args['template'])) {
            $apiargs['templateid'] = 'clipdefault';
            $args['templatefile']  = 'display.tpl';
        } else {
            $apiargs['templateid'] = "{$args['template']}";
            $args['templatefile']  = "display_{$args['template']}.tpl";
        }

        //// Security
        $this->throwForbiddenUnless(Clip_Access::toPub($pubtype, $apiargs['pid'], $apiargs['id'], 'display', $apiargs['templateid']));

        // alert pubtype admins only
        $alert = $this->getVar('devmode', false) && Clip_Access::toPubtype($pubtype);

        //// Cache
        // validate the template existance, if not defaults to the general one
        if (!$this->getVar('commontpls', false) || $this->view->template_exists($pubtype['folder'].'/'.$args['templatefile'])) {
            $args['templatefile'] = $pubtype['folder'].'/'.$args['templatefile'];
        } else {
            if ($alert) {
                LogUtil::registerStatus($this->__f('Notice: Template [%s] not found.', $pubtype['folder'].'/'.$args['templatefile']));
            }

            $args['templatefile'] = 'common_'.$args['templatefile'];
        }

        // check if the common does not exist
        if (!$this->view->template_exists($args['templatefile']) && !$this->getVar('devmode', false)) {
            // auto-generate it only on development mode
            return LogUtil::registerError($this->__('This page cannot be displayed. Please contact the administrator.'));
        }

        // check if cache is enabled and this view is cached
        if (!empty($args['cachelifetime']) && $this->view->template_exists($args['templatefile'])) {
            $this->view->setCacheLifetime($args['cachelifetime']);

            Clip_Util::register_nocache_plugins($this->view);

            $cacheid = 'tid_'.$apiargs['tid'].'/display'
                       .'/pid'.$apiargs['pid']
                       .'/id'.$apiargs['id']
                       .'/tpl_'.$apiargs['templateid']
                       .'/'.UserUtil::getGidCacheString();
            // FIXME PLUGINS Add plugin specific cache sections
            // $cacheid .= '|field'.id.'|'.output

            // set the output info
            $this->view->setCaching(Zikula_View::CACHE_INDIVIDUAL)
                       ->setCacheId($cacheid);

            if ($this->view->is_cached($args['templatefile'])) {
                return $this->view->fetch($args['templatefile']);
            }
        } else {
            $cacheid = null;
            $this->view->setCaching(Zikula_View::CACHE_DISABLED);
        }

        //// Execution
        // fill the conditions of the item to get
        $apiargs['where'] = array();
        if (!Clip_Access::toPubtype($apiargs['tid'], 'editor')) {
            $apiargs['where'][] = array('core_online = ?', 1);
            $apiargs['where'][] = array('core_intrash = ?', 0);
        }

        // get the publication from the database
        $pubdata = ModUtil::apiFunc('Clip', 'user', 'get', $apiargs);

        if (!$pubdata) {
            if (Clip_Access::toPubtype($pubtype)) {
                // detailed error message for the admin only
                return LogUtil::registerError($this->__f('No such publication [tid: %1$s - pid: %2$s; id: %3$s] found.', array($apiargs['tid'], $apiargs['pid'], $apiargs['id'])));
            } else {
                return LogUtil::registerError($this->__('No such publication found.'));
            }
        }

        // prevent Doctrine to mess with the publication by cloning it
        $pub = $pubdata->clipCopy();

        // notify the publication data
        $pub = Clip_Event::notify('data.display', $pub)->getData();

        // store the arguments used
        Clip_Util::setArgs('display', $args);

        // register clip_util
        Clip_Util::register_utilities($this->view);

        // resolve the permalink
        $apiargs   = Clip_Util::getArgs('getapi');
        $returnurl = Clip_Util::url($pub, 'display', array(), null, null, true);

        //// Output
        // assign the pubdata and pubtype to the output
        $this->view->assign('clipvalues', $clipvalues)
                   ->assign('pubdata',    $pub)
                   ->assign('relations',  $pub->getRelations(false, 'title'))
                   ->assign('returnurl',  $returnurl)
                   ->assign('pubtype',    $pubtype)
                   ->assign('pubfields',  Clip_Util::getPubFields($apiargs['tid'])->toKeyValueArray('name', 'title'))
                   ->assign('clipargs',   Clip_Util::getArgs());

        // notify the ui display
        $this->view = Clip_Event::notify('ui.display', $this->view, array_merge($args, $apiargs))->getData();

        // check if template is not available
        if (!$this->view->template_exists($args['templatefile'])) {
            if ($alert) {
                LogUtil::registerStatus($this->__f('Notice: Template [%s] not found.', $args['templatefile']));
            }

            // check the generic template to use
            $isblock = (strpos($apiargs['templateid'], 'block') === 0);

            $args['templatefile'] = 'var:template_generic_code';

            // settings for the autogenerated display template
            $this->view->setForceCompile(true)
                       ->setCaching(Zikula_View::CACHE_DISABLED)
                       ->assign('clip_generic_tpl', true)
                       ->assign('template_generic_code', Clip_Generator::pubdisplay($apiargs['tid'], true, $isblock));
        }

        return $this->view->fetch($args['templatefile'], $cacheid);
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

        $pubtype = Clip_Util::getPubType($args['tid']);

        //// Parameters
        // extract the clip arguments
        $clipvalues = array();
        Clip_Util::getClipArgs($clipvalues, $this->request->getGet(), $args);

        // define the arguments
        $args = array(
            'tid'      => $args['tid'],
            'pid'      => isset($args['pid']) ? (int)$args['pid'] : FormUtil::getPassedValue('pid'),
            'id'       => isset($args['id']) ? (int)$args['id'] : FormUtil::getPassedValue('id'),
            'template' => isset($args['template']) ? $args['template'] : FormUtil::getPassedValue('template'),
            'lastrev'  => true
        );

        // sets the function parameter (navbar)
        $this->view->assign('func', 'edit');

        //// Validation
        // check for the pubfields
        $pubfields = Clip_Util::getPubFields($args['tid'])->toArray(false);

        if (empty($pubfields)) {
            LogUtil::registerError($this->__('Error! No publication fields found.'));
        }

        // check for the pub ID if not passed
        if (empty($args['id']) && !empty($args['pid'])) {
            $args['id'] = (int)ModUtil::apiFunc('Clip', 'user', 'getId', $args);

            if (!$args['id']) {
                return LogUtil::registerError($this->__f('Error! No such publication [%1$s - %2$s] found.', array($args['tid'], $args['pid'])));
            }
        }

        //// Template
        // checks for the input template value
        $args['template'] = DataUtil::formatForOS($args['template']);
        // cleans it of not desired parameters
        $args['template'] = preg_replace(Clip_Util::REGEX_TEMPLATE, '', $args['template']);
        $args['templateid'] = empty($args['template']) ? 'clipdefault' : $args['template'];

        //// Publication processing
        // process a new or existing pub, and it's available actions
        if ($args['id']) {
            $pubdata = ModUtil::apiFunc('Clip', 'user', 'get', array(
                'tid'           => $args['tid'],
                'id'            => $args['id'],
                'templateid'    => $args['templateid'],
                'checkperm'     => true,
                'handleplugins' => false, // do not interfer with selected values on edit form
                'loadworkflow'  => false
            ));

            // validate the publication
            if (!$pubdata) {
                LogUtil::registerError($this->__f('Error! No such publication [%1$s - %2$s] found.', array($args['tid'], $args['id'])));

                return $this->redirect(Clip_Util::url($args['tid'], 'list'));
            }
        } else {
            // initial values
            $classname = 'ClipModels_Pubdata'.$args['tid'];
            $pubdata = new $classname();
        }

        // get the workflow state
        $workflow = new Clip_Workflow($pubtype, $pubdata);

        $args['state'] = $workflow->getWorkflow('state');

        //// Security
        $this->throwForbiddenUnless(Clip_Access::toPub($pubtype, $pubdata, null, 'form', $args['templateid']));

        // notify the publication data
        $pubdata = Clip_Event::notify('data.edit', $pubdata)->getData();

        //// Form Handler
        // no security check needed, will be done by the workflow actions for the state
        $handler = new Clip_Form_Handler_User_Pubedit();

        // setup the form handler
        $handler->ClipSetUp($pubdata, $workflow, $pubtype, $pubfields, $args);

        //// Output
        // create the output object
        $render = Clip_Util::newForm($this, true);

        // compatibility prefilter for the 0.9 series only
        $render->load_filter('pre', 'clip_form_compat');

        // store the arguments used
        Clip_Util::setArgs('edit', $args);

        // register clip_util
        Clip_Util::register_utilities($render);

        $render->assign('clipvalues', $clipvalues)
               ->assign('clipargs',   Clip_Util::getArgs())
               ->assign('pubtype',    $pubtype);

        // notify the ui edition
        $render = Clip_Event::notify('ui.edit', $render, $args)->getData();

        // alert pubtype admins only
        $alert = $this->getVar('devmode', false) && Clip_Access::toPubtype($pubtype);

        // resolve the template to use
        // 1. custom template
        if (!empty($args['template'])) {
            $template = $pubtype['folder']."/form_{$args['state']}_{$args['template']}.tpl";

            if ($render->template_exists($template)) {
                return $render->execute($template, $handler);

            } elseif ($alert) {
                LogUtil::registerStatus($this->__f('Notice: Template [%s] not found.', $template));
            }
        }

        // 2. individual state
        $template = $pubtype['folder']."/form_{$args['state']}.tpl";

        if ($render->template_exists($template)) {
            return $render->execute($template, $handler);

        } elseif ($alert) {
            LogUtil::registerStatus($this->__f('Notice: Template [%s] not found.', $template));
        }

        // 3. generic edit
        $template = $pubtype['folder'].'/form_all.tpl';

        if (!$render->template_exists($template)) {
            // auto-generate it only on development mode
            if (!$this->getVar('devmode', false)) {
                return LogUtil::registerError($this->__('This page cannot be displayed. Please contact the administrator.'));
            }

            if ($alert) {
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
        $pub = Doctrine_Core::getTable('ClipModels_Pubdata'.$args['tid'])->find($args['id']);

        if (!$pub) {
            return LogUtil::registerError($this->__f('Error! No such publication [%s] found.', $args['id']));
        }

        // load the publication values and workflow
        $pub->clipPostRead()
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
        $displayUrl = Clip_Util::url($pub, 'display');

        $returnurl = System::serverGetVar('HTTP_REFERER', $displayUrl);

        switch ($args['goto'])
        {
            case 'stepmode':
                $goto = Clip_Util::url($pub, 'edit', array('goto' => 'stepmode'));
                break;

            case 'edit':
                $goto = Clip_Util::url($pub, 'edit');
                break;

            case 'list':
                $goto = Clip_Util::url($args['tid'], 'list');
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
