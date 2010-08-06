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
 * Form handler to update publication types.
 */
class PageMaster_Form_Handler_Admin_Pubtypes extends Form_Handler
{
    var $tid;
    var $returnurl;

    /**
     * Initialize function
     */
    function initialize($view)
    {
        $tid = FormUtil::getPassedValue('tid');

        if (!empty($tid) && is_numeric($tid)) {
            $this->tid = $tid;

            $pubtype   = PageMaster_Util::getPubType($tid);
            $pubfields = PageMaster_Util::getPubFields($tid);

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
                    'text'  => $this->__('Creation date'),
                    'value' => 'cr_date'
                ),
                'core_lu_date' => array(
                    'text'  => $this->__('Update date'),
                    'value' => 'lu_date'
                ),
                'core_cr_uid' => array(
                    'text'  => $this->__('Creator'),
                    'value' => 'core_author'
                ),
                'core_lu_uid' => array(
                    'text'  => $this->__('Updater'),
                    'value' => 'lu_uid'
                ),
                'core_pu_date' => array(
                    'text'  => $this->__('Publish date'),
                    'value' => 'pm_publishdate'
                ),
                'core_ex_date' => array(
                    'text'  => $this->__('Expire date'),
                    'value' => 'pm_expiredate'
                ),
                'core_language' => array(
                    'text'  => $this->__('Language'),
                    'value' => 'pm_language'
                ),
                'core_hitcount' => array(
                    'text'  => $this->__('Number of reads'),
                    'value' => 'pm_hitcount'
                )
            );

            foreach ($pubfields as $fieldname => $pubfield) {
                $index = ($pubfield['istitle'] == 1) ? 'core_title' : $fieldname;
                $pubarr[$index] = array(
                    'text'  => $this->__($pubfield['title']),
                    'value' => $fieldname
                );
            }

            $pubarr = array_values(array_filter(array_merge($arraysort, $pubarr)));

            $view->assign('pubfields', $pubarr)
                 ->assign('pubtype', $pubtype->toArray());
        }

        // stores the return URL and the item URL
        if (empty($this->returnurl)) {
            $adminurl = ModUtil::url('PageMaster', 'admin');
            $this->returnurl = System::serverGetVar('HTTP_REFERER', $adminurl);
        }

        $pubtypes = PageMaster_Util::getPubType(-1);

        $workflows = PageMaster_Util::getWorkflowsOptionList();

        $view->assign('pmworkflows', $workflows)
             ->assign('pubtypes', $pubtypes);

        return true;
    }

    /**
     * Command handler
     */
    function handleCommand($view, &$args)
    {
        if ($args['commandName'] == 'cancel') {
            return $view->redirect($this->returnurl);
        }

        $data = $view->getValues();

        // creates and fill a Pubtype instance
        $pubtype = new PageMaster_Model_Pubtype();
        if (!empty($this->tid)) {
            $pubtype->assignIdentifier($this->tid);
        }
        $pubtype->fromArray($data{'pubtype'});

        // handle the commands
        switch ($args['commandName'])
        {
            // create a pubtype
            case 'create':
                if (!$view->isValid()) {
                    return false;
                }

                // cleanup some fields
                if (empty($pubtype->urltitle)) {
                    $pubtype->urltitle = DataUtil::formatPermalink($pubtype->title);
                }
                $pubtype->outputset = DataUtil::formatPermalink($pubtype->outputset);
                $pubtype->inputset  = DataUtil::formatPermalink($pubtype->inputset);

                // create/edit status messages
                if (empty($this->tid)) {
                    LogUtil::registerStatus($this->__('Done! Publication type created. Now you can proceed to define its fields.'));
                    $this->returnurl = ModUtil::url('PageMaster', 'admin', 'pubfields', array('tid' => $pubtype->tid));
                } else {
                    LogUtil::registerStatus($this->__('Done! Publication type updated.'));
                }
                $pubtype->save();
                break;

            // clone the current pubtype
            case 'clone':
                // clone the pubtype info
                $pubtype = PageMaster_Util::getPubType($this->tid);

                $newpubtype = $pubtype->copy();
                $newpubtype->title = $this->__f('%s Clon', $pubtype->title);
                $newpubtype->save();

                // clone the pubtype fields
                $pubfields = PageMaster_Util::getPubFields($this->tid);
                if ($pubfields) {
                    foreach ($pubfields as $pubfield) {
                        $pubfield = $pubfield->clone();
                        $pubfield->tid = $newpubtype->tid;
                        $pubfield->save();
                    }
                }

                LogUtil::registerStatus($this->__('Done! Publication type cloned.'));

                $this->returnurl = ModUtil::url('PageMaster', 'admin', 'pubtype', array('tid' => $newpubtype->tid));
                break;

            // delete
            case 'delete':
                // delete the pubtype data and fields
                PageMaster_Util::getPubType($this->tid)->delete();
                PageMaster_Util::getPubFields($this->tid)->delete();

                // also delete the data if available
                $existingtables = DBUtil::metaTables();
                if (in_array(DBUtil::getLimitedTablename('pagemaster_pubdata'.$this->tid), $existingtables)) {
                    DBUtil::dropTable('pagemaster_pubdata'.$this->tid);
                }
                // FIXME Delete related stuff is needed? Hooks, Workflows registries?

                LogUtil::registerStatus($this->__('Done! Publication type deleted.'));

                $this->returnurl = ModUtil::url('PageMaster', 'admin');
                break;
        }

        return $view->redirect($this->returnurl);
    }
}
