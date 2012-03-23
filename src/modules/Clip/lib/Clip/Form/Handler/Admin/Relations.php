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
class Clip_Form_Handler_Admin_Relations extends Zikula_Form_AbstractHandler
{
    protected $id;
    protected $filter;
    protected $referer;

    /**
     * Initialize function.
     */
    public function initialize(Zikula_Form_View $view)
    {
        $pubtypes = Clip_Util::getPubType(-1);

        if (count($pubtypes) == 0) {
            LogUtil::registerError($this->__('There are no publication types to relate.'));

            return $view->redirect(ModUtil::url('Clip', 'admin', 'main'));
        }

        // check if there are tables to update
        $tids = $pubtypes->getKeys();

        // process the handler values
        $this->id = FormUtil::getPassedValue('id', 0, 'GET', FILTER_SANITIZE_NUMBER_INT);

        $tid  = FormUtil::getPassedValue('tid', null, 'GET', FILTER_SANITIZE_NUMBER_INT);
        $tid1 = FormUtil::getPassedValue('withtid1', null, 'GET');
        $op   = FormUtil::getPassedValue('op', 'or', 'GET');
        $tid2 = FormUtil::getPassedValue('withtid2', null, 'GET');

        $tid  = (in_array($tid, $tids) ? $tid : null);
        $tid1 = is_array($tid1) ? current($tid1) : (in_array($tid1, $tids) ? $tid1 : null);
        $op   = is_array($op) ? current($op) : $op;
        $tid2 = is_array($tid2) ? current($tid2) : (in_array($tid2, $tids) ? $tid2 : null);

        // get the table object for utility purposes
        $tableObj = Doctrine_Core::getTable('Clip_Model_Pubrelation');

        if ($this->id) {
            $relation = $tableObj->find($this->id);

            if (!$relation) {
                LogUtil::registerError($this->__f('No such relation found [%s].', $this->id));

                return $view->redirect(ModUtil::url('Clip', 'admin', 'relations'));
            }

            $relation->mapValue('type1', $relation->type < 2 ? 0 : 1);
            $relation->mapValue('type2', $relation->type%2 == 0 ? 0 : 1);

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

        $this->filter = array('withtid1' => $tid1, 'op' => $op, 'withtid2' => $tid2);

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
        $view->assign('pubtypes', $pubtypes->toArray())
             ->assign('typeselector', Clip_Util_Selectors::pubtypes(true, true))
             ->assign('relations', $relations)
             ->assign('reltypes', array($reltype1, $reltype2))
             ->assign('ops', $ops)
             ->assign('tid', $tid)
             ->assign('filter', $this->filter);

        // stores the return URL and filter
        if (!$view->getStateData('referer')) {
            $view->setStateData('referer', ModUtil::url('Clip', 'admin', 'relations', $this->filter));
        }

        $view->setStateData('filter', $this->filter)
             ->setStateData('tid', $tid);

        return true;
    }

    /**
     * Command handler.
     */
    public function handleCommand(Zikula_Form_View $view, &$args)
    {
        $tid = $view->getStateData('tid');
        $isAjax = $view->getType() == 'ajax';
        $this->filter  = $view->getStateData('filter');
        $this->filter  = array_merge(array('tid' => $tid), $this->filter);
        $this->referer = $view->getStateData('referer');

        // cancel processing
        if ($args['commandName'] == 'cancel') {
            if ($isAjax) {
                return new Zikula_Response_Ajax_Json(array('cancel' => true));
            }
            return $view->redirect($this->referer);
        }

        // get the data set in the form
        $data = $view->getValues();

        // load the relation object for the specific commands
        if (in_array($args['commandName'], array('save', 'delete'))) {
            // creates and fill a Relation instance
            $relation = new Clip_Model_Pubrelation();
            if ($this->id) {
                $relation->assignIdentifier($this->id);
            } else {
                // build the decimal type value on creation only
                $relation->type = bindec("{$data['relation']['type1']}{$data['relation']['type2']}");
            }
            $relation->fromArray($data['relation']);
        }

        // handle the commands
        switch ($args['commandName'])
        {
            // create/update a relation
            case 'save':
                if (!$view->isValid()) {
                    return false;
                }

                // verify not duplicated alias
                if (!$this->id && $relation->tid1 == $relation->tid2 && $relation->alias1 == $relation->alias2) {
                    return $view->setPluginErrorMsg('alias1', $this->__('The aliases cannot be the same.'));
                }

                $previous = $this->id ? Doctrine_Core::getTable('Clip_Model_Pubrelation')->find($this->id) : null;

                // verify unique alias1
                if (!$this->id || $relation->alias1 != $previous->alias1) {
                    if (!Clip_Util::validateTid($relation->tid1)) {
                        return $view->setPluginErrorMsg('tid1', $this->__('Invalid owning publication type passed.'));
                    } else {
                        $pub = Doctrine_Core::getTable('ClipModels_Pubdata'.$relation->tid1)->getRecord();
                        if (array_key_exists($relation->alias1, $pub->pubFields())) {
                            return $view->setPluginErrorMsg('alias1', $this->__f("The alias '%s' is already in use.", $relation->alias1));
                        }
                    }
                }

                // verify unique alias2
                if (!$this->id || $relation->alias2 != $previous->alias2) {
                    if (!Clip_Util::validateTid($relation->tid2)) {
                        return $view->setPluginErrorMsg('tid2', $this->__('Invalid related publication type passed.'));
                    } else {
                        $pub = Doctrine_Core::getTable('ClipModels_Pubdata'.$relation->tid2)->getRecord();
                        if (array_key_exists($relation->alias2, $pub->pubFields())) {
                            return $view->setPluginErrorMsg('alias2', $this->__f("The alias '%s' is already in use.", $relation->alias2));
                        }
                    }
                }
                /*
                if ($this->id) {
                    // TODO support relation definition transitions
                    // detect a type change for m2m before save
                    if ($previous->type != $relation->type && $previous->type == 3) {
                        Doctrine_Core::getTable('ClipModels_Relation'.$this->id)->dropTable();
                    }
                }
                */
                $relation->save();

                // create/update status messages
                if (!$this->id) {
                    LogUtil::registerStatus($this->__('Done! Relation created.'));
                } else {
                    LogUtil::registerStatus($this->__('Done! Relation updated.'));
                }
                break;

            // delete the relation
            case 'delete':
                $relation = Doctrine_Core::getTable('Clip_Model_Pubrelation')->find($this->id);

                if ($relation->delete()) {
                    LogUtil::registerStatus($this->__('Done! Relation deleted.'));
                } else {
                    return LogUtil::registerError($this->__('Error! Deletion attempt failed.'));
                }
                break;

            // filter relation list
            case 'filter':
                $params = array_merge(array('tid' => $tid), $data['filter']);
                $this->referer = new Clip_Url('Clip', 'admin', 'relations', $params);
                break;

            // clear any filter
            case 'clear':
                $this->referer = new Clip_Url('Clip', 'admin', 'relations', array('tid' => $tid));
                break;
        }

        if ($isAjax) {
            if ($this->referer instanceof Clip_Url) {
                $response = array('func' => $this->referer->getAction(), 'pars' => $this->referer->getArgs());
            } else {
                $response = array('func' => 'relations', 'pars' => array_merge(array('tid' => $tid), $this->filter));
            }

            return new Zikula_Response_Ajax_Json($response);
        }

        return $view->redirect($this->referer instanceof Clip_Url ? $this->referer->getUrl() : $this->referer);
    }
}
