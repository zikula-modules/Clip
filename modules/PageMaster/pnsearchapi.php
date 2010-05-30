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
 * Search plugin info
 */
function PageMaster_searchapi_info()
{
    return array('title'     => 'PageMaster',
                 'functions' => array('PageMaster' => 'search'));
}

/**
 * Search form component
 */
function PageMaster_searchapi_options($args)
{
    if (SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_READ)) {
        $render = pnRender::getInstance('PageMaster');

        // Looking for pubtype with at least one searchable field
        $pubtypes = DBUtil::selectObjectArray('pagemaster_pubtypes');
        foreach ($pubtypes as $key => $pubtype)
        {
            $pubfields = DBUtil::selectFieldArray('pagemaster_pubfields', 'name', "pm_issearchable = '1' AND pm_tid = '$pubtype[tid]'");

            if (count($pubfields) == 0 ) {
                unset($pubtypes[$key]);
            }
        }

        $render->assign('pubtypes', $pubtypes);

        return $render->fetch('pagemaster_search_options.htm');
    }

    return '';
}

/**
 * Search plugin main function
 */
function PageMaster_searchapi_search($args)
{
    $dom = ZLanguage::getModuleDomain('PageMaster');

    $search_tid = FormUtil::getPassedValue('search_tid', '', 'REQUEST');

    Loader::includeOnce('modules/PageMaster/common.php');
    pnModDBInfoLoad('Search');
    pnModDBInfoLoad('PageMaster');

    $tables = pnDBGetTables();
    $searchTable  = $tables['search_result'];
    $searchColumn = $tables['search_result_column'];
    $where_arr    = array();

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
        if ($search_tid == '' || $search_tid[$pubtype['tid']] == 1){
            $pubfieldnames = DBUtil::selectFieldArray('pagemaster_pubfields', 'name', 'pm_issearchable = 1 AND pm_tid = '.$pubtype['tid']);

            $tablename  = 'pagemaster_pubdata'.$pubtype['tid'];
            $columnname = $tables[$tablename.'_column'];

            foreach ($pubfieldnames as $pubfieldname) {
                $where_arr[] = $columnname[$pubfieldname];
            }

            if (is_array($where_arr)) {
                $where  = search_construct_where($args, $where_arr);
                $where .= " AND pm_showinlist = '1'
                            AND pm_online = '1'
                            AND pm_indepot = '0'
                            AND (pm_language = '' OR pm_language = '". ZLanguage::getLanguageCode() ."')
                            AND (pm_publishdate <= NOW() OR pm_publishdate IS NULL) AND (pm_expiredate >= NOW() OR pm_expiredate IS NULL)";

                $tablename  = 'pagemaster_pubdata'.$pubtype['tid'];

                $publist    = DBUtil::selectObjectArray($tablename, $where);

                $core_title = PMgetPubtypeTitleField($pubtype['tid']);

                foreach ($publist as $pub)
                {
                    $extra = serialize(array('tid' => $pubtype['tid'], 'pid' => $pub['core_pid']));
                    $sql = $insertSql . '('
                    . '\'' . DataUtil::formatForStore($pub[$core_title]) . '\', '
                    . '\'' . DataUtil::formatForStore('') . '\', '
                    . '\'' . DataUtil::formatForStore($extra) . '\', '
                    . '\'' . DataUtil::formatForStore($pub['cr_date']) . '\', '
                    . '\'' . 'PageMaster' . '\', '
                    . '\'' . DataUtil::formatForStore($sessionId) . '\')';

                    $insertResult = DBUtil::executeSQL($sql);
                    if (!$insertResult) {
                        return LogUtil::registerError(__('Error! Could not save the search results.', $dom));
                    }
                }
            }
        }
        $where_arr = array();
    }

    return true;
}

/**
 * Do last minute access checking and assign URL to items
 *
 * Access checking is ignored since access check has
 * already been done. But we do add a URL to the found item
 */
function PageMaster_searchapi_search_check(&$args)
{
    $datarow = &$args['datarow'];
    $extra   = unserialize($datarow['extra']);
    $datarow['url'] = pnModUrl('PageMaster', 'user', 'viewpub',
                               array('tid' => $extra['tid'],
                                     'pid' => $extra['pid']));
    return true;
}
