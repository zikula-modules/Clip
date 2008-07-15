<?php
/**
 * PageMaster
 *
 * @copyright (c) 2008, PageMaster Team
 * @link        http://code.zikula.org/projects/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package     Zikula_3rd_party_Modules
 * @subpackage  pagemaster
 */

function pagemaster_init()
{
    // create table
    if (!DBUtil::createTable('pagemaster_pubfields')) {
        return false;
    }

    if (!DBUtil::createTable('pagemaster_pubtypes')) {
        return false;
    }
    
    if (!DBUtil::createIndex('urltitle', 'pagemaster_pubtypes', array('urltitle'))) {
        return LogUtil::registerError(_CREATEINDEXFAILED);
    }
    
    if (!DBUtil::createTable('pagemaster_revisions')) {
        return false;
    }

    Loader::loadClass('CategoryUtil');
    Loader::loadClassFromModule('Categories', 'Category');

    $rootcat = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules');
    $lang = pnUserGetLang();

    $cat = new PNCategory();
    $cat->setDataField('parent_id', $rootcat['id']);
    $cat->setDataField('name', 'pagemaster');
    $cat->setDataField('display_name', array ($lang => 'pagemaster'));
    $cat->setDataField('display_desc', array ($lang => 'module category for pagemaster'));
    $cat->insert();
    $cat->update();

    $rootcat = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules/pagemaster');
    $cat = new PNCategory();
    $cat->setDataField('parent_id', $rootcat['id']);
    $cat->setDataField('name', 'lists');
    $cat->setDataField('display_name', array ($lang => 'lists'));
    $cat->setDataField('display_desc', array ($lang => 'contains lists for pagemaster publications'));
    $cat->insert();
    $cat->update();
    return (true);
}

function pagemaster_upgrade($from_version)
{
    switch ($from_version) {

    case '0.1' :
        if (!DBUtil::changeTable('pagemaster_pubtypes')) {
            return LogUtil::registerError(_CHANGETABLEFAILED);
        }
        if (!DBUtil::createIndex('urltitle', 'pagemaster_pubtypes', array('urltitle'))) {
            return LogUtil::registerError(_CREATEINDEXFAILED);
        }
        $types = DBUtil::selectObjectArray('pagemaster_pubtypes');
        if ($types !== false) {
            foreach (array_keys($types) as $k) {
                $types[$k]['urltitle'] = DataUtil::formatPermalink($types[$k]['title']);
            }
            if (!DBUtil::updateObjectArray($types, 'pagemaster_pubtypes', 'tid')) {
                return LogUtil::registerError(_UPDATEFAILED);
            }
        }
    case '0.2':
        break;

    }

    return true;
}

function pagemaster_delete()
{
    $pubtypes = DBUtil::selectObjectArray('pagemaster_pubtypes');
    foreach ($pubtypes as $pubtype) {
        DBUtil::dropTable('pagemaster_pubdata'.$pubtype['tid']);
    }

    if (!DBUtil::dropTable('pagemaster_pubfields')) {
        return false;
    }
    if (!DBUtil::dropTable('pagemaster_pubtypes')) {
        return false;
    }
    if (!DBUtil::dropTable('pagemaster_revisions')) {
        return false;
    }

    Loader::loadClass('CategoryUtil');
    CategoryUtil::deleteCategoriesByPath('/__SYSTEM__/Modules/pagemaster', 'path');
    pnModDelVar('pagemaster', 'uploadpath');

    return true;
}
