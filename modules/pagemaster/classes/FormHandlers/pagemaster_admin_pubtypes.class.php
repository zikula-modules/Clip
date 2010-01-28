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
 * pnForm handler for updating publication types
 *
 * @author kundi
 */
class pagemaster_admin_pubtypes
{
    var $tid;

    /**
     * Initialize function
     */
    function initialize(&$render)
    {
        $dom = ZLanguage::getModuleDomain('pagemaster');

        $tid = FormUtil::getPassedValue('tid');

        if (!empty($tid) && is_numeric($tid)) {
            $this->tid = $tid;

            $pubtype   = PMgetPubType($tid);
            $pubfields = PMgetPubFields($tid);

            $pubarr = array(
                array(
                    'text'  => '',
                    'value' => ''
                ),
                array(
                    'text'  => __('Creation date', $dom),
                    'value' => 'cr_date'
                ),
                array(
                    'text'  => __('Update date', $dom),
                    'value' => 'lu_date'
                ),
                array(
                    'text'  => __('Creator', $dom),
                    'value' => 'core_author'
                ),
                array(
                    'text'  => __('Updater', $dom),
                    'value' => 'lu_uid'
                ),
                array(
                    'text'  => __('Publish Date', $dom),
                    'value' => 'pm_publishdate'
                ),
                array(
                    'text'  => __('Expire Date', $dom),
                    'value' => 'pm_expiredate'
                ),
                array(
                    'text'  => __('Language', $dom),
                    'value' => 'pm_language'
                ),
                array(
                    'text'  => __('Number of Clicks', $dom),
                    'value' => 'pm_hitcount'
                )
            );

            $fieldnames = array_keys($pubfields);
            foreach ($fieldnames as $fieldname) {
                $pubarr[] = array(
                    'text'  => $fieldname,
                    'value' => $fieldname
                );
            }

            $render->assign($pubtype);
            $render->assign('pubfields', $pubarr);
        }

        $pubtypes = DBUtil::selectObjectArray('pagemaster_pubtypes');
        $render->assign('pubtypes', $pubtypes);

        $workflows = PMgetWorkflowsOptionList();
        $render->assign('pmWorkflows', $workflows);

        return true;
    }

    /**
     * Command handler
     */
    function handleCommand(&$render, &$args)
    {
        $dom = ZLanguage::getModuleDomain('pagemaster');

        $data = $render->pnFormGetValues();
        $data['tid'] = $this->tid;

        // handle the commands
        switch ($args['commandName'])
        {
            // update db tables
            case 'updatetabledef':
                $ret = pnModAPIFunc('pagemaster', 'admin', 'updatetabledef',
                                    array('tid' => $data['tid']));

                if (!$ret) {
                    return LogUtil::registerError(__('Error! Update attempt failed.', $dom));
                }

                LogUtil::registerStatus(__('Done! Publication type updated.', $dom));
                break;

            // create a pubtype
            case 'create':
                if (!$render->pnFormIsValid()) {
                    return false;
                }

                if (!isset($data['urltitle']) || empty($data['urltitle'])) {
                    $data['urltitle'] = DataUtil::formatPermalink($data['title']);
                }
                if (empty($data['filename'])) {
                    $data['filename'] = $data['title'];
                }
                if (empty($data['formname'])) {
                    $data['formname'] = $data['title'];
                }

                if (empty($this->tid)) {
                    DBUtil::insertObject($data, 'pagemaster_pubtypes');
                    LogUtil::registerStatus(__('Done! Publication type created.', $dom));
                } else {
                    DBUtil::updateObject($data, 'pagemaster_pubtypes', 'pm_tid='.$this->tid);
                    LogUtil::registerStatus(__('Done! Publication type updated.', $dom));
                }
                break;

            // delete
            case 'delete':
                DBUtil::deleteObject(null, 'pagemaster_pubtypes', 'pm_tid='.$this->tid);
                DBUtil::deleteObject(null, 'pagemaster_pubfields', 'pm_tid='.$this->tid);
                DBUtil::dropTable('pagemaster_pubdata' . $this->tid);
                // FIXME no more related stuff is needed? Hooks, Workflows registers? 

                LogUtil::registerStatus(__('Done! Publication type deleted.', $dom));
                break;
        }

        return $render->pnFormRedirect(pnModURL('pagemaster', 'admin', 'main'));
    }
}
