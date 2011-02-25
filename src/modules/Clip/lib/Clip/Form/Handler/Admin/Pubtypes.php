<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Form_Handler_Admin
 */

/**
 * Form handler to update publication types.
 */
class Clip_Form_Handler_Admin_Pubtypes extends Zikula_Form_Handler
{
    private $tid;
    private $returnurl;

    /**
     * Initialize function
     */
    function initialize($view)
    {
        $tid = (int)FormUtil::getPassedValue('tid');

        if (!empty($tid) && is_numeric($tid)) {
            $this->tid = $tid;

            $pubtype   = Clip_Util::getPubType($tid);
            $pubfields = Clip_Util::getFieldsSelector($tid);

            if (!$pubtype) {
                LogUtil::registerError($this->__f('Error! No such publication type [%s] found.', $tid));
                return $view->redirect(ModUtil::url('Clip', 'admin'));
            }

            $view->assign('pubfields', $pubfields)
                 ->assign('pubtype', $pubtype->toArray())
                 ->assign('config', $this->configPreProcess($pubtype['config']));
        } else {
            $view->assign('config', $this->configPreProcess($this->configDefault()));
        }

        // stores the return URL
        if (empty($this->returnurl)) {
            $adminurl = ModUtil::url('Clip', 'admin');
            $this->returnurl = System::serverGetVar('HTTP_REFERER', $adminurl);
        }

        $view->assign('clipworkflows', Clip_Util::getWorkflowsOptionList());

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
        $pubtype = new Clip_Model_Pubtype();
        if (!empty($this->tid)) {
            $pubtype->assignIdentifier($this->tid);
        }
        $pubtype->fromArray($data['pubtype']);
        $pubtype->config = $this->configPostProcess($data['config']);

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
                $pubtype->save();

                // create/edit status messages
                if (empty($this->tid)) {
                    // create the table
                    Clip_Generator::loadDataClasses(true);
                    Doctrine_Core::getTable('Clip_Model_Pubdata'.$pubtype->tid)->createTable();

                    LogUtil::registerStatus($this->__('Done! Publication type created. Now you can proceed to define its fields.'));
                    $this->returnurl = ModUtil::url('Clip', 'admin', 'pubfields', array('tid' => $pubtype->tid));
                } else {
                    LogUtil::registerStatus($this->__('Done! Publication type updated.'));
                }
                break;

            // clone the current pubtype
            case 'clone':
                // clone the pubtype info
                $pubtype = Clip_Util::getPubType($this->tid);

                $newpubtype = $pubtype->copy();
                $newpubtype->title = $this->__f('%s Clon', $pubtype->title);
                $newpubtype->save();

                // clone the pubtype fields
                $pubfields = Clip_Util::getPubFields($this->tid);
                if ($pubfields) {
                    foreach ($pubfields as $pubfield) {
                        $pubfield = $pubfield->copy();
                        $pubfield->tid = $newpubtype->tid;
                        $pubfield->save();
                    }
                }

                // create the cloned table
                Clip_Generator::loadDataClasses(true);
                Doctrine_Core::getTable('Clip_Model_Pubdata'.$newpubtype->tid)->createTable();

                // status message
                LogUtil::registerStatus($this->__('Done! Publication type cloned.'));

                $this->returnurl = ModUtil::url('Clip', 'admin', 'pubtype', array('tid' => $newpubtype->tid));
                break;

            // delete
            case 'delete':
                // delete the pubtype data and fields
                Clip_Util::getPubType($this->tid)->delete();
                Clip_Util::getPubFields($this->tid)->delete();

                // delete any relation
                $where = array("tid1 = '{$this->tid}' OR tid2 = '{$this->tid}'");
                Doctrine_Core::getTable('Clip_Model_Pubrelation')->deleteWhere($where);
                // FIXME m2m relations needs something more?

                // delete the data table
                Doctrine_Core::getTable('Clip_Model_Pubdata'.$this->tid)->dropTable();
                // FIXME Delete related stuff is needed? Hooks, Workflows registries?

                // status message
                LogUtil::registerStatus($this->__('Done! Publication type deleted.'));

                $this->returnurl = ModUtil::url('Clip', 'admin');
                break;
        }

        return $view->redirect($this->returnurl);
    }

    /**
     * Utility methods.
     */
    private function configDefault()
    {
        $result = array(
            'view' => array(
                'load' => false,
                'onlyown' => true,
                'processrefs' => false,
                'checkperm' => false,
                'handleplugins' => false,
                'loadworkflow' => false
            ),
            'display' => array(
                'load' => true,
                'onlyown' => true,
                'processrefs' => true,
                'checkperm' => true,
                'handleplugins' => false,
                'loadworkflow' => false
            ),
            'edit' => array(
                'onlyown' => true
            )
        );

        return $result;
    }

    private function configPreProcess($config)
    {
        $result = array();

        foreach ($config as $j => $c) {
            foreach ($c as $k => $v) {
                $result["$j.$k"] = (int)$v;
            }
        }

        return $result;
    }

    private function configPostProcess($config)
    {
        $result = array();

        foreach ($config as $k => $v) {
            if (strpos($k, '.') !== false) {
                list($k0, $k1) = explode('.', $k);
                $result[$k0][$k1] = (bool)$v;
            } else {
                $result[$k] = (bool)$v;
            }
        }

        return $result;
    }
}
