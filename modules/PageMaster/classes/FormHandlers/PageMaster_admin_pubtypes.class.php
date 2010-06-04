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
class PageMaster_admin_pubtypes
{
    var $tid;

    /**
     * Initialize function
     */
    function initialize(&$render)
    {
        $dom = ZLanguage::getModuleDomain('PageMaster');

        $tid = FormUtil::getPassedValue('tid');

        if (!empty($tid) && is_numeric($tid)) {
            $this->tid = $tid;

            $pubtype   = PMgetPubType($tid);
            $pubfields = PMgetPubFields($tid);

            $arraysort = array(
                'core_empty' => array(),
                'core_title' => array(),
                'core_cr_date' => array(),
                'core_pu_date' => array(),
                'core_hitcount' => array()
            );

            $pubarr = array(
                'core_empty' => array(
                    'text'  => '',
                    'value' => ''
                ),
                'core_cr_date' => array(
                    'text'  => __('Creation date', $dom),
                    'value' => 'cr_date'
                ),
                'core_lu_date' => array(
                    'text'  => __('Update date', $dom),
                    'value' => 'lu_date'
                ),
                'core_cr_uid' => array(
                    'text'  => __('Creator', $dom),
                    'value' => 'core_author'
                ),
                'core_lu_uid' => array(
                    'text'  => __('Updater', $dom),
                    'value' => 'lu_uid'
                ),
                'core_pu_date' => array(
                    'text'  => __('Publish date', $dom),
                    'value' => 'pm_publishdate'
                ),
                'core_ex_date' => array(
                    'text'  => __('Expire date', $dom),
                    'value' => 'pm_expiredate'
                ),
                'core_language' => array(
                    'text'  => __('Language', $dom),
                    'value' => 'pm_language'
                ),
                'core_hitcount' => array(
                    'text'  => __('Number of reads', $dom),
                    'value' => 'pm_hitcount'
                )
            );

            foreach (array_keys($pubfields) as $fieldname) {
                $index = ($pubfields[$fieldname]['istitle'] == 1) ? 'core_title' : $fieldname;
                $pubarr[$index] = array(
                    'text'  => __($pubfields[$fieldname]['title'], $dom),
                    'value' => $fieldname
                );
            }

            $pubarr = array_values(array_merge($arraysort, $pubarr));

            $render->assign('pubfields', $pubarr);
            $render->assign($pubtype);
        }

        $pubtypes = PMgetPubType(-1);
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
        $dom = ZLanguage::getModuleDomain('PageMaster');

        $data = $render->pnFormGetValues();
        $data['tid'] = $this->tid;

        // handle the commands
        switch ($args['commandName'])
        {
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
                DBUtil::deleteObject(null, 'pagemaster_pubtypes', "pm_tid = '{$this->tid}'");
                DBUtil::deleteObject(null, 'pagemaster_pubfields', "pm_tid = '{$this->tid}'");
                DBUtil::dropTable('pagemaster_pubdata' . $this->tid);
                // FIXME no more related stuff is needed? Hooks, Workflows registers? 

                LogUtil::registerStatus(__('Done! Publication type deleted.', $dom));
                break;
        }

        return $render->pnFormRedirect(pnModURL('PageMaster', 'admin', 'main'));
    }
}
