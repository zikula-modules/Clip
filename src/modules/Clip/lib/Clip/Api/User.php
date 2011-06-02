<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Api
 */

/**
 * User Model.
 */
class Clip_Api_User extends Zikula_AbstractApi
{
    /**
     * Returns a Publication List.
     *
     * @param integer $args['tid']           ID of the publication type.
     * @param string  $args['filter']        Filter string.
     * @param string  $args['orderby']       OrderBy string.
     * @param integer $args['startnum']      Offset to start from.
     * @param integer $args['itemsperpage']  Number of items to retrieve.
     * @param string  $args['countmode']     Mode: no (list without count - default), just (count elements only), both.
     * @param boolean $args['checkperm']     Whether to check the permissions.
     * @param boolean $args['handleplugins'] Whether to parse the plugin fields.
     * @param boolean $args['loadworkflow']  Whether to add the workflow information.
     *
     * @return array Collection of publications and/or Count.
     */
    public function getall($args)
    {
        //// Validation
        if (!isset($args['tid'])) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'tid'));
        }
        if (!Clip_Util::validateTid($args['tid'])) {
            return LogUtil::registerError($this->__f('Error! Invalid publication type ID passed [%s].', DataUtil::formatForDisplay($args['tid'])));
        }

        $pubfields = Clip_Util::getPubFields($args['tid']);

        if (!$pubfields) {
            return LogUtil::registerError($this->__('Error! No publication fields found.'));
        }

        $pubtype = Clip_Util::getPubType($args['tid'])->mapTitleField();

        //// Parameters
        // old parameters (will be removed on Clip 1.0)
        $args['checkPerm']     = isset($args['checkPerm']) ? (bool)$args['checkPerm'] : true;
        $args['handlePluginF'] = isset($args['handlePluginFields']) ? (bool)$args['handlePluginFields'] : true;
        $args['getApprovalS']  = isset($args['getApprovalState']) ? (bool)$args['getApprovalState'] : false;
        // define the arguments
        $args = array(
            'tid'           => (int)$args['tid'],
            'filter'        => isset($args['filter']) ? $args['filter'] : null,
            'orderby'       => isset($args['orderby']) ? $args['orderby'] : null,
            'startnum'      => (isset($args['startnum']) && is_numeric($args['startnum'])) ? (int)abs($args['startnum']) : 1,
            'itemsperpage'  => (isset($args['itemsperpage']) && is_numeric($args['itemsperpage'])) ? (int)abs($args['itemsperpage']) : 0,
            'countmode'     => (isset($args['countmode']) && in_array($args['countmode'], array('no', 'just', 'both'))) ? $args['countmode'] : 'no',
            'checkperm'     => isset($args['checkperm']) ? (bool)$args['checkperm'] : $args['checkPerm'],
            'handleplugins' => isset($args['handleplugins']) ? (bool)$args['handleplugins'] : $args['handlePluginF'],
            'loadworkflow'  => isset($args['loadworkflow']) ? (bool)$args['loadworkflow'] : $args['getApprovalS']
        );

        if (!$args['itemsperpage']) {
            $args['itemsperpage'] = $pubtype['itemsperpage'] > 0 ? $pubtype['itemsperpage'] : $this->getVar('maxperpage', 100);
        }

        //// Security
        if ($args['checkperm'] && !Clip_Access::toPubtype($args['tid'], 'list')) {
            return LogUtil::registerPermissionError();
        }

        // mode check
        // FIXME SECURITY enrich with all the possibilities
        $args['admin'] = (isset($args['admin']) && $args['admin']) || Clip_Access::toPubtype($args['tid'], 'editor');

        $tableObj = Doctrine_Core::getTable('Clip_Model_Pubdata'.$args['tid']);

        //// Misc values
        // set the order
        // handling column names till the end
        if (empty($args['orderby'])) {
            if (!empty($pubtype['sortfield1'])) {
                if ($pubtype['sortdesc1'] == 1) {
                    $args['orderby'] = $pubtype['sortfield1'].' DESC ';
                } else {
                    $args['orderby'] = $pubtype['sortfield1'].' ASC ';
                }

                if (!empty($pubtype['sortfield2'])) {
                    if ($pubtype['sortdesc2'] == 1) {
                        $args['orderby'] .= ', '.$pubtype['sortfield2'].' DESC ';
                    } else {
                        $args['orderby'] .= ', '.$pubtype['sortfield2'].' ASC ';
                    }
                }

                if (!empty($pubtype['sortfield3'])) {
                    if ($pubtype['sortdesc3'] == 1) {
                        $args['orderby'] .= ', '.$pubtype['sortfield3'].' DESC ';
                    } else {
                        $args['orderby'] .= ', '.$pubtype['sortfield3'].' ASC ';
                    }
                }
            } else {
                $args['orderby'] = 'core_publishdate DESC';
            }
        } else {
            $args['orderby'] = Clip_Util::createOrderBy($args['orderby']);
        }

        //// Query setup
        $args['queryalias'] = "pub_{$args['tid']}";
        $query = $tableObj->createQuery($args['queryalias']);

        //// Filter
        // resolve the FilterUtil arguments
        $filter['args'] = array(
            'alias'   => $args['queryalias'],
            'plugins' => array()
        );
        foreach ($pubfields as $fieldname => $field)
        {
            $plugin = Clip_Util_Plugins::get($field['fieldplugin']);

            // includes any filter default class
            if (isset($plugin->filterClass)) {
                $filter['args']['plugins'][$plugin->filterClass]['fields'][] = $fieldname;
            }

            // includes the user operator restriction if it's an UID
            if ($field['isuid']) {
                $filter['args']['restrictions'][$fieldname][] = 'user';
                $filter['args']['plugins']['clipuser']['fields'][] = $fieldname;
            }

            // process the query
            if (method_exists($plugin, 'processQuery')) {
                $plugin->processQuery($query, $field, $args);
            }
        }

        // filter instance
        $filter['obj'] = new FilterUtil('Clip', $tableObj, $filter['args']);

        if (!empty($args['filter'])) {
            $filter['obj']->setFilter($args['filter']);
        } elseif (!empty($pubtype['defaultfilter'])) {
            $filter['obj']->setFilter($pubtype['defaultfilter']);
        }

        //// Relations
        // filters will be limited to the loaded relations
        $args['rel'] = $pubtype['config']['view'];

        if ($args['rel']['load']) {
            // adds the relations data
            $record = $tableObj->getRecordInstance();
            foreach ($record->getRelations($args['rel']['onlyown']) as $ralias => $rinfo) {
                // load the relation if it means to load ONE related record only
                if (($rinfo['own'] && $rinfo['type'] % 2 == 0) || (!$rinfo['own'] && $rinfo['type'] < 2)) {
                    $query->leftJoin("{$args['queryalias']}.{$ralias}");
                }
            }
        }

        // add the conditions to the query
        $uid = UserUtil::getVar('uid');

        if (!$args['admin']) {
            if ($uid && $pubtype['enableeditown'] == 1) {
                $query->andWhere('core_author = ? OR core_showinlist = ?', array($uid, 1));
            } else {
                $query->andWhere('core_showinlist = ?', 1);
            }
            $query->andWhere('core_indepot = ?', 0);
            $query->andWhere('(core_language = ? OR core_language = ?)', array('', ZLanguage::getLanguageCode()));
            $query->andWhere('(core_publishdate <= ? OR core_publishdate IS NULL)', new Doctrine_Expression('NOW()'));
            $query->andWhere('(core_expiredate >= ? OR core_expiredate IS NULL)', new Doctrine_Expression('NOW()'));
        }

        // enrich the query with the Filterutil stuff
        $filter['obj']->enrichQuery($query);

        // fill $args.filter with the final filter used
        $args['filter'] = array();
        foreach ($filter['obj']->getObject() as $f1) {
            foreach ($f1 as $farray) {
                $args['filter'][$farray['field']]['ops'][] = $farray['op'];
                $args['filter'][$farray['field']][$farray['op']][] = $farray['value'];
            }
        }

        //// Count
        if ($args['countmode'] != 'no') {
            $pubcount = $query->count();
        }

        //// Collection
        if ($args['countmode'] != 'just') {
            //// Order by
            // map the unprocessed orderby to the pubtype
            $pubtype->mapValue('orderby', $args['orderby']);
            // replaces the core_title alias by the original field name
            if (strpos($args['orderby'], 'core_title') !== false) {
                $args['orderby'] = str_replace('core_title', $pubtype['titlefield'], $args['orderby']);
            }
            // check if some plugin specific orderby has to be done
            $args['orderby'] = Clip_Util_Plugins::handleOrderBy($args['orderby'], $pubfields, $args['queryalias'].'.');

            // add the orderby to the query
            foreach (explode(', ', $args['orderby']) as $orderby) {
                $query->orderBy($orderby);
            }

            //// Offset and limit
            if ($args['startnum']-1 > 0) {
                $query->offset($args['startnum']-1);
            }

            if ($args['itemsperpage'] > 0) {
                $query->limit($args['itemsperpage']);
            }

            //// execution and postprocess
            $publist = $query->execute();

            for ($i = 0; $i < count($publist); $i++) {
                // FIXME SECURITY individual permission check here and fetch additional ones?
                $publist[$i]->clipProcess($args);
            }
        }

        //// Result
        // store the arguments used
        Clip_Util::setArgs('getallapi', $args);

        return array (
            'publist'  => isset($publist) ? $publist : null,
            'pubcount' => isset($pubcount) ? $pubcount : null
        );
    }

    /**
     * Returns a Publication.
     *
     * @param integer $args['tid']           ID of the publication type.
     * @param integer $args['pid']           ID of the publication.
     * @param integer $args['id']            ID of the publication revision (optional if pid is used).
     * @param boolean $args['checkperm']     Whether to check the permissions.
     * @param boolean $args['handleplugins'] Whether to parse the plugin fields.
     * @param boolean $args['loadworkflow']  Whether to add the workflow information.
     *
     * @return Doctrine_Record One publication.
     */
    public function get($args)
    {
        //// Validation
        if (!isset($args['tid'])) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'tid'));
        }
        if (!Clip_Util::validateTid($args['tid'])) {
            return LogUtil::registerError($this->__f('Error! Invalid publication type ID passed [%s].', DataUtil::formatForDisplay($args['tid'])));
        }
        if (!isset($args['id']) && !isset($args['pid'])) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'id | pid'));
        }

        //// Parameters
        // old parameters (will be removed on Clip 1.0)
        $args['checkPerm']     = isset($args['checkPerm']) ? (bool)$args['checkPerm'] : false;
        $args['handlePluginF'] = isset($args['handlePluginFields']) ? (bool)$args['handlePluginFields'] : true;
        $args['getApprovalS']  = isset($args['getApprovalState']) ? (bool)$args['getApprovalState'] : false;
        // define the arguments
        $args = array(
            'tid'           => (int)$args['tid'],
            'pid'           => isset($args['pid']) ? (int)$args['pid'] : null,
            'by'            => isset($args['by']) ? $args['by'] : 'core_revision', // for pubtype editors and a pid only
            'id'            => isset($args['id']) ? (int)$args['id'] : null,
            'checkperm'     => isset($args['checkperm']) ? (bool)$args['checkperm'] : $args['checkPerm'],
            'templateid'    => isset($args['templateid']) ? $args['templateid'] : '', // for perm check
            'handleplugins' => isset($args['handleplugins']) ? (bool)$args['handleplugins'] : $args['handlePluginF'],
            'loadworkflow'  => isset($args['loadworkflow']) ? (bool)$args['loadworkflow'] : $args['getApprovalS']
        );

        //// Misc values
        $pubtype = Clip_Util::getPubType($args['tid']);

        $tableObj = Doctrine_Core::getTable('Clip_Model_Pubdata'.$args['tid']);

        //// Query setup
        $args['queryalias'] = "pub_{$args['tid']}_"
                              .($args['pid'] ? $args['pid'] : '')
                              .($args['id'] ? '_'.$args['id'] : '');

        $uid   = UserUtil::getVar('uid');
        $query = $tableObj->createQuery($args['queryalias']);

        // add the conditions to the query
        if (!Clip_Access::toPubtype($args['tid'], 'editor'))
        {
            if (!empty($uid) && $pubtype['enableeditown'] == 1) {
                $query->andWhere('(core_author = ? OR core_online = ?)', array($uid, 1));
            } else {
                $query->andWhere('core_online = ?', 1);
            }
            $query->andWhere('core_indepot = ?', 0);
            $query->andWhere('(core_language = ? OR core_language = ?)', array('', ZLanguage::getLanguageCode()));
            $query->andWhere('(core_publishdate <= ? OR core_publishdate IS NULL)', new Doctrine_Expression('NOW()'));
            $query->andWhere('(core_expiredate >= ? OR core_expiredate IS NULL)', new Doctrine_Expression('NOW()'));

            if (empty($args['id'])) {
                $query->andWhere('core_pid = ?', $args['pid']);
            } else {
                $query->andWhere('id = ?', $args['id']);
            }
        } else {
            if (!empty($args['id'])) {
                $query->where('id = ?', $args['id']);
            } else {
                $query->where('core_pid = ?', $args['pid'])
                      ->orderBy($args['by']);
            }
        }

        //// Relations
        $args['rel'] = $pubtype['config']['display'];

        // adds the relations data
        if ($args['rel']['load']) {
            $record = $tableObj->getRecordInstance();
            foreach ($record->getRelations($args['rel']['onlyown']) as $ralias => $rinfo) {
                // load the relation if it means to load ONE related record only
                if (($rinfo['own'] && $rinfo['type'] % 2 == 0) || (!$rinfo['own'] && $rinfo['type'] < 2)) {
                    $query->leftJoin("{$args['queryalias']}.{$ralias}");
                }
            }
        }

        // fetch the publication
        $pubdata = $query->fetchOne();

        if (!$pubdata) {
            return false;
        }

        //// Security
        // check permissions if needed
        if ($args['checkperm'] && !Clip_Access::toPub($args['tid'], $pubdata, null, ACCESS_READ, null, 'display', $args['templateid'])) {
            return LogUtil::registerPermissionError();
        }

        //// Result
        // postprocess the record and related records depending on the call arguments
        $pubdata->clipProcess($args);

        // store the arguments used
        Clip_Util::setArgs('getapi', $args);

        return $pubdata;
    }

    /**
     * Saves a new or existing publication.
     *
     * @param array               $args['data']        Publication data.
     * @param string              $args['commandName'] Command name has to be a valid workflow action for the currenct state.
     * @param Doctrine_Collection $args['pubfields']   Collection of pubfields (optional).
     *
     * @return boolean True on success, false otherwise.
     */
    public function edit($args)
    {
        //// Validation
        if (!isset($args['data'])) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'data'));
        }
        if (!isset($args['commandName'])) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'commandName').' '.$this->__('commandName has to be a valid workflow action for the current state.'));
        }

        //// Execution
        // assign for easy handling of the data
        $obj = $args['data'];

        // create the workflow and executes the action
        $pubtype  = Clip_Util::getPubType($obj['core_tid']);
        $workflow = new Clip_Workflow($pubtype, $obj);

        $ret = $workflow->executeAction($args['commandName']);

        // checks for a failure
        if ($ret === false) {
            return LogUtil::hasErrors() ? false : LogUtil::registerError($this->__('Unknown workflow action error. Operation failed.'));
        }

        $obj->mapValue('core_operations', $ret);

        return $obj;
    }

    /**
     * Returns pid.
     *
     * @param int $args['tid']
     * @param int $args['id']
     *
     * @return int pid.
     */
    public function getPid($args)
    {
        //// Validation
        if (!isset($args['tid'])) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'tid'));
        }
        if (!isset($args['id'])) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'id'));
        }

        //// Result
        return Doctrine_Core::getTable('Clip_Model_Pubdata'.$args['tid'])
               ->selectFieldBy('core_pid', $args['id'], 'id');
    }

    /**
     * Returns the ID of the online publication.
     *
     * @param int $args['tid']
     * @param int $args['pid']
     * @param bool $args['lastrev']
     *
     * @return int id.
     */
    public function getId($args)
    {
        //// Validation
        if (!isset($args['tid']) || !is_numeric($args['tid'])) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'tid'));
        }
        if (!isset($args['pid']) || !is_numeric($args['pid'])) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'pid'));
        }
        $args['lastrev'] = isset($args['lastrev']) ? (bool)$args['lastrev'] : false;

        //// Execution
        $tbl = Doctrine_Core::getTable('Clip_Model_Pubdata'.$args['tid']);

        // checks for a online pub first
        $where = array(
                     array('core_pid = ? AND core_online = ?', array($args['pid'], 1))
                 );

        $id = $tbl->selectField('id', $where);

        // checks for the last revision if asked for
        if ($args['lastrev'] && !$id) {
            $where = array(
                         array('core_pid = ?', $args['pid'])
                     );

            $id = $tbl->selectField('id', $where, 'core_revision');
        }

        return $id;
    }

    /**
     * Form custom url string.
     *
     * @param array $args Arguments given by ModUtil::url.
     *
     * @return string Custom URL string.
     */
    public function encodeurl($args)
    {
        if (!isset($args['modname']) || !isset($args['func']) || !isset($args['args'])) {
            return LogUtil::registerArgsError();
        }

        static $cache = array();

        $supportedfunctions = array('main', 'list', 'display', 'edit', 'view', 'publist', 'viewpub', 'pubedit');
        if (!in_array($args['func'], $supportedfunctions)) {
            return false;
        }

        // deprecated function transition
        if ($args['func'] == 'pubedit') {
            $args['func'] = 'edit';
        }

        $pubtypeTitle = '';
        if (!isset($args['args']['tid'])) {
            return false;
        } else {
            $tid          = (int)$args['args']['tid'];
            $pubtype      = Clip_Util::getPubType($tid);
            $pubtypeTitle = $pubtype['urltitle'];

            unset($args['args']['tid']);
        }

        $pubTitle = '';
        if (isset($args['args']['pid']) || isset($args['args']['id'])) {
            if (!isset($args['args']['pid']) && !isset($args['args']['id'])){
                return false;
            }
            if (isset($args['args']['pid'])) {
                $pid = (int)$args['args']['pid'];
                unset($args['args']['pid']);
            }
            if (isset($args['args']['id'])) {
                $id = (int)$args['args']['id'];
                unset($args['args']['id']);
                if (!isset($pid)) {
                    if (!isset($cache['id'][$id])) {
                        $pid = $cache['id'][$id] = Doctrine_Core::getTable('Clip_Model_Pubdata'.$tid)
                                                   ->selectFieldBy('core_pid', $id, 'id');
                    } else {
                        $pid = $cache['id'][$id];
                    }
                }
            }

            if (isset($cache['title'][$tid][$pid])) {
                $pubTitle = $cache['title'][$tid][$pid];
            } elseif (isset($args['args']['title']) && !empty($args['args']['title'])) {
                $pubTitle = $args['args']['title'];
            } else {
                $pubTitle = Doctrine_Core::getTable('Clip_Model_Pubdata'.$tid)
                            ->selectFieldBy(Clip_Util::getTitleField($tid), $pid, 'core_pid');
                $pubTitle = DataUtil::formatPermalink($pubTitle);
            }
            if (isset($args['args']['title'])) {
                unset($args['args']['title']);
            }
            $cache['title'][$tid][$pid] = $pubTitle;

            $pubTitle = '/'.$pubTitle.'.'.$pid;

            if (isset($id)) {
                $pubTitle .= '.'.$id;
            }
        }

        $params = '';

        // special function indicator
        if ($args['func'] == 'edit') {
            $params .= '/' . ($pubTitle ? $this->__('edit') : $this->__('submit'));
        }

        if (count($args['args']) > 0) {
            $paramarray = array();
            foreach ($args['args'] as $k => $v) {
                $paramarray[] = urlencode($k).'/'.urlencode($v);
            }
            $params .= '/'. implode('/', $paramarray);
        }

        return $args['modname'].'/'.$pubtypeTitle.$pubTitle.$params;
    }

    /**
     * Decode custom url string.
     *
     * @param array $args Arguments given by Core::init.
     *
     * @return bool true if succeded false otherwise.
     */
    public function decodeurl($args)
    {
        $_ = $args['vars'];

        $functions = array('exec', 'executecommand');

        $argsnum   = count($_);
        if (!isset($_[2]) || empty($_[2])) {
            System::queryStringSetVar('func', 'main');
            return true;
        }

        if (in_array($_[2], $functions)) {
            return false;
        }

        $nextvar = 2;

        $tid = Doctrine_Core::getTable('Clip_Model_Pubtype')
               ->selectFieldBy('tid', $_[$nextvar], 'urltitle');

        if (!$tid) {
            return false;
        } else {
            System::queryStringSetVar('func', 'list');
            System::queryStringSetVar('tid', $tid);
            $nextvar++;
        }

        $shortedits = array($this->__('edit'), $this->__('submit'));

        if (isset($_[$nextvar]) && !empty($_[$nextvar])) {
            if (in_array($_[$nextvar], $shortedits)) {
                // special function indicator check
                System::queryStringSetVar('func', 'edit');
                $nextvar++;

            } else {
                // publication url check
                $permalinksseparator = System::getVar('shorturlsseparator');
                $match = '';
                $isPub = (bool) preg_match('~^[a-z0-9_'.$permalinksseparator.']+\.(\d+)+(\.(\d+))?+$~i', $_[$nextvar], $match);
                if ($isPub) {
                    System::queryStringSetVar('func', 'display');
                    System::queryStringSetVar('pid', $match[1]);
                    if (isset($match[3])) {
                        System::queryStringSetVar('id', $match[3]);
                    }
                    $nextvar++;
                }
            }
        }

        // special function indicator check
        if (isset($_[$nextvar]) && in_array($_[$nextvar], $shortedits)) {
            System::queryStringSetVar('func', 'edit');
            $nextvar++;
        }

        if (isset($_[$nextvar]) && !empty($_[$nextvar])) {
            for ($i = $nextvar; $i < $argsnum; $i+=2) {
                System::queryStringSetVar($_[$i], $_[$i+1]);
            }
        }

        return true;
    }

    /**
     * @see Clip_Api_User::getall
     *
     * @deprecated 0.9
     */
    public function pubList($args)
    {
        return $this->getall($args);
    }

    /**
     * @see Clip_Api_User::get
     *
     * @deprecated 0.9
     */
    public function getPub($args)
    {
        return $this->get($args);
    }

    /**
     * @see Clip_Api_User::edit
     *
     * @deprecated 0.9
     */
    public function editPub($args)
    {
        return $this->edit($args);
    }
}
