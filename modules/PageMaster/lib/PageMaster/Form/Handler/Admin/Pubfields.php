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
class PageMaster_Form_Handler_Admin_Pubfields extends Form_Handler
{
    var $tid;
    var $id;
    var $referer;

    /**
     * Initialize function
     */
    function initialize(&$view)
    {
        $tid = FormUtil::getPassedValue('tid');
        $id  = FormUtil::getPassedValue('id');

        // validation check
        if (empty($tid) || !is_numeric($tid)) {
            $view->setErrorMsg($this->__f('Error! %s not set.', 'tid'));
            return $view->redirect(ModUtil::url('PageMaster', 'admin'));
        }
        $this->tid = $tid;

        if (!empty($id)) {
            $this->id = $id;
            $pubfield = DBUtil::selectObjectByID('pagemaster_pubfields', $id);
            $view->assign($pubfield);
        }

        // stores the first referer and the item URL
        if (empty($this->referer)) {
            $adminurl = ModUtil::url('PageMaster', 'admin');
            $this->referer = System::serverGetVar('HTTP_REFERER', $adminurl);
        }

        $pubfields = DBUtil::selectObjectArray('pagemaster_pubfields', "pm_tid = '$tid'", 'pm_lineno', -1, -1, 'name');

        $view->assign('pubfields', $pubfields)
             ->assign('tid', $tid);

        return true;
    }

    /**
     * Command handler
     */
    function handleCommand(&$view, &$args)
    {
        if ($args['commandName'] == 'cancel') {
            return $view->redirect($this->referer);
        }

        $data = $view->getValues();

        $data['id']        = (int)$this->id;
        $data['tid']       = (int)$this->tid;

        $plugin            = PageMaster_Util::getPlugin($data['fieldplugin']);
        $data['fieldtype'] = $plugin->columnDef;

        $returnurl = ModUtil::url('PageMaster', 'admin', 'pubfields',
                                  array('tid' => $data['tid']));

        // handle the commands
        switch ($args['commandName'])
        {
            // create a field
            case 'create':
                if (!$view->isValid()) {
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
                    return $view->setErrorMsg($this->__('Error! Name has to be unique.'));
                }

                if (empty($this->id)) {
                    $max_rowID = DBUtil::selectFieldMax('pagemaster_pubfields', 'id', 'MAX', 'pm_tid = '.$data['tid']);
                    $data['lineno'] = $max_rowID + 1;
                    if ($max_rowID == 1) {
                        $data['istitle'] = 1;
                    }
                    DBUtil::insertObject($data, 'pagemaster_pubfields');
                    LogUtil::registerStatus($this->__('Done! Field created.'));

                } else {
                    DBUtil::updateObject($data, 'pagemaster_pubfields', 'pm_id = '.$this->id);
                    LogUtil::registerStatus($this->__('Done! Field updated.'));
                }
                break;

            // delete the field
            case 'delete':
                if (DBUtil::deleteObject($data, 'pagemaster_pubfields')) {
                    LogUtil::registerStatus($this->__('Done! Field deleted.'));
                } else {
                    return $view->setErrorMsg($this->__('Error! Deletion attempt failed.'));
                }
                break;
        }

        return $view->redirect($returnurl);
    }
}
