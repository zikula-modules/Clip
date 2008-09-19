<?php
/**
 * PageMaster
 *
 * @copyright (c) 2008, PageMaster Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package     Zikula_3rd_party_Modules
 * @subpackage  pagemaster
 */

/**
 * Search plugin info
 */
function pagemaster_searchapi_info()
{
    return array('title'     => 'pagemaster',
                 'functions' => array('pagemaster' => 'search'));
}

/**
 * Search form component
 */
function pagemaster_searchapi_options($args)
{
    if (SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_READ)) {
        $render = pnRender::getInstance('pagemaster');

        // Looking for pubtype with at least one searchable field
        $pubtypes = DBUtil::selectObjectArray('pagemaster_pubtypes');
        foreach ($pubtypes as $key => $pubtype) {
            $pubfields = DBUtil::selectObjectArray('pagemaster_pubfields', 'pm_issearchable = 1 and pm_tid = '.$pubtype['tid']);
            $found = false;
            foreach ($pubfields as $pubfield) {
                $found = true;
            }
            if (!$found)
                unset ($pubtypes[$key]);
        }

        $render->assign('pubtypes', $pubtypes);

        return $render->fetch('pagemaster_search_options.htm');
    }
    return '';
}

/**
 * Search plugin main function
 */
function pagemaster_searchapi_search($args)
{

    $search_tid = FormUtil::getPassedValue('search_tid', '', 'REQUEST');
    Loader::includeOnce('modules/pagemaster/common.php');
    pnModDBInfoLoad('Search');
    pnModDBInfoLoad('pagemaster');

    $pntable = pnDBGetTables();
    $searchTable  = $pntable['search_result'];
    $searchColumn = $pntable['search_result_column'];
    $where_arr = '';

    $sessionId = session_id();
    $insertSql = "INSERT INTO $searchTable
    ($searchColumn[title],
    $searchColumn[text],
    $searchColumn[extra],
    $searchColumn[created],
    $searchColumn[module],
    $searchColumn[session])
    VALUES ";

    $pubtypes = DBUtil::selectObjectArray('pagemaster_pubtypes');
    foreach ($pubtypes as $pubtype)
    {
        if ($search_tid[$pubtype['tid']] == 1){
            $pubfields  = DBUtil::selectObjectArray('pagemaster_pubfields', 'pm_issearchable = 1 and pm_tid = '.$pubtype['tid']);
            $tablename  = 'pagemaster_pubdata'.$pubtype['tid'];
            $columnname = $pntable[$tablename.'_column'];

            foreach ($pubfields as $pubfield) {
                $where_arr[] = $columnname[$pubfield['name']];
            }

            if (is_array($where_arr)) {
                $where = search_construct_where($args, $where_arr);
                $where .= ' AND pm_showinlist = 1 ';
                $where .= ' AND pm_online = 1 ';
                $where .= ' AND pm_indepot = 0 ';
                $where .= " AND (pm_language = '' or pm_language = '".pnUserGetLang()."')";
                $where .= ' AND (pm_publishdate <= NOW() or pm_publishdate is null) AND (pm_expiredate >= NOW() or pm_expiredate is null)';

                $tablename = 'pagemaster_pubdata'.$pubtype['tid'];
                $publist = DBUtil::selectObjectArray($tablename, $where);
                $core_title = getTitleField($pubfields);
                $type_name = pnML($pubtype['title']); 
                foreach ($publist as $pub) {
                    $extra = serialize(array('tid' => $pubtype['tid'], 'pid' => $pub['core_pid']));
                    $sql = $insertSql . '('
                    . '\'' . DataUtil::formatForStore($type_name . ' - ' . $pub[$core_title]) . '\', '
                    . '\'' . DataUtil::formatForStore('') . '\', '
                    . '\'' . DataUtil::formatForStore($extra) . '\', '
                    . '\'' . DataUtil::formatForStore($pub['cr_date']) . '\', '
                    . '\'' . 'pagemaster' . '\', '
                    . '\'' . DataUtil::formatForStore($sessionId) . '\')';
                    $insertResult = DBUtil::executeSQL($sql);
                    if (!$insertResult) {
                        return LogUtil::registerError (_GETFAILED);
                    }
                }
            }
        }
        $where_arr = '';
    }
    return true;
}

/**
 * Do last minute access checking and assign URL to items
 *
 * Access checking is ignored since access check has
 * already been done. But we do add a URL to the found item
 */
function pagemaster_searchapi_search_check(&$args)
{
    $datarow = &$args['datarow'];
    $extra   = unserialize($datarow['extra']);
    $datarow['url'] = pnModUrl('pagemaster', 'user', 'viewpub',
                               array('tid' => $extra['tid'],
                                     'pid' => $extra['pid']));
    return true;
}
