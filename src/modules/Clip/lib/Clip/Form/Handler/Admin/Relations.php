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
 * Form handler to manage the relations.
 */
class Clip_Form_Handler_Admin_Relations extends Form_Handler
{
    private $id;
    private $returnurl;

    /**
     * Initialize function
     */
    function initialize($view)
    {
        $pubtypes = Clip_Util::getPubType(-1);

        if (count($pubtypes) == 0) {
            LogUtil::registerError($this->__('There are no publication types to relate.'));

            return $view->redirect(ModUtil::url('Clip', 'admin', 'pubtypes'));
        }

        // check if there are tables to update
        $tids = array_keys($pubtypes->toArray());
        $update = FormUtil::getPassedValue('update');

        foreach (explode(',', $update) as $tid) {
            if (in_array($tid, $tids)) {
                Doctrine_Core::getTable('Clip_Model_Pubdata'.$tid)->changeTable(true);
            }
        }

        // process the handler values
        $id  = (int)FormUtil::getPassedValue('id', 0);
        $tid = FormUtil::getPassedValue('tid');
        $tid = in_array($tid, $tids) ? $tid : null;

        $tableObj = Doctrine_Core::getTable('Clip_Model_Pubrelation');

        if (!empty($id)) {
            $this->id = $id;
            $relation = $tableObj->find($id);

            if (!$relation) {
                LogUtil::registerError($this->__f('No such relation found [%s].', $id));

                return $view->redirect(ModUtil::url('Clip', 'admin', 'relations'));
            }

            $relation->mapValue('type1', $relation->type < 2 ? 0 : 1);
            $relation->mapValue('type2', $relation->type%2 == 0 ? 0 : 1);

            // update the implied pubdata tables
            Doctrine_Core::getTable('Clip_Model_Pubdata'.$relation->tid1)->changeTable(true);
            Doctrine_Core::getTable('Clip_Model_Pubdata'.$relation->tid2)->changeTable(true);

            $view->assign('relation', $relation->toArray());
        }

        $where     = $tid ? "tid1 = '$tid'" : '';
        $relations = $tableObj->selectCollection($where, 'tid1 ASC, tid2 ASC', -1, -1, 'id');

        $reltype1 = array(
            array(
                'text'  => $this->__('One'),
                'value' => 0
            ),
            array(
                'text'  => $this->__('Many'),
                'value' => 1
            )
        );
        $reltype2 = array(
            array(
                'text'  => $this->__('has One'),
                'value' => 0
            ),
            array(
                'text'  => $this->__('has Many'),
                'value' => 1
            )
        );

        $view->assign('pubtypes', $pubtypes)
             ->assign('typeselector', Clip_Util::getPubtypesSelector(true, false))
             ->assign('relations', $relations)
             ->assign('reltypes', array($reltype1, $reltype2))
             ->assign('tid', $tid);

        // stores the return URL
        if (empty($this->returnurl)) {
            $returnurl = ModUtil::url('Clip', 'admin', 'relations',
                                      array('tid' => $tid));
            $this->returnurl = System::serverGetVar('HTTP_REFERER', $returnurl);
        }

        return true;
    }

    /**
     * Command handler
     */
    function handleCommand($view, $args)
    {
        if ($args['commandName'] == 'cancel') {
            return $view->redirect($this->returnurl);
        }

        $data = $view->getValues();

        // creates and fill a Pubfield instance
        $relation = new Clip_Model_Pubrelation();
        if (!empty($this->id)) {
            $relation->assignIdentifier($this->id);
        }
        $relation->fromArray($data['relation']);

        // fill default data
        $relation->type = bindec("{$data['relation']['type1']}{$data['relation']['type2']}");

        // handle the commands
        switch ($args['commandName'])
        {
            // create a field
            case 'create':
                if (!$view->isValid()) {
                    return false;
                }

                $tableObj = Doctrine_Core::getTable('Clip_Model_Pubrelation');

                // TODO verify unique alias too
                // check it's unique
                $where = array(
                    array('type = ?', $relation->type),
                    array('tid1 = ?', $relation->tid1),
                    array('tid2 = ?', $relation->tid2)
                );
                if (!empty($this->id)) {
                    $where[] = array('id <> ?', $this->id);
                }

                $isUnique = (int)$tableObj->selectFieldFunction('id', 'COUNT', $where);
                if ($isUnique > 0) {
                    $plugin = $view->getPluginById('tid1');
                    $plugin->setError($this->__('This relation already exists.'));
                    return false;
                }

                // detect a type change for m2m before save
                $previous = $tableObj->find($this->id);
                if ($previous->type != $relation->type && $previous->type == 3) {
                    Doctrine_Core::getTable('Clip_Model_Relation'.$this->id)->dropTable();
                }

                $relation->save();

                // create/edit status messages
                if (empty($this->id)) {
                    // create the table
                    Clip_Generator::loadDataClasses(true);
                    if ($relation->type == 3) {
                        Doctrine_Core::getTable('Clip_Model_Relation'.$relation->id)->createTable();
                    }
                    // setup the return url as the edit form
                    // to update the corresponding tables
                    $this->returnurl = ModUtil::url('Clip', 'admin', 'relations',
                                                    array('update' => $relation->tid1.','.$relation->tid2));

                    LogUtil::registerStatus($this->__('Done! Relation created.'));
                } else {
                    LogUtil::registerStatus($this->__('Done! Relation updated.'));
                }
                break;

            // delete the field
            case 'delete':
                $relation = Doctrine_Core::getTable('Clip_Model_Pubrelation')->find($this->id);

                if ($relation->delete()) {
                    $this->returnurl = ModUtil::url('Clip', 'admin', 'relations',
                                                    array('update' => $relation->tid1.','.$relation->tid2));

                    LogUtil::registerStatus($this->__('Done! Relation deleted.'));
                } else {
                    return LogUtil::registerError($this->__('Error! Deletion attempt failed.'));
                }
                break;
        }

        return $view->redirect($this->returnurl);
    }
}
