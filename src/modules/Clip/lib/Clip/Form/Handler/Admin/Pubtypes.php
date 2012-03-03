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
class Clip_Form_Handler_Admin_Pubtypes extends Zikula_Form_AbstractHandler
{
    protected $tid;
    protected $referer;

    /**
     * Initialize function.
     */
    public function initialize(Zikula_Form_View $view)
    {
        $this->tid = FormUtil::getPassedValue('tid', null, 'GET', FILTER_SANITIZE_NUMBER_INT);

        // validate the tid if exists
        if ($this->tid && !Clip_Util::validateTid($this->tid)) {
            $view->setErrorMsg($this->__f('Error! Invalid publication type ID passed [%s].', $this->tid));
            return $view->redirect(ModUtil::url('Clip', 'admin', 'main'));
        }

        // edit a pubtype or create one
        if ($this->tid) {
            $pubtype   = Clip_Util::getPubType($this->tid);
            $pubfields = Clip_Util_Selectors::fields($this->tid);

            if (!$pubtype) {
                $view->setErrorMsg($this->__f('Error! No such publication type [%s] found.', $this->tid));
                return $view->redirect(ModUtil::url('Clip', 'admin', 'main'));
            }

            // assigns the pubfuelds for the sort configuration
            $view->assign('pubfields', $pubfields)
                 ->assign('config', $this->configPreProcess($pubtype['config']));

        } else {
            $pubtype = new Clip_Model_Pubtype();

            $view->assign('config', $this->configPreProcess(Clip_Util::getPubtypeConfig()));
        }

        $view->assign('clipworkflows', Clip_Util_Selectors::workflows())
             ->assign('pubtype', $pubtype->toArray());

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

        // get the table object for utility purposes
        $tbl = Doctrine_Core::getTable('Clip_Model_Pubtype');

        // get the data set in the form
        $data = $view->getValues();

        // creates and fill a Pubtype instance
        if ($this->tid) {
            // clone to avoid interferences of the Doctrine_Table cache
            $pubtype = $tbl->find($this->tid)->copy(true);
            $pubtype->assignIdentifier($this->tid);
        } else {
            $pubtype = new Clip_Model_Pubtype();
        }

        // update the pubtype with the form values
        $pubtype->fromArray($data['pubtype']);
        $pubtype->config = $this->configPostProcess($data['config']);

        // handle the commands
        switch ($args['commandName'])
        {
            // create/update a pubtype
            case 'save':
                if (!$view->isValid()) {
                    return false;
                }

                // cleanup some fields
                if (empty($pubtype->urltitle)) {
                    $pubtype->urltitle = DataUtil::formatPermalink($pubtype->title);
                } else {
                    $pubtype->urltitle = DataUtil::formatPermalink($pubtype->urltitle);
                }

                // reserved words check
                if (Clip_Util::validateReservedWord($pubtype->title)) {
                    return $view->setPluginErrorMsg('title', $this->__('The submitted value is a reserved word. Please choose a different one.'), array('text' => $pubtype->title));
                }
                if (Clip_Util::validateReservedWord($pubtype->urltitle)) {
                    return $view->setPluginErrorMsg('urltitle', $this->__('The submitted value is a reserved word. Please choose a different one.'), array('text' => $pubtype->urltitle));
                }

                // verify a unique title and urltitle
                $n = !$this->tid ? 0 : 1;
                if ($tbl->findBy('title', $pubtype->title)->count() > $n) {
                    return $view->setPluginErrorMsg('title', $this->__('The submitted value already exists. Please choose a different one.'), array('text' => $pubtype->title));
                }
                if ($tbl->findBy('urltitle', $pubtype->urltitle)->count() > $n) {
                    return $view->setPluginErrorMsg('urltitle', $this->__('The submitted value already exists. Please choose a different one.'), array('text' => $pubtype->urltitle));
                }

                // save the pubtype
                $pubtype->save();

                // create/update status messages
                if (!$this->tid) {
                    LogUtil::registerStatus($this->__('Done! Publication type created. Now you can proceed to define its fields.'));

                    $this->referer = new Clip_Url('Clip', 'admin', 'pubfields', array('tid' => $pubtype->tid));
                } else {
                    LogUtil::registerStatus($this->__('Done! Publication type updated.'));

                    $this->referer = new Clip_Url('Clip', 'admin', 'pubtypeinfo', array('tid' => $pubtype->tid));
                }
                break;

            // clone the current pubtype
            case 'clone':
                // clone the pubtype info
                $pubtype = Clip_Util::getPubType($this->tid);

                $newpubtype = $pubtype->copy(true);
                $newpubtype->title = $this->__f('%s Clon', $pubtype->title);
                // be sure that title is unique
                while ($tbl->findBy('title', $newpubtype->title)->count() || Clip_Util::validateReservedWord($newpubtype->title)) {
                    $newpubtype->title++;
                }
                // and also the urltitle
                while ($tbl->findBy('urltitle', $newpubtype->urltitle)->count() || Clip_Util::validateReservedWord($newpubtype->urltitle)) {
                    $newpubtype->urltitle++;
                }

                // save the new pubtype to get the new tid
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

                // update the table with all the fields inserted
                $newpubtype->updateTable();

                // status message
                LogUtil::registerStatus($this->__('Done! Publication type cloned.'));

                // redirect to pubfields to update the table
                $this->referer = new Clip_Url('Clip', 'admin', 'pubfields', array('tid' => $newpubtype->tid));
                break;

            // delete this pubtype
            case 'delete':
                // delete the pubtype
                Clip_Util::getPubType($this->tid)->delete();

                // status message
                LogUtil::registerStatus($this->__('Done! Publication type deleted.'));

                $this->referer = ModUtil::url('Clip', 'admin', 'main');
                break;
        }

        // stop here if the request is ajax based
        if ($isAjax) {
            if ($this->referer instanceof Clip_Url) {
                $response = array(
                                  'func' => $this->referer->getAction(),
                                  'pars' => $this->referer->getArgs()
                                 );
            } else {
                $response = array('redirect' => $this->referer);
            }

            return new Zikula_Response_Ajax_Json($response);
        }

        // redirect to the determined url
        return $view->redirect($this->referer instanceof Clip_Url ? $this->referer->getUrl() : $this->referer);
    }

    /**
     * Utility methods.
     */
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
