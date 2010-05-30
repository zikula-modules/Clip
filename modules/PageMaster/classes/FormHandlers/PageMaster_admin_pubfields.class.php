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
 * pnForm handler for updating publication fields
 *
 * @author kundi
 */
class PageMaster_admin_pubfields
{
    var $tid;
    var $id;

    /**
     * Initialize function
     */
    function initialize(&$render)
    {
        $dom = ZLanguage::getModuleDomain('PageMaster');

        $tid = FormUtil::getPassedValue('tid');
        $id  = FormUtil::getPassedValue('id');

        // validation check
        if (empty($tid) || !is_numeric($tid)) {
            LogUtil::registerError(__f('Error! %s not set.', 'tid', $dom));
            $render->pnFormRedirect(pnModURL('PageMaster', 'admin', 'main'));
        }
        $this->tid = $tid;

        if (!empty($id)) {
            $this->id = $id;
            $pubfield = DBUtil::selectObjectByID('pagemaster_pubfields', $id);
            $render->assign($pubfield);
        }

        $pubfields = DBUtil::selectObjectArray('pagemaster_pubfields', "pm_tid = '$tid'", 'pm_lineno', -1, -1, 'name');

        $render->assign('pubfields', $pubfields);
        $render->assign('tid', $tid);
        return true;
    }

    /**
     * Command handler
     */
    function handleCommand(&$render, &$args)
    {
        $dom = ZLanguage::getModuleDomain('PageMaster');

        $data = $render->pnFormGetValues();

        $data['id']        = (int)$this->id;
        $data['tid']       = (int)$this->tid;

        $plugin            = PMgetPlugin($data['fieldplugin']);
        $data['fieldtype'] = $plugin->columnDef;

        $returnurl = pnModURL('PageMaster', 'admin', 'pubfields',
                              array('tid' => $data['tid']));

        // handle the commands
        switch ($args['commandName'])
        {
            // create a field
            case 'create':
                if (!$render->pnFormIsValid()) {
                    return false;
                }

                if ($data['istitle'] == 1) {
                    $istitle = array('istitle' => '0');
                    DBUtil::updateObject($istitle, 'pagemaster_pubfields', "pm_tid = '$data[tid]'");
                }

                $data['name']  = str_replace("'", '', $data['name']);
                $submittedname = DataUtil::formatForStore($data['name']);
                if (empty($this->id)) {
                    $where = "pm_name = '$submittedname' AND pm_tid = '$data[tid]'";
                } else {
                    $where = "pm_id <> '{$this->id}' AND pm_name = '$submittedname' AND pm_tid = '$data[tid]'";
                }

                $nameUnique = DBUtil::selectFieldMax('pagemaster_pubfields', 'id', 'COUNT', $where);
                if ($nameUnique > 0) {
                    return LogUtil::registerError(__('Error! Name has to be unique.', $dom));
                }

                if (empty($this->id)) {
                    $max_rowID = DBUtil::selectFieldMax('pagemaster_pubfields', 'id', 'MAX', 'pm_tid = '.$data['tid']);
                    $data['lineno'] = $max_rowID + 1;
                    if ($max_rowID == 1) {
                        $data['istitle'] = 1;
                    }
                    DBUtil::insertObject($data, 'pagemaster_pubfields');
                    LogUtil::registerStatus(__('Done! Field created.', $dom));

                } else {
                    DBUtil::updateObject($data, 'pagemaster_pubfields', 'pm_id = '.$this->id);
                    LogUtil::registerStatus(__('Done! Field updated.', $dom));
                }
                break;

            // delete the field
            case 'delete':
                if (DBUtil::deleteObject($data, 'pagemaster_pubfields')) {
                    LogUtil::registerStatus(__('Done! Field deleted.', $dom));
                } else {
                    LogUtil::registerError(__('Error! Deletion attempt failed.', $dom));
                }
                break;
        }

        $render->pnFormRedirect($returnurl);
        return true;
    }
}
