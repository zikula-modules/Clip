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
 * Search Model.
 */
class PageMaster_Api_Search extends Zikula_Api
{
    /**
     * Search plugin info.
     */
    public function info()
    {
        return array('title'     => 'PageMaster',
                     'functions' => array('PageMaster' => 'search'));
    }

    /**
     * Search form component.
     */
    public function options($args)
    {
        if (SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_READ)) {
            $render = Zikula_View::getInstance('PageMaster');

            // Looking for pubtype with at least one searchable field
            $pubtypes = Doctrine_Core::getTable('PageMaster_Model_Pubtype')
                        ->getPubtypes()
                        ->toArray();

            foreach ($pubtypes as $key => $pubtype)
            {
                $pubfields = Doctrine_Core::getTable('PageMaster_Model_Pubtype')
                             ->selectFieldArray('name', "issearchable = '1' AND tid = '$pubtype[tid]'");

                if (count($pubfields) == 0 ) {
                    unset($pubtypes[$key]);
                }
            }

            $render->assign('pubtypes', $pubtypes);

            return $render->fetch('pagemaster_search_options.tpl');
        }

        return '';
    }

    /**
     * Search plugin main function.
     */
    public function search($args)
    {
        $search_tid = FormUtil::getPassedValue('search_tid', '', 'REQUEST');

        ModUtil::dbInfoLoad('Search');
        ModUtil::dbInfoLoad('PageMaster');

        $tables = DBUtil::getTables();
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

        $pubtypes = Doctrine_Core::getTable('PageMaster_Model_Pubtype')
                    ->getPubtypes()
                    ->toArray();

        foreach ($pubtypes as $pubtype)
        {
            if ($search_tid == '' || $search_tid[$pubtype['tid']] == 1){
                $pubfieldnames = Doctrine_Core::getTable('PageMaster_Model_Pubtype')
                                 ->selectArray('name', "issearchable = '1' AND tid = '$pubtype[tid]'");

                $tablename  = 'pagemaster_pubdata'.$pubtype['tid'];
                $columnname = $tables[$tablename.'_column'];

                foreach ($pubfieldnames as $pubfieldname) {
                    $where_arr[] = $columnname[$pubfieldname];
                }

                if (is_array($where_arr)) {
                    // FIXME
                    $where  = search_construct_where($args, $where_arr);
                    $where .= " AND core_showinlist = '1'
                                AND core_online = '1'
                                AND core_indepot = '0'
                                AND (core_language = '' OR core_language = '". ZLanguage::getLanguageCode() ."')
                                AND (core_publishdate <= NOW() OR core_publishdate IS NULL)
                                AND (core_expiredate >= NOW() OR core_expiredate IS NULL)";

                    $tablename  = 'pagemaster_pubdata'.$pubtype['tid'];

                    $publist = Doctrine_Core::getTable('PageMaster_Model_Pubdata'.$pubtype['tid'])
                               ->selectCollection($where)
                               ->toArray();

                    $core_title = PageMaster_Util::getTitleField($pubtype['tid']);

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
                            return LogUtil::registerError($this->__('Error! Could not save the search results.'));
                        }
                    }
                }
            }
            $where_arr = array();
        }

        return true;
    }

    /**
     * Do last minute access checking and assign URL to items.
     *
     * Access checking is ignored since access check has
     * already been done. But we do add a URL to the found item.
     */
    public function search_check(&$args)
    {
        $datarow = &$args['datarow'];
        $extra   = unserialize($datarow['extra']);
        $datarow['url'] = ModUtil::url('PageMaster', 'user', 'display',
                                       array('tid' => $extra['tid'],
                                             'pid' => $extra['pid']));
        return true;
    }
}
