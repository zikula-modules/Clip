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
    protected $tid;
    protected $id;
    protected $returnurl;

    /**
     * Initialize function.
     */
    public function initialize(Zikula_Form_View $view)
    {
        $this->tid = FormUtil::getPassedValue('tid', null, 'GET', FILTER_SANITIZE_NUMBER_INT);
        $this->id  = FormUtil::getPassedValue('id', null, 'GET', FILTER_SANITIZE_NUMBER_INT);

        // validate the tid
        if (!Clip_Util::validateTid($this->tid)) {
            $view->setErrorMsg($this->__f('Error! Invalid publication type ID passed [%s].', $this->tid));
            return $view->redirect(ModUtil::url('Clip', 'admin', 'main'));
        }

        // get the pubtype object
        $pubtype = Clip_Util::getPubType($this->tid);

        // update the pubtype table with previous changes
        Doctrine_Core::getTable('Clip_Model_Pubdata'.$this->tid)->changeTable(true);

        // get the pubfields table
        $tableObj = Doctrine_Core::getTable('Clip_Model_Pubfield');

        // set the field information
        if (!empty($this->id)) {
            $pubfield = $tableObj->find($this->id);

            if (!$pubfield) {
                $view->setErrorMsg($this->__f('Error! No such publication field [%s] found.', $this->id));
                return $view->redirect(ModUtil::url('Clip', 'admin', 'main'));
            }

            $view->assign('field', $pubfield->toArray());
        } else {
            $view->assign('field', $tableObj->getRecord()->toArray());
        }

        // assign all the existing fields
        $pubfields = Clip_Util::getPubFields($this->tid);

        $view->assign('pubfields', $pubfields)
             ->assign('pubtype', $pubtype)
             ->assign('tid', $this->tid);

        // stores the return URL
        if (!$view->getStateData('returnurl')) {
            $adminurl = ModUtil::url('Clip', 'admin', 'main');
            $view->setStateData('returnurl', System::serverGetVar('HTTP_REFERER', $adminurl));
        }

        return true;
    }

    /**
     * Command handler.
     */
    public function handleCommand(Zikula_Form_View $view, &$args)
    {
        $this->returnurl = $view->getStateData('returnurl');

        // cancel processing
        if ($args['commandName'] == 'cancel') {
            return $view->redirect($this->returnurl);
        }

        // get the data set in the form
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
        $plugin = Clip_Util_Plugins::get($pubfield->fieldplugin);

        $pubfield->tid       = $this->tid;
        $pubfield->fieldtype = $plugin->columnDef;

        $this->returnurl = ModUtil::url('Clip', 'admin', 'pubfields',
                                        array('tid' => $pubfield->tid));

        // handle the commands
        switch ($args['commandName'])
        {
            // create/update a field
            case 'save':
                if (!$view->isValid()) {
                    return false;
                }

                $tableObj = Doctrine_Core::getTable('Clip_Model_Pubfield');

                // name restrictions
                $pubfield->name = str_replace("'", '', $pubfield->name);
                $pubfield->name = DataUtil::formatForStore($pubfield->name);

                // reserved names
                if (Clip_Util::validateReservedWord($pubfield->name)) {
                    return $view->setPluginErrorMsg('name', $this->__('The submitted name is reserved. Please choose a different one.'));
                }

                // check that the name is unique
                if (empty($this->id)) {
                    $where = "name = '{$pubfield->name}' AND tid = '{$pubfield->tid}'";
                } else {
                    $where = "id <> '{$this->id}' AND name = '{$pubfield->name}' AND tid = '{$pubfield->tid}'";
                }

                $nameUnique = (int)$tableObj->selectFieldFunction('id', 'COUNT', $where);
                if ($nameUnique > 0) {
                    return $view->setPluginErrorMsg('name', $this->__('Another field already has this name.'));
                }

                // check that the new name is not another publication property
                if (empty($this->id)) {
                    $pubClass = 'Clip_Model_Pubdata'.$this->tid;
                    $pubObj   = new $pubClass();
                    if ($pubObj->contains($pubfield->name)) {
                        return $view->setPluginErrorMsg('name', $this->__('The provided name is reserved for the publication standard fields.'));
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
