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
     * @param array   $args['where']         Direct where conditions to the query.
     * @param string  $args['filter']        Filter string.
     * @param string  $args['distinct']      Distinct field(s) to select.
     * @param string  $args['function']      Function(s) to perform.
     * @param string  $args['groupby']       GroupBy field.
     * @param string  $args['orderby']       OrderBy string.
     * @param integer $args['startnum']      Offset to start from.
     * @param integer $args['itemsperpage']  Number of items to retrieve.
     * @param string  $args['countmode']     Mode: no (list without count - default), just (count elements only), both.
     * @param boolean $args['array']         Whether to fetch the resulting publications as array (default: false).
     * @param boolean $args['checkperm']     Whether to check the permissions.
     * @param boolean $args['handleplugins'] Whether to parse the plugin fields.
     * @param boolean $args['loadworkflow']  Whether to add the workflow information.
     * @param array   $args['rel']           Relation configuration flags to use {load, onlyown, processrefs, checkperm, handleplugins, loadworkflow}.
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

        $pubtype = Clip_Util::getPubType($args['tid']);

        //// Parameters
        // old parameters (will be removed on Clip 1.0)
        $args['checkPerm']     = isset($args['checkPerm']) ? (bool)$args['checkPerm'] : true;
        $args['handlePluginF'] = isset($args['handlePluginFields']) ? (bool)$args['handlePluginFields'] : true;
        $args['getApprovalS']  = isset($args['getApprovalState']) ? (bool)$args['getApprovalState'] : false;
        // define the arguments
        $args = array(
            'tid'           => (int)$args['tid'],
            'where'         => isset($args['where']) ? $args['where'] : array(),
            'filter'        => isset($args['filter']) ? $args['filter'] : null,
            'distinct'      => isset($args['distinct']) ? $args['distinct'] : null,
            'function'      => isset($args['function']) ? $args['function'] : null,
            'groupby'       => isset($args['groupby']) ? $args['groupby'] : null,
            'orderby'       => isset($args['orderby']) ? $args['orderby'] : null,
            'startnum'      => (isset($args['startnum']) && is_numeric($args['startnum'])) ? (int)abs($args['startnum']) : 1,
            'itemsperpage'  => (isset($args['itemsperpage']) && is_numeric($args['itemsperpage'])) ? (int)abs($args['itemsperpage']) : 0,
            'countmode'     => (isset($args['countmode']) && in_array($args['countmode'], array('no', 'just', 'both'))) ? $args['countmode'] : 'no',
            'array'         => isset($args['array']) ? (bool)$args['array'] : false,
            'limitdate'     => isset($args['limitdate']) ? (bool)$args['limitdate'] : !Clip_Access::toPubtype($args['tid'], 'editor'),
            'checkperm'     => isset($args['checkperm']) ? (bool)$args['checkperm'] : $args['checkPerm'],
            'handleplugins' => isset($args['handleplugins']) ? (bool)$args['handleplugins'] : $args['handlePluginF'],
            'loadworkflow'  => isset($args['loadworkflow']) ? (bool)$args['loadworkflow'] : $args['getApprovalS'],
            'rel'           => isset($args['rel']) ? $args['rel'] : null
        );

        if (!$args['itemsperpage']) {
            $args['itemsperpage'] = $pubtype['itemsperpage'] > 0 ? $pubtype['itemsperpage'] : $this->getVar('maxperpage', 100);
        }

        //// Security
        if ($args['checkperm'] && !Clip_Access::toPubtype($args['tid'], 'list')) {
            return false;
        }

        //// Misc values
        // utility vars
        $tableObj  = Doctrine_Core::getTable('ClipModels_Pubdata'.$args['tid']);
        $record    = $tableObj->getRecordInstance();
        $relfields = $record->getRelationFields();

        // set the order
        // handling column names till the end
        if (empty($args['orderby'])) {
            $args['orderby'] = array();
            if (!empty($pubtype['sortfield1']) && $record->contains($pubtype['sortfield1'])) {
                $args['orderby'][] = $pubtype['sortfield1'].($pubtype['sortdesc1'] == 1 ? ' DESC' : ' ASC');
            }
            if (!empty($pubtype['sortfield2']) && $record->contains($pubtype['sortfield2'])) {
                $args['orderby'][] = $pubtype['sortfield2'].($pubtype['sortdesc2'] == 1 ? ' DESC' : ' ASC');
            }
            if (!empty($pubtype['sortfield3']) && $record->contains($pubtype['sortfield3'])) {
                $args['orderby'][] = $pubtype['sortfield3'].($pubtype['sortfield3'] == 1 ? ' DESC' : ' ASC');
            }
            $args['orderby'] = implode(', ', $args['orderby']);

            if (empty($args['orderby'])) {
                $args['orderby'] = 'core_publishdate DESC';
            }
        } else {
            $args['orderby'] = Clip_Util::createOrderBy($args['orderby'], $relfields);
        }

        //// Query setup
        $args['queryalias'] = $queryalias = "pub_{$args['tid']}";

        if (!$args['distinct'] && !$args['function']) {
            $queryalias = "{$args['queryalias']} INDEXBY {$args['queryalias']}.id";
        }

        $query = $tableObj->createQuery($queryalias);

        if ($args['distinct']) {
            $distinct = explode(',', $args['distinct']);
            foreach ($distinct as $k => $v) {
                if (isset($relfields[$v])) {
                    $distinct[$k] = "{$relfields[$v]} as $v";
                } else {
                    $distinct[$k] = "$v as $v";
                }
            }
            $distinct = implode(',', $distinct);

            $query->select("DISTINCT $distinct");

        } elseif ($args['function']) {
            $function = explode(',', $args['function']);
            foreach ($function as $k => $v) {
                $v = explode(':', $v);
                $field = isset($relfields[$v[0]]) ? $relfields[$v[0]] : $v[0];
                $func  = isset($v[1]) ? strtoupper($v[1]) : 'COUNT';
                if (!in_array($func, array('MIN', 'MAX', 'SUM', 'COUNT'))) {
                    return LogUtil::registerError($this->__('Error! Invalid function passed.'));
                }
                $query->addSelect("$func($field) AS ".strtolower("{$v[0]}_{$func}"));
            }
        }

        if ($args['groupby']) {
            $query->groupBy($args['groupby']);
        }

        //// Filter
        // resolve the FilterUtil arguments
        $filter['args'] = array(
            'alias'   => $args['queryalias'],
            'plugins' => array()
        );
        foreach ($pubfields as $fieldname => $field)
        {
            $plugin = Clip_Util_Plugins::get($field['fieldplugin']);

            // process the filter args
            if (method_exists($plugin, 'enrichFilterArgs')) {
                $plugin->enrichFilterArgs($filter['args'], $field, $args);
            }

            // enrich the query
            if (method_exists($plugin, 'enrichQuery')) {
                $plugin->enrichQuery($query, $field, $args);
            }
        }

        // filter instance
        $filter['obj'] = new FilterUtil('Clip', $tableObj, $filter['args']);

        if (!empty($args['filter'])) {
            $filter['obj']->setFilter($args['filter']);
        } elseif (!$filter['obj']->getFilter() && !empty($pubtype['defaultfilter'])) {
            $filter['obj']->setFilter($pubtype['defaultfilter']);
        }
        if (empty($args['filter']) && !empty($pubtype['fixedfilter'])) {
            $filter['obj']->andFilter($pubtype['fixedfilter']);
        }

        //// Relations
        // filters will be limited to the loaded relations
        $args['rel'] = isset($args['rel']) ? Clip_Util::getPubtypeConfig('list', $args['rel']) : array();

        if ($args['rel'] && $args['rel']['load']) {
            // adds the relations data
            foreach ($record->getRelations($args['rel']['onlyown']) as $ralias => $rinfo) {
                // load the relation if it means to load ONE related record only
                if (($rinfo['own'] && $rinfo['type'] % 2 == 0) || (!$rinfo['own'] && $rinfo['type'] < 2)) {
                    $query->leftJoin("{$args['queryalias']}.{$ralias}");
                }
            }
        }

        // add the conditions to the query
        // restrictions for non-editors
        if ($args['limitdate']) {
            $query->andWhere('(core_publishdate IS NULL OR core_publishdate <= ?)', date('Y-m-d H:i:s', time()) /*new Doctrine_Expression('NOW()')*/);
            $query->andWhere('(core_expiredate IS NULL OR core_expiredate >= ?)', date('Y-m-d H:i:s', time()) /*new Doctrine_Expression('NOW()')*/);
        }
        // query for the current user language
        $query->andWhere('(core_language = ? OR core_language = ?)', array(ZLanguage::getLanguageCode(), ''));
        // additional call specifications
        foreach ($args['where'] as $method => $condition) {
            if (is_numeric($method)) {
                $method = 'andWhere';
            }
            if (is_array($condition)) {
                $query->$method($condition[0], $condition[1]);
            } else {
                $query->$method($condition);
            }
        }

        // enrich the query with the Filterutil stuff
        $filter['obj']->enrichQuery($query);

        // fill $args.filter with the final filter used
        $args['filter'] = array();
        foreach ($filter['obj']->getObject() as $f1) {
            if (isset($f1['field'])) {
                $args['filter'][$f1['field']]['ops'][] = $f1['op'];
                $args['filter'][$f1['field']][$f1['op']][] = $f1['value'];
            } else {
                foreach ($f1 as $farray) {
                    $args['filter'][$farray['field']]['ops'][] = $farray['op'];
                    $args['filter'][$farray['field']][$farray['op']][] = $farray['value'];
                }
            }
        }
        $filterstr = $filter['obj']->getFilter();
        $filterstr = strpos($filterstr, '(') === 0 ? substr($filterstr, 1, -1) : $filterstr;
        $args['filterstr'] = explode(')*(', $filterstr);

        if ($args['function']) {
            $publist = $query->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);

            // remove the posthydrated core values
            foreach ($publist as $k => $v) {
                if (strpos($k, 'core_') === 0) {
                    unset($publist[$k]);
                }
            }

            $publist = (count($publist) == 1) ? reset($publist) : $publist;

        } else {
            //// Count
            if ($args['countmode'] != 'no') {
                $pubcount = $query->count();
            }

            //// Collection
            if ($args['countmode'] != 'just') {
                //// Order by
                // replaces the core_title alias by the original field name
                if (strpos($args['orderby'], 'core_title') !== false) {
                    $args['orderby'] = str_replace('core_title', $pubtype->getTitleField(), $args['orderby']);
                }
                // check if some plugin specific orderby has to be done
                $args['orderby'] = Clip_Util_Plugins::handleOrderBy($args['orderby'], $pubfields, $args['queryalias'].'.');

                // add the orderby to the query
                if ($args['orderby']) {
                    $query->orderBy($args['orderby']);
                }

                //// Offset and limit
                if ($args['startnum']-1 > 0) {
                    $query->offset($args['startnum']-1);
                }

                if ($args['itemsperpage'] > 0) {
                    $query->limit($args['itemsperpage']);
                }

                //// execution and postprocess
                if ($args['distinct']) {
                    // distinct field(s)
                    $publist = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

                    // remove the posthydrated core values
                    foreach ($publist as $j => $res) {
                        foreach ($res as $k => $v) {
                            if (strpos($k, 'core_') === 0) {
                                unset($publist[$j][$k]);
                            }
                        }
                    }

                    if (strpos($args['distinct'], ',') === false) {
                        foreach ($publist as $k => $v) {
                            $publist[$k] = $v[$args['distinct']];
                        }
                    }

                } else {
                    // normal list
                    $publist = $query->execute(array(), $args['array'] ? Doctrine_Core::HYDRATE_ARRAY : Doctrine_Core::HYDRATE_RECORD);

                    foreach ($publist as $i => $pub) {
                        // FIXME fetch additional ones when unset?
                        if (Clip_Access::toPub($pubtype, $pub, null, 'display')) {
                            if (is_object($publist[$i])) {
                                $publist[$i]->clipProcess($args);
                            }
                        } else {
                            unset($publist[$i]);
                        }
                    }

                    // store the arguments used
                    Clip_Util::setArgs('getallapi', $args);
                }
            }
        }

        //// Result
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
     * @param array   $args['where']         Direct where conditions to the query.
     * @param boolean $args['array']         Whether to fetch the resulting publications as array (default: false).
     * @param boolean $args['checkperm']     Whether to check the permissions.
     * @param string  $args['templateid']    Template ID for the permission check.
     * @param boolean $args['handleplugins'] Whether to parse the plugin fields.
     * @param boolean $args['loadworkflow']  Whether to add the workflow information.
     * @param array   $args['rel']           Relation configuration flags to use {load, onlyown, processrefs, checkperm, handleplugins, loadworkflow}.
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
            'id'            => isset($args['id']) ? (int)$args['id'] : null,
            'where'         => isset($args['where']) ? $args['where'] : array(),
            'array'         => isset($args['array']) ? (bool)$args['array'] : false,
            'checkperm'     => isset($args['checkperm']) ? (bool)$args['checkperm'] : $args['checkPerm'],
            'templateid'    => isset($args['templateid']) ? $args['templateid'] : '', // for perm check
            'handleplugins' => isset($args['handleplugins']) ? (bool)$args['handleplugins'] : $args['handlePluginF'],
            'loadworkflow'  => isset($args['loadworkflow']) ? (bool)$args['loadworkflow'] : $args['getApprovalS'],
            'rel'           => isset($args['rel']) ? $args['rel'] : null
        );

        //// Query setup
        $args['queryalias'] = "pub_{$args['tid']}_"
                              .($args['pid'] ? $args['pid'] : '')
                              .($args['id'] ? '_'.$args['id'] : '');

        $tableObj = Doctrine_Core::getTable('ClipModels_Pubdata'.$args['tid']);

        $query = $tableObj->createQuery($args['queryalias']);

        // add the conditions to the query
        if (!empty($args['id'])) {
            $query->where('id = ?', $args['id']);
        } else {
            $query->where('core_pid = ?', $args['pid'])
                  ->orderBy('core_language DESC, core_revision DESC');
        }
        // query for the current user language
        $query->andWhere('(core_language = ? OR core_language = ?)', array(ZLanguage::getLanguageCode(), ''));
        // restrictions for non-editors
        if (!Clip_Access::toPubtype($args['tid'], 'editor')) {
            $query->andWhere('(core_publishdate IS NULL OR core_publishdate <= ?)', date('Y-m-d H:i:s', time()) /*new Doctrine_Expression('NOW()')*/);
            $query->andWhere('(core_expiredate IS NULL OR core_expiredate >= ?)', date('Y-m-d H:i:s', time()) /*new Doctrine_Expression('NOW()')*/);
        }
        // additional call specifications
        foreach ($args['where'] as $method => $condition) {
            if (is_numeric($method)) {
                $method = 'andWhere';
            }
            if (is_array($condition)) {
                $query->$method($condition[0], $condition[1]);
            } else {
                $query->$method($condition);
            }
        }

        //// Relations
        $args['rel'] = isset($args['rel']) ? Clip_Util::getPubtypeConfig('display', $args['rel']) : array();

        // adds the relations data
        if ($args['rel'] && $args['rel']['load']) {
            $record = $tableObj->getRecordInstance();
            foreach ($record->getRelations($args['rel']['onlyown']) as $ralias => $rinfo) {
                // load the relation if it means to load ONE related record only
                if (($rinfo['own'] && $rinfo['type'] % 2 == 0) || (!$rinfo['own'] && $rinfo['type'] < 2)) {
                    $query->leftJoin("{$args['queryalias']}.{$ralias}");
                }
            }
        }

        // fetch the publication
        $pubdata = $query->fetchOne(array(), $args['array'] ? Doctrine_Core::HYDRATE_ARRAY : Doctrine_Core::HYDRATE_RECORD);

        if (!$pubdata) {
            return false;
        }

        //// Security
        // check permissions if needed
        if ($args['checkperm'] && !Clip_Access::toPub($args['tid'], $pubdata, null, 'access', $args['templateid'])) {
            return false;
        }

        //// Result
        // postprocess the record and related records depending on the call arguments
        if (is_object($pubdata)) {
            $pubdata->clipProcess($args);
        }

        // store the arguments used
        Clip_Util::setArgs('getapi', $args);

        return $pubdata;
    }

    /**
     * Saves a new or existing publication.
     *
     * @param array  $args['data']        Publication data.
     * @param string $args['commandName'] Command name has to be a valid workflow action for the currenct state.
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
        return Doctrine_Core::getTable('ClipModels_Pubdata'.$args['tid'])
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
        $tbl = Doctrine_Core::getTable('ClipModels_Pubdata'.$args['tid']);

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
        if (!isset($args['args']['tid']) || !Clip_Util::validateTid($args['args']['tid'])) {
            return false;
        }

        $supportedfunctions = array('main', 'list', 'display', 'edit', 'exec', 'view', 'publist', 'viewpub', 'pubedit', 'executecommand');
        if (!in_array($args['func'], $supportedfunctions)) {
            return false;
        }

        // deprecated functions transition
        // @deprecated 0.9
        switch ($args['func'])
        {
            case 'pubedit':
                $args['func'] = 'edit';
                break;
            case 'view':
            case 'publist':
                $args['func'] = 'list';
                break;
            case 'viewpub':
                $args['func'] = 'display';
                break;
            case 'executecommand':
                $args['func'] = 'exec';
                break;
        }

        // utility assign
        $_ = $args['args'];

        // pubtype id
        $tid      = (int)$_['tid'];
        $tidtitle = Clip_Util::getPubType($tid, 'urltitle');

        // template parameter
        $template = '';
        if (isset($_['template'])) {
            $template = preg_replace(Clip_Util::REGEX_TEMPLATE, '', $_['template']);
            if ($template != $_['template']) {
                // do not build shortURLs for faulty templates
                return false;
            }
            $tpl = $template = $_['template'];
        } else {
            $tpl = $this->getVar('shorturls');
        }

        $tplhtml = $template && $this->getVar('shorturls') ? (in_array($tpl, array('htm', 'html')) ? $tpl : 'htm') : '';

        unset($_['tid'], $_['template']);

        // shortURLs scheme
        // template defaults to modvar - htm
        //  main:    /pubtype[.template]
        //  list:    /pubtype[/filter[/orderby]]/pageX.template]
        //  list:    /pubtype[/filter[/orderby]]/startY.template]
        //  display: /pubtype/pubtitle[.template]
        //  edit:    /pubtype[/template]/submit[.htm[l]]
        //  edit:    /pubtype[/goto/somewhere]/edit[.htm[l]]
        //  edit:    /pubtype[/template/goto/somewhere]/edit[.htm[l]]
        //  edit:    /pubtype/pubtitle[/template]/edit[.htm[l]]
        //  edit:    /pubtype/pubtitle[/goto/somewhere]/edit[.htm[l]]
        //  edit:    /pubtype/pubtitle[/template/goto/somewhere]/edit[.htm[l]]
        //  edit:    /pubtype/pubtitle[/param1/value1/param2/value2]/edit[.htm[l]]
        //  exec:    /pubtype/pubtitle/action/___/csfrtoken/___/exec

        static $cache = array();

        $shorturl = '';

        switch ($args['func'])
        {
            case 'main':
                $shorturl = $tpl ? ".$tpl" : '';

                // adds the parameters
                if (!empty($_)) {
                    foreach ($_ as $k => $v) {
                        $shorturl .= '/'.urlencode($k).'/'.urlencode($v);
                    }
                }
                break;

            case 'list':
                if (isset($_['startnum'])) {
                    $filename = 'start'.(int)$_['startnum'];
                } else {
                    $filename = 'page'.(isset($_['page']) ? (int)$_['page'] : 1);
                }
                unset($_['startnum'], $_['page']);

                // adds the parameters
                if (!empty($_)) {
                    foreach ($_ as $k => $v) {
                        $shorturl .= '/'.urlencode($k).'/'.urlencode($v);
                    }
                }

                $shorturl .= "/$filename" . ($tpl ? ".$tpl" : '');
                break;

            case 'edit':
                // set pid if not set to get into the display logic
                if (!isset($_['pid']) && !isset($_['id'])) {
                    $_['pid'] = 0;
                }

            case 'exec':
            case 'display':
                if (!isset($_['urltitle']) && !isset($_['pid']) && !isset($_['id'])) {
                    return false;
                }

                if (isset($_['pid'])) {
                    $pid = (int)$_['pid'];
                }
                if (isset($_['id'])) {
                    $id = (int)$_['id'];
                    if (!isset($pid)) {
                        if (!isset($cache['id'][$tid][$id])) {
                            $pub = Doctrine_Core::getTable('ClipModels_Pubdata'.$tid)->findOneBy('id', $id);
                            $pid = $cache['id'][$tid][$id] = $pub['core_pid'];
                            $cache['urltitle'][$tid][$pid] = $pub['core_urltitle'];
                        } else {
                            $pid = $cache['id'][$tid][$id];
                        }
                    }
                } elseif (isset($_['urltitle']) && !isset($pid)) {
                    $pid = $cache['id'][$tid][$id] = Doctrine_Core::getTable('ClipModels_Pubdata'.$tid)
                                                     ->selectFieldBy('core_pid', $_['urltitle'], 'core_urltitle', 'core_online DESC');
                    if (!$pid) {
                        return false;
                    }
                }

                if ($pid) {
                    // not submit (pid: 0)
                    if (isset($cache['urltitle'][$tid][$pid])) {
                        $urltitle = $cache['urltitle'][$tid][$pid];
                    } elseif (isset($_['urltitle']) && !empty($_['urltitle'])) {
                        $urltitle = $_['urltitle'];
                    } else {
                        $urltitle = Doctrine_Core::getTable('ClipModels_Pubdata'.$tid)
                                    ->selectFieldBy('core_urltitle', $pid, 'core_pid');
                    }
                    $cache['urltitle'][$tid][$pid] = $urltitle;

                    $urltitle = "/$urltitle" . (isset($id) ? "~$id" : '');
                }

                unset($_['urltitle'], $_['title'], $_['pid'], $_['id']);

                if ($args['func'] == 'display') {
                    $shorturl .= $urltitle . ($tpl ? ".$tpl" : '');
                } else {
                    $shorturl  = ($pid ? $urltitle : '');
                    $shorturl .= ($tpl ? "/$tpl" : '');
                }

                // adds the parameters
                if (!empty($_)) {
                    foreach ($_ as $k => $v) {
                        $shorturl .= '/'.urlencode($k).'/'.urlencode($v);
                    }
                }

                switch ($args['func'])
                {
                    case 'edit':
                        $shorturl .= '/' . ($pid ? 'edit' : 'submit') . ($tplhtml ? ".$tplhtml" : '');
                        break;

                    case 'exec':
                        $shorturl .= '/exec';
                        break;
                }
                break;
        }

        return $args['modname'].'/'.$tidtitle.$shorturl;
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
        // utility assign
        $_ = array_slice($args['vars'], 2);

        if (isset($args[2])) {
            // unsupported function, process it with default shortURLs
            return false;
        }

        if (empty($_)) {
            if ($this->getVar('pubtype')) {
                System::redirect(ModUtil::url('Clip', 'user', 'main', array('tid' => $this->getVar('pubtype'))));
            }
            // no pubtype passed, let the module to process the error
            return true;
        }

        // reset the function to main
        System::queryStringSetVar('func', 'main');

        preg_match('/^([a-z0-9_\-\~]+?)(\.([a-z0-9_\.\-]+))?$/i', end($_), $matches);

        $urltitle = reset($_);
        $filename = $matches[1];
        $template = isset($matches[3]) ? $matches[3] : '';

        // detection of edit mode
        if (in_array($filename, array('submit', 'edit'))) {
            $func = 'edit';
            $pid  = ($filename == 'submit') ? null : 0;

            unset($_[0], $_[count($_)]);

        // action execution
        } elseif ($filename == 'exec') {
            $func = 'exec';
            $pubtitle = $_[1];

            unset($_[count($_)-1], $_[0], $_[1]);

        // list request
        } elseif (preg_match('/^(page|start)/', $filename)) {
            $func = 'list';

            unset($_[0], $_[count($_)]);

        } else {
            // process the possibilities
            if (count($_) % 2 == 1) {
                // if odd, it's a main request
                if (!preg_match('/^([a-z0-9_\-\~]+?)(\.([a-z0-9_\.\-]+))?$/i', $_[0], $matches)) {
                    // there must be a valid filename
                    return true;
                }
                $func = 'main';
                $urltitle = $matches[1];
                $template = isset($matches[3]) ? $matches[3] : '';

                unset($_[0]);

            } else {
                // if even, it includes the publication title
                if (!preg_match('/^([a-z0-9_\-\~]+?)(\.([a-z0-9_\.\-]+))?$/i', $_[1], $matches)) {
                    // there must be a valid filename
                    return true;
                }
                $func = 'display';
                $filename = $matches[1];
                $template = isset($matches[3]) ? $matches[3] : '';

                unset($_[0], $_[1]);
            }
        }

        // pubtype urltitle check
        $tid = Doctrine_Core::getTable('Clip_Model_Pubtype')
               ->selectFieldBy('tid', $urltitle, 'urltitle');

        if (!$tid) {
            // no pubtype match
            return true;
        }

        // process the arguments of each function
        switch ($func)
        {
            case 'list':
                // parse the pager
                if (preg_match('/^page([\d]+)$/', $filename, $matches)) {
                    System::queryStringSetVar('page', $matches[1]);

                } elseif (preg_match('/^start([\d]+)$/', $filename, $matches)) {
                    System::queryStringSetVar('startnum', $matches[1]);

                } else {
                    // no valid number given
                    return true;
                }

            case 'main':
                // additional args
                if (!empty($_)) {
                    $_ = array_values($_);
                    for ($i = 0; $i < floor(count($_)/2); $i++) {
                        System::queryStringSetVar($_[$i*2], $_[$i*2+1]);
                    }
                }
                break;

            case 'edit':
                if (isset($pid)) {
                    // edit: capture and remove the pub title
                    $pubtitle = reset($_);
                    unset($_[key($_)]);
                }

                if (count($_) % 2) {
                    // there's a custom edit template
                    $template = reset($_);
                    unset($_[key($_)]);
                }

            case 'exec':
            case 'display':
                if (!empty($_)) {
                    $_ = array_values($_);
                    for ($i = 0; $i < floor(count($_)/2); $i++) {
                        System::queryStringSetVar($_[$i*2], $_[$i*2+1]);
                    }
                }

                $s = preg_quote(System::getVar('shorturlsseparator'), '~');

                if (!isset($pubtitle)) {
                    // by now, the pub still has the id as suffix
                    preg_match('/^([a-z0-9_\-'.$s.']+?(\~[\d]+)?)$/i', $filename, $matches);

                    $pubtitle = $matches[1];
                }

                // extract the urltitle[~id] when not submitting
                if (!($func == 'edit' && !isset($pid)) && preg_match('~^([a-z0-9_\-'.$s.']+?)(\~(\d+))?$~i', $pubtitle, $matches)) {
                    $where = array();

                    if (isset($matches[3])) {
                        $where[] = array('id = ?', $matches[3]);

                        System::queryStringSetVar('id', $matches[3]);
                    }

                    $where[] = array('core_urltitle = ?', $matches[1]);

                    $pid = Doctrine_Core::getTable('ClipModels_Pubdata'.$tid)
                           ->selectField('core_pid', $where);

                    // invalid urltitle~pid combination
                    if (!$pid) {
                        return false;
                    }

                    System::queryStringSetVar('pid', $pid);
                }
                break;
        }

        // set the arguments
        System::queryStringSetVar('tid',  $tid);
        System::queryStringSetVar('func', $func);

        // if template is set and is not the html output
        if ($template && !in_array($template, array('htm', 'html'))) {
            System::queryStringSetVar('template', $template);
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
