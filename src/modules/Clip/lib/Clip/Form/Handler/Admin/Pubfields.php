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
 * Form handler to update publication fields.
 */
class Clip_Form_Handler_Admin_Pubfields extends Zikula_Form_AbstractHandler
{
    private $tid;
    private $id;
    private $returnurl;

    /**
     * Initialize function
     */
    function initialize($view)
    {
        $this->tid = FormUtil::getPassedValue('tid');
        $this->id  = FormUtil::getPassedValue('id', null, 'GET', FILTER_SANITIZE_NUMBER_INT);

        // validation check
        if (!Clip_Util::validateTid($this->tid)) {
            $view->setErrorMsg($this->__f('Error! Invalid publication type ID passed [%s].', DataUtil::formatForDisplay($this->tid)));
            return $view->redirect(ModUtil::url('Clip', 'admin', 'main'));
        }

        $pubtype = Clip_Util::getPubType($this->tid);

        // update the pubtype table with previous changes
        Doctrine_Core::getTable('Clip_Model_Pubdata'.$this->tid)->changeTable(true);

        // get the pubfields table
        $tableObj = Doctrine_Core::getTable('Clip_Model_Pubfield');

        if (!empty($this->id)) {
            $pubfield = $tableObj->find($this->id);

            if (!$pubfield) {
                LogUtil::registerError($this->__f('Error! No such publication field [%s] found.', $this->id));
                return $view->redirect(ModUtil::url('Clip', 'admin'));
            }

            $view->assign('field', $pubfield->toArray());
        } else {
            $view->assign('field', $tableObj->getRecord()->toArray());
        }

        $pubfields = $tableObj->selectCollection("tid = '{$this->tid}'", 'lineno', -1, -1, 'name');

        $view->assign('pubfields', $pubfields)
             ->assign('pubtype', $pubtype)
             ->assign('tid', $tid);

        // stores the return URL
        if (!$view->getStateData('returnurl')) {
            $adminurl = ModUtil::url('Clip', 'admin', 'main');
            $view->setStateData('returnurl', System::serverGetVar('HTTP_REFERER', $adminurl));
        }

        return true;
    }

    /**
     * Command handler
     */
    function handleCommand($view, &$args)
    {
        $this->returnurl = $view->getStateData('returnurl');

        if ($args['commandName'] == 'cancel') {
            return $view->redirect($this->returnurl);
        }

        $data = $view->getValues();

        // creates a Pubfield instance
        if (!empty($this->id)) {
            // object fetch due the use of default values
            $pubfield = Doctrine_Core::getTable('Clip_Model_Pubfield')->find($this->id);
        } else {
            $pubfield = new Clip_Model_Pubfield();
        }
        $pubfield->fromArray($data['field']);

        // fill default data
        $plugin = Clip_Util::getPlugin($pubfield->fieldplugin);

        $pubfield->tid = (int)$this->tid;
        $pubfield->fieldtype = $plugin->columnDef;

        $this->returnurl = ModUtil::url('Clip', 'admin', 'pubfields',
                                        array('tid' => $pubfield->tid));

        // handle the commands
        switch ($args['commandName'])
        {
            // create a field
            case 'create':
                if (!$view->isValid()) {
                    return false;
                }

                $tableObj = Doctrine_Core::getTable('Clip_Model_Pubfield');

                // name restrictions
                $pubfield->name = str_replace("'", '', $pubfield->name);
                $pubfield->name = $submittedname = DataUtil::formatForStore($pubfield->name);

                // reserved names
                $reserved = array('module', 'func', 'type', 'tid', 'pid');
                if (in_array($submittedname, $reserved)) {
                    $plugin = $view->getPluginById('name');
                    $plugin->setError($this->__('The submitted name is reserved. Please choose a different one.'));
                    return false;
                }

                // check that the name is unique
                if (empty($this->id)) {
                    $where = "name = '$submittedname' AND tid = '{$pubfield->tid}'";
                } else {
                    $where = "id <> '{$this->id}' AND name = '$submittedname' AND tid = '{$pubfield->tid}'";
                }

                $nameUnique = (int)$tableObj->selectFieldFunction('id', 'COUNT', $where);
                if ($nameUnique > 0) {
                    $plugin = $view->getPluginById('name');
                    $plugin->setError($this->__('Another field already has this name.'));
                    return false;
                }

                // check that the new name is not another publication property
                if (empty($this->id)) {
                    $pubClass = 'Clip_Model_Pubdata'.$this->tid;
                    $pubObj   = new $pubClass();
                    if (isset($pubObj[$pubfield->name])) {
                        $plugin = $view->getPluginById('name');
                        $plugin->setError($this->__('The provided name is reserved for the publication standard fields.'));
                        return false;
                    }
                }

                // reset any other title field if this one is enabled
                if ($pubfield->istitle == true) {
                    $tableObj->createQuery()
                             ->update()
                             ->set('istitle', '0')
                             ->where('tid = ?', $pubfield->tid)
                             ->execute();
                }

                // force a titlefield
                $where = array(
                    array('tid = ?', $pubfield->tid)
                );
                $max_line = (int)$tableObj->selectFieldFunction('lineno', 'MAX', $where);
                if ($max_line == 0) {
                    $pubfield->istitle = true;
                }

                // create/edit status messages
                if (empty($this->id)) {
                    $pubfield->lineno = $max_line + 1;
                    LogUtil::registerStatus($this->__('Done! Field created.'));
                } else {
                    LogUtil::registerStatus($this->__('Done! Field updated.'));
                }
                $pubfield->save();
                break;

            // delete the field
            case 'delete':
                if ($pubfield->delete()) {
                    LogUtil::registerStatus($this->__('Done! Field deleted.'));
                } else {
                    return LogUtil::registerError($this->__('Error! Deletion attempt failed.'));
                }
                break;
        }

        return $view->redirect($this->returnurl);
    }
}
