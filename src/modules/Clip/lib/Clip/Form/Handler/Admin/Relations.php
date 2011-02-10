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
    private $tid;
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
        $id   = (int)FormUtil::getPassedValue('id', 0);
        $tid  = FormUtil::getPassedValue('tid');
        $tid1 = FormUtil::getPassedValue('tid1');
        $op   = FormUtil::getPassedValue('op', 'or');
        $tid2 = FormUtil::getPassedValue('tid2');

        $this->tid = in_array($tid, $tids) ? $tid : null;

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

        // get the relations list
        $where = array();
        if (in_array($tid1, $tids)) {
            $where[] = array('tid1 = ?', $tid1);
        }
        if (in_array($tid2, $tids)) {
            $key = in_array($op, array('and', 'or')) ? (!empty($where) ? $op.'Where' : 0) : 1;
            $where[$key] = array('tid2 = ?', $tid2);
        }

        $relations = $tableObj->selectCollection($where, 'tid1 ASC, tid2 ASC', -1, -1, 'id');

        // options for dropdowns
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
        $ops = array(
            array(
                'text'  => $this->__('or'),
                'value' => 'or'
            ),
            array(
                'text'  => $this->__('and'),
                'value' => 'and'
            )
        );

        // fill the view
        $view->assign('pubtypes', $pubtypes)
             ->assign('typeselector', Clip_Util::getPubtypesSelector(true, true))
             ->assign('relations', $relations)
             ->assign('reltypes', array($reltype1, $reltype2))
             ->assign('ops', $ops)
             ->assign('tid', $tid)
             ->assign('filter', array('tid1' => $tid1, 'op' => $op, 'tid2' => $tid2));

        // stores the return URL
        if (empty($this->returnurl)) {
            $returnurl = ModUtil::url('Clip', 'admin', 'relations',
                                      array('tid' => $this->tid));
            $this->returnurl = System::serverGetVar('HTTP_REFERER', $returnurl);
        }

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

        if ($data['relation']['tid1']) {
            // creates and fill a Relation instance
            $relation = new Clip_Model_Pubrelation();
            if (!empty($this->id)) {
                $relation->assignIdentifier($this->id);
            }
            $relation->fromArray($data['relation']);

            // fill default data
            $relation->type = bindec("{$data['relation']['type1']}{$data['relation']['type2']}");
        }

        // handle the commands
        switch ($args['commandName'])
        {
            // create a relation
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
                if (!empty($this->id)) {
                    // TODO support relation definition transitions
                    $previous = $tableObj->find($this->id);
                    if ($previous->type != $relation->type && $previous->type == 3) {
                        Doctrine_Core::getTable('Clip_Model_Relation'.$this->id)->dropTable();
                    }
                }

                $relation->save();

                // create/edit status messages
                if (empty($this->id)) {
                    // create the table
                    Clip_Generator::evalrelations();
                    if ($relation->type == 3) {
                        Doctrine_Core::getTable('Clip_Model_Relation'.$relation->id)->createTable();
                    }
                    // setup the return url as the edit form
                    // to update the corresponding tables
                    $this->returnurl = ModUtil::url('Clip', 'admin', 'relations',
                                                    array('tid'    => $this->tid,
                                                          'update' => $relation->tid1.','.$relation->tid2));

                    LogUtil::registerStatus($this->__('Done! Relation created.'));
                } else {
                    LogUtil::registerStatus($this->__('Done! Relation updated.'));
                }
                break;

            // delete the relation
            case 'delete':
                $relation = Doctrine_Core::getTable('Clip_Model_Pubrelation')->find($this->id);

                if ($relation->delete()) {
                    $this->returnurl = ModUtil::url('Clip', 'admin', 'relations',
                                                    array('tid'    => $this->tid,
                                                          'update' => $relation->tid1.','.$relation->tid2));

                    LogUtil::registerStatus($this->__('Done! Relation deleted.'));
                } else {
                    return LogUtil::registerError($this->__('Error! Deletion attempt failed.'));
                }
                break;

            // filter relation list
            case 'filter':
                $data['filter']['tid'] = $this->tid;
                $this->returnurl = ModUtil::url('Clip', 'admin', 'relations', $data['filter']);
                break;

            // clear any filter
            case 'clear':
                $this->returnurl = ModUtil::url('Clip', 'admin', 'relations');
                break;
        }

        return $view->redirect($this->returnurl);
    }
}
