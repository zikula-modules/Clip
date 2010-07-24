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
 * User Model.
 */
class PageMaster_Api_User extends Zikula_Api
{
    /**
     * Returns a Publication List.
     *
     * @author kundi
     * @param  int     $args['tid']
     * @param  int     $args['startnum']
     * @param  int     $args['itemsperpage']
     * @param  string  $args['countmode'] no, just, both.
     * @param  bool    $args['checkperm']
     * @param  bool    $args['handlePluginFields']
     * @param  bool    $args['getApprovalState']
     *
     * @return array   Publication list and/or count.
     */
    public function getall($args)
    {
        if (!isset($args['tid'])) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'tid'));
        }

        $tid = $args['tid'];

        // validate the passed tid
        $tables = DBUtil::getTables();
        if (!isset($tables['pagemaster_pubdata'.$tid])) {
            return LogUtil::registerError($this->__f('Error! No such publication type [%s] found.', $tid));
        }
        unset($tables);

        // parameters defaults
        $handlePluginFields = isset($args['handlePluginFields']) ? $args['handlePluginFields'] : false;
        $getApprovalState   = isset($args['getApprovalState']) ? $args['getApprovalState'] : false;
        $checkPerm          = isset($args['checkPerm']) ? $args['checkPerm'] : false;

        // permission check
        if ($checkPerm && !SecurityUtil::checkPermission('pagemaster:list:', "$tid::", ACCESS_READ)) {
            return LogUtil::registerPermissionError();
        }

        // optional arguments.
        if (!isset($args['startnum']) || !is_numeric($args['startnum'])) {
            $args['startnum'] = 1;
        }
        if (!isset($args['itemsperpage']) || !is_numeric($args['itemsperpage'])) {
            $args['itemsperpage'] = -1;
        }
        if (!isset($args['justcount']) || !is_numeric($args['justcount'])) {
            $args['justcount'] = 'no';
        }

        $pubtype   = isset($args['pubtype']) ? $args['pubtype'] : PageMaster_Util::getPubType($tid);
        $pubfields = isset($args['pubfields']) ? $args['pubfields'] : PageMaster_Util::getPubFields($tid);

        // mode check
        $isadmin = !SecurityUtil::checkPermission('pagemaster:full:', "$tid::", ACCESS_ADMIN) || (!isset($args['admin']) || !$args['admin']);
        // TODO pubtype.editown + author mode parameter check

        // set the order
        if (!isset($args['orderby']) || empty($args['orderby'])) {
            if (!empty($pubtype['sortfield1'])) {
                if ($pubtype['sortdesc1'] == 1) {
                    $orderby = $pubtype['sortfield1'].' DESC ';
                } else {
                    $orderby = $pubtype['sortfield1'].' ASC ';
                }

                if (!empty($pubtype['sortfield2'])) {
                    if ($pubtype['sortdesc2'] == 1) {
                        $orderby .= ', '.$pubtype['sortfield2'].' DESC ';
                    } else {
                        $orderby .= ', '.$pubtype['sortfield2'].' ASC ';
                    }
                }

                if (!empty($pubtype['sortfield3'])) {
                    if ($pubtype['sortdesc3'] == 1) {
                        $orderby .= ', '.$pubtype['sortfield3'].' DESC ';
                    } else {
                        $orderby .= ', '.$pubtype['sortfield3'].' ASC ';
                    }
                }
            } else {
                $orderby = 'cr_date';
            }
        } else {
            $orderby = $args['orderby'];
        }

        $filterPlugins = array();
        foreach ($pubfields as $fieldname => $field)
        {
            $pluginclass = $field['fieldplugin'];
            $plugin = PageMaster_Util::getPlugin($pluginclass);

            if (isset($plugin->filterClass)) {
                $filterPlugins[$plugin->filterClass]['fields'][] = $fieldname;
            }
            // check for tables to join
            if ($args['countmode'] <> 'just'){
                // do not join for just
                if ($field['fieldplugin'] == 'pmformpubinput'){
                    $vars        = explode(';', $field['typedata']);
                    $join_tid    = $vars[0];
                    $join_filter = $vars[1]; // TODO Use?
                    $join        = $vars[2];
                    $join_fields = $vars[3];
                    $join_arr    = explode(',', $join_fields);
                    if ($join == 'on') {
                        foreach ($join_arr as $value) {
                            list($x, $y) = explode(':', $value);
                            $join_field_arr[]        = $x;
                            $object_field_name_arr[] = $y;
                        }
                        $joinInfo[] = array('join_table'         =>  'pagemaster_pubdata'.$join_tid,
                                            'join_field'         =>  $join_field_arr,
                                            'object_field_name'  =>  $object_field_name_arr,
                                            'compare_field_table'=>  $fieldname,
                                            'compare_field_join' =>  'core_pid');
                    }
                }
            }
        }

        if (isset($joinInfo)) {
            $tbl_alias = 'tbl.';
            $filter_args = array('join' => array('join_table' => $joinInfo['join_table']),
                                                 'plugins'    => $filterPlugins);
        } else {
            $tbl_alias = '';
            $filter_args = array('plugins' => $filterPlugins);
        }

        // check if some plugin specific orderby has to be done
        $orderby   = PageMaster_Util::handlePluginOrderBy($orderby, $pubfields, $tbl_alias);
        $tablename = 'pagemaster_pubdata'.$tid;

        $fu = new FilterUtil('PageMaster', $tablename, $filter_args);

        if (isset($args['filter']) && !empty($args['filter'])) {
            $fu->setFilter($args['filter']);
        } elseif (!empty($pubtype['defaultfilter'])) {
            $fu->setFilter($pubtype['defaultfilter']);
        }

        $filter_where = $fu->GetSQL();

        // build the where clause
        $where = array();
        $uid   = UserUtil::getVar('uid');

        if ($isadmin) {
            if (!empty($uid) && $pubtype['enableeditown'] == 1) {
                $where[] = "( {$tbl_alias}pm_online = '1' AND ( {$tbl_alias}pm_author = '$uid' OR {$tbl_alias}pm_showinlist = '1') )";
            } else {
                $where[] = "  {$tbl_alias}pm_online = '1' AND {$tbl_alias}pm_showinlist = '1'";
            }

            $where[] = "  {$tbl_alias}pm_indepot = '0' ";
            $where[] = "( {$tbl_alias}pm_language = '' OR {$tbl_alias}pm_language = '".ZLanguage::getLanguageCode()."' )";
            $where[] = "( {$tbl_alias}pm_publishdate <= NOW() OR {$tbl_alias}pm_publishdate IS NULL )";
            $where[] = "( {$tbl_alias}pm_expiredate >= NOW() OR {$tbl_alias}pm_expiredate IS NULL )";
        }
        // TODO Implement author condition

        if (!empty($filter_where['where'])) {
            $where[] = $filter_where['where'];
        }

        $where = implode(' AND ', $where);

        if ($args['countmode'] <> 'just') {
            if (isset($joinInfo)) {
                $publist = DBUtil::selectExpandedObjectArray($tablename, $joinInfo, $where, $orderby, $args['startnum']-1, $args['itemsperpage']);
            } else {
                $publist = DBUtil::selectObjectArray($tablename, $where, $orderby, $args['startnum']-1, $args['itemsperpage']);
            }
            if ($getApprovalState) {
                foreach (array_keys($publist) as $key) {
                    Zikula_Workflow_Util::getWorkflowForObject($publist[$key], $tablename, 'id', 'PageMaster');
                }
            }
            if ($handlePluginFields) {
                $publist = PageMaster_Util::handlePluginFields($publist, $pubfields);
            }
        }

        if ($args['countmode'] == 'just' || $args['countmode'] == 'both') {
            $pubcount = DBUtil::selectObjectCount($tablename, str_replace(' tbl.', ' ', $where));
        }

        return array (
            'publist'  => isset($publist) ? $publist : null,
            'pubcount' => isset($pubcount) ? $pubcount : null
        );
    }

    /**
     * Returns a Publication.
     *
     * @author kundi
     * @param  int  $args['tid']
     * @param  int  $args['pid']
     * @param  int  $args['id']
     * @param  bool $args['checkPerm']
     * @param  bool $args['getApprovalState']
     * @param  bool $args['handlePluginFields']
     *
     * @return array One publication.
     */
    public function get($args)
    {
        // validation of essential parameters
        if (!isset($args['tid'])) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'tid'));
        }
        if (!isset($args['id']) && !isset($args['pid'])) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'id | pid'));
        }

        // defaults
        $pubtype            = isset($args['pubtype']) ? $args['pubtype'] : null;
        $pubfields          = isset($args['pubfields']) ? $args['pubfields'] : null;
        $getApprovalState   = isset($args['getApprovalState']) ? $args['getApprovalState'] : false;
        $checkPerm          = isset($args['checkPerm']) ? $args['checkPerm'] : false;
        $handlePluginFields = isset($args['handlePluginFields']) ? $args['handlePluginFields'] : false;

        $tid = (int)$args['tid'];
        $pid = isset($args['pid']) ? (int)$args['pid'] : null;
        $id  = isset($args['id']) ? (int)$args['id'] : null;
        unset($args);

        // get the pubtype if not set
        if (empty($pubtype)) {
            $pubtype = PageMaster_Util::getPubType($tid);
            // validate the result
            if (!$pubtype) {
                return LogUtil::registerError($this->__f('Error! No such publication type [%s] found.', $tid));
            }
        }

        // get the pubfields if not set
        if (empty($pubfields)) {
            $pubfields = PageMaster_Util::getPubFields($tid);
            // validate the result
            if (!$pubfields) {
                return LogUtil::registerError($this->__('Error! No publication fields found.'));
            }
        }

        // build the where clause
        $tablename = 'pagemaster_pubdata'.$tid;
        $uid       = UserUtil::getVar('uid');
        $where     = '';

        if (!SecurityUtil::checkPermission('pagemaster:full:', "$tid::", ACCESS_ADMIN))
        {
            if (!empty($uid) && $pubtype['enableeditown'] == 1) {
                $where .= " (pm_author = '$uid' OR pm_online = '1' )";
            } else {
                $where .= " pm_online = '1' ";
            }
            $where .= " AND pm_indepot = '0'
                        AND (pm_language = '' OR pm_language = '".ZLanguage::getLanguageCode()."')
                        AND (pm_publishdate <= NOW() OR pm_publishdate IS NULL)
                        AND (pm_expiredate >= NOW() OR pm_expiredate IS NULL)";

            if (empty($args['id'])) {
                $where .= " AND pm_pid = '$pid'";
            } else {
                $where .= " AND pm_id = '$id'";
            }
        } else {
            if (empty($id)) {
                $where .= " pm_pid = '$pid' AND pm_online = '1'";
            } else {
                $where .= " pm_id = '$id'";
            }
        }

        $pubdata = DBUtil::selectObject($tablename, $where);

        if (!$pubdata) {
            return false;
        }

        if ($checkPerm && !SecurityUtil::checkPermission('pagemaster:full:', "$tid:$pubdata[core_pid]:", ACCESS_READ)) {
            return LogUtil::registerPermissionError();
        }

        // handle the plugins data if needed
        if ($handlePluginFields){
            $pubdata = PageMaster_Util::handlePluginFields($pubdata, $pubfields, false);
        }

        if ($getApprovalState) {
            Zikula_Workflow_Util::getWorkflowForObject($pubdata, $tablename, 'id', 'PageMaster');
        }

        // fills the core_title field
        $core_title = PageMaster_Util::findTitleField($pubfields);
        $pubdata    = array('core_title' => $pubdata[$core_title]) + $pubdata;

        return $pubdata;
    }

    /**
     * Edit or creates a new publication.
     *
     * @author kundi
     * @param $args['data'] array of pubfields data.
     * @param $args['commandName'] commandName has to be a valid workflow action for the currenct state.
     * @param $args['pubfields'] array of pubfields (optional, performance).
     * @param $args['schema'] schema name (optional, performance).
     *
     * @return boolean true or false.
     */
    public function edit($args)
    {
        if (!isset($args['data'])) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'data'));
        }
        if (!isset($args['commandName'])) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'commandName').' '.$this->__('commandName has to be a valid workflow action for the current state.'));
        }

        $commandName = $args['commandName'];
        $data        = $args['data'];
        $tid         = $data['tid'];

        $pubfields = isset($args['pubfields']) ? $args['pubfields'] : PageMaster_Util::getPubFields($tid);

        if (!isset($args['schema'])) {
            $pubtype = PageMaster_Util::getPubType($tid);
            $schema  = str_replace('.xml', '', $pubtype['workflow']);
        } else {
            $schema = $args['schema'];
        }

        foreach ($pubfields as $fieldname => $field)
        {
            $plugin = PageMaster_Util::getPlugin($field['fieldplugin']);
            if (method_exists($plugin, 'preSave')) {
                $data[$fieldname] = $plugin->preSave($data, $field);
            }
        }

        $ret = Zikula_Workflow_Util::executeAction($schema, $data, $commandName, 'pagemaster_pubdata'.$data['tid'], 'PageMaster');

        if (empty($ret)) {
            return LogUtil::registerError($this->__('Workflow action error.'));
        }

        $data = array_merge($data, array('core_operations' => $ret));

        return $data;
    }

    /**
     * Returns pid.
     *
     * @author kundi
     * @param int $args['tid']
     * @param int $args['id']
     *
     * @return int pid.
     */
    public function getPid($args)
    {
        if (!isset($args['tid'])) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'tid'));
        }
        if (!isset($args['id'])) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'id'));
        }

        $tablename = 'pagemaster_pubdata'.$args['tid'];
        $pub       = DBUtil::selectObjectByID($tablename, $args['id'], 'id');

        return $pub['id'];
    }

    /**
     * Returns the ID of the online publication.
     *
     * @author kundi
     * @param int $args['tid']
     * @param int $args['pid']
     *
     * @return int id.
     */
    public function getId($args)
    {
        if (!isset($args['tid']) || !is_numeric($args['tid'])) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'tid'));
        }
        if (!isset($args['pid']) || !is_numeric($args['pid'])) {
            return LogUtil::registerError($this->__f('Error! Missing argument [%s].', 'pid'));
        }

        // build the query
        $tablename = 'pagemaster_pubdata'.$args['tid'];
        $where     = "pm_pid = '$args[pid]' AND pm_online = '1'";

        return DBUtil::selectField($tablename, 'id', $where);
    }

    /**
     * Hierarchical data of publication types and publications.
     *
     * @author rgasch
     * @param $args['tid']
     * @param $args['pid'] (optional)
     * @param $args['orderby'] (optional)
     *
     * @return publication data.
     */
    public function editlist($args=array())
    {
        $orderby  = isset($args['orderby']) ? $args['orderby'] : FormUtil::getPassedValue('orderby', 'core_pid');

        $allTypes = array();
        $pubtypes = DBUtil::selectObjectArray('pagemaster_pubtypes', null, 'title');

        foreach ($pubtypes as $pubtype) {
            $tid    = $pubtype['tid'];
            $tables = DBUtil::getTables();

            if (!isset($tables['pagemaster_pubdata'.$tid])) {
                $allTypes[$tid] = $pubtype['title'];
                continue;
            }

            $coreTitle = PageMaster_Util::getTitleField($tid);
            if (substr($orderby, 0, 10) == 'core_title') {
                $orderby = str_replace('core_title', $coreTitle, $orderby);
            }

            $tablename = 'pagemaster_pubdata'.$tid;
            $where     = 'pm_indepot = 0';
            $sort      = str_replace(':', ' ', $orderby);
            $list      = DBUtil::selectObjectArray($tablename, $where, $sort);

            foreach ($list as $k=>$v) {
                if (!SecurityUtil::checkPermission('pagemaster:input:', "$tid:$v[pid]:", ACCESS_EDIT)) {
                    unset($list[$k]);
                } else {
                    $list[$k]['_title'] = $v[$coreTitle];
                }
            }

            $publist[$tid]  = $list;
            $allTypes[$tid] = $pubtype['title'];
        }

        $ret = array(
            'pubList'  => $publist,
            'allTypes' => $allTypes
        );

        return $ret;
    }

    /**
     * Form custom url string.
     *
     * @author Philipp Niethammer <webmaster@nochwer.de>
     * @param  array $args Arguments given by ModUtil::url.
     *
     * @return string Custom URL string.
     */
    public function encodeurl($args)
    {
        if (!isset($args['modname']) || !isset($args['func']) || !isset($args['args'])) {
            return LogUtil::registerArgsError();
        }

        static $cache = array();

        $supportedfunctions = array('main', 'display', 'viewpub');
        if (!in_array($args['func'], $supportedfunctions)) {
            return '';
        }

        $pubtypeTitle = '';
        if (!isset($args['args']['tid'])) {
            return false;
        } else {
            $tid          = (int)$args['args']['tid'];
            $pubtype      = PageMaster_Util::getPubType($tid);
            $pubtypeTitle = DataUtil::formatPermalink($pubtype['urltitle']);

            unset($args['args']['tid']);
            unset($pubtype);
        }

        $pubTitle = '';
        if (isset($args['args']['pid']) || isset($args['args']['id'])) {
            if (isset($args['args']['pid'])) {
                $pid = (int)$args['args']['pid'];
                unset($args['args']['pid']);
            } elseif (isset($args['args']['id'])) {
                $id = (int)$args['args']['id'];
                unset($args['args']['id']);
                if (!isset($cache['id'][$id])) {
                    $pid = $cache['id'][$id] = DBUtil::selectFieldByID("pagemaster_pubdata{$tid}", 'core_pid', $id, 'id');
                } else {
                    $pid = $cache['id'][$id];
                }
            } else {
                return false;
            }

            $titlefield = PageMaster_Util::getTitleField($tid);

            $pubTitle = DBUtil::selectFieldByID("pagemaster_pubdata{$tid}", $titlefield, $pid, 'core_pid');
            $pubTitle = '/'.DataUtil::formatPermalink($pubTitle).'.'.$pid;
        }

        $params = '';
        if (count($args['args']) > 0) {
            $paramarray = array();
            foreach ($args['args'] as $k => $v) {
                $paramarray[] = $k.'/'.urlencode($v);
            }
            $params = '/'. implode('/', $paramarray);
        }

        return $args['modname'].'/'.$pubtypeTitle.$pubTitle.$params;
    }

    /**
     * Decode custom url string.
     *
     * @author Philipp Niethammer
     *
     * @return bool true if succeded false otherwise.
     */
    public function decodeurl($args)
    {
        $_ = $args['vars'];

        $functions = array('executecommand', 'main', 'view', 'display', 'edit', 'viewpub', 'pubedit');
        $argsnum   = count($_);
        if (!isset($_[2]) || empty($_[2])) {
            System::queryStringSetVar('func', 'main');
            return true;
        }

        if (in_array($_[2], $functions)) {
            return false;
        }

        $nextvar = 3;

        $tid = DBUtil::selectFieldByID('pagemaster_pubtypes', 'tid', $_[2], 'urltitle');
        if (!$tid) {
            return false;
        } else {
            System::queryStringSetVar('func', 'view');
            System::queryStringSetVar('tid', $tid);
        }

        if (isset($_[3]) && !empty($_[3])) {
            $permalinksseparator = System::getVar('shorturlsseparator');
            $match = '';
            $isPub = (bool) preg_match('~^[a-z0-9_'.$permalinksseparator.']+\.(\d+)+$~i', $_[3], $match);
            if ($isPub) {
                $pid = $match[1];
                System::queryStringSetVar('func', 'display');
                System::queryStringSetVar('pid', $pid);
                $nextvar = 4;
            }
        }

        if (isset($_[$nextvar]) && !empty($_[$nextvar])) {
            for ($i = $nextvar; $i < $argsnum; $i+=2) {
                System::queryStringSetVar($_[$i], $_[$i+1]);
            }
        }

        return true;
    }

    /**
     * @see PageMaster_Api_User::getall
     * @deprecated
     */
    public function pubList($args)
    {
        return $this->getall($args);
    }

    /**
     * @see PageMaster_Api_User::get
     * @deprecated
     */
    public function getPub($args)
    {
        return $this->get($args);
    }

    /**
     * @see PageMaster_Api_User::edit
     * @deprecated
     */
    public function editPub($args)
    {
        return $this->edit($args);
    }

    /**
     * @see PageMaster_Api_User::editlist
     * @deprecated
     */
    public function pubeditlist($args)
    {
        return $this->editlist($args);
    }
}
