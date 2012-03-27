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
    protected $referer;

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

        // get the pubfields table
        $tableObj = Doctrine_Core::getTable('Clip_Model_Pubfield');

        // set the field information
        if ($this->id) {
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
        $pubfields = Clip_Util::getPubFields($this->tid)->toArray();

        $view->assign('pubfields', $pubfields)
             ->assign('pubtype', $pubtype)
             ->assign('tid', $this->tid);

        // stores the return URL
        if (!$view->getStateData('referer')) {
            $adminurl = ModUtil::url('Clip', 'admin', 'main');
            $view->setStateData('referer', System::serverGetVar('HTTP_REFERER', $adminurl));
        }

        return true;
    }

    /**
     * Command handler.
     */
    public function handleCommand(Zikula_Form_View $view, &$args)
    {
        $isAjax = $view->getType() == 'ajax';
        $this->referer = $view->getStateData('referer');

        // cancel processing
        if ($args['commandName'] == 'cancel') {
            if ($isAjax) {
                return new Zikula_Response_Ajax_Json(array('cancel' => true));
            }
            return $view->redirect($this->referer);
        }

        $tableObj = Doctrine_Core::getTable('Clip_Model_Pubfield');

        // get the data set in the form
        $data = $view->getValues();

        // creates a Pubfield instance
        if ($this->id) {
            // object fetch due the use of default values
            $pubfield = $tableObj->find($this->id)->copy(true);
            $pubfield->assignIdentifier($this->id);
        } else {
            $pubfield = new Clip_Model_Pubfield();
        }

        $pubfield->fromArray($data['field']);

        // fill default data
        $plugin = Clip_Util_Plugins::get($pubfield->fieldplugin);

        $pubfield->tid       = $this->tid;
        $pubfield->fieldtype = $plugin->columnDef;

        $this->referer = ModUtil::url('Clip', 'admin', 'pubfields',
                                      array('tid' => $pubfield->tid));

        // handle the commands
        switch ($args['commandName'])
        {
            // create/update a field
            case 'save':
                if (!$view->isValid()) {
                    return false;
                }

                $previous = $this->id ? $tableObj->find($this->id) : null;

                // name restrictions
                $pubfield->name = str_replace("'", '', $pubfield->name);
                $pubfield->name = DataUtil::formatForStore($pubfield->name);

                // reserved names
                if (Clip_Util::validateReservedWord($pubfield->name)) {
                    return $view->setPluginErrorMsg('name', $this->__('The submitted name is reserved. Please choose a different one.'));
                }

                $pubClass = 'ClipModels_Pubdata'.$pubfield->tid;
                $pubObj   = Doctrine_Core::getTable($pubClass)->getRecord();

                // check that the name is not in use
                if (!$this->id || $pubfield->name != $previous->name) {
                    if (array_key_exists($pubfield->name, $pubObj->pubFields())) {
                        return $view->setPluginErrorMsg('name', $this->__f("The name '%s' is already in use.", $pubfield->name));
                    }
                }

                // check that the new name is not another publication property
                if (!$this->id && $pubObj->contains($pubfield->name)) {
                    return $view->setPluginErrorMsg('name', $this->__('The provided name is reserved for the publication standard fields.'));
                }

                // reset any other title field if this one is enabled
                if ($pubfield->istitle == true) {
                    $q = $tableObj->createQuery()
                                  ->update()
                                  ->set('istitle', '0')
                                  ->where('tid = ?', $pubfield->tid);

                    if ($this->id) {
                        $q->andWhere('id <> ?', $this->id);
                    }

                    $q->execute();
                }

                $pubfield->save();

                // update the pubtype table
                Clip_Util::getPubType($this->tid)->updateTable();

                // create/edit status messages
                if (!$this->id) {
                    LogUtil::registerStatus($this->__('Done! Field created.'));
                } else {
                    LogUtil::registerStatus($this->__('Done! Field updated.'));
                }
                break;

            // delete the field
            case 'delete':
                if ($pubfield->delete()) {
                    LogUtil::registerStatus($this->__f("Done! Field '%s' deleted.", $pubfield->name));
                } else {
                    LogUtil::registerError($this->__('Error! Deletion attempt failed.'));
                }
                break;
        }

        // stop here if the request is ajax based
        if ($isAjax) {
            return new Zikula_Response_Ajax_Json(array('func' => 'pubfields', 'pars' => array('tid' => $this->tid)));
        }

        // redirect to the determined url
        return $view->redirect($this->referer instanceof Clip_Url ? $this->referer->getUrl() : $this->referer);
    }
}
