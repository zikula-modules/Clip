<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Form_Handler_User
 */

/**
 * Form handler to update publications.
 */
class Clip_Form_Handler_User_Pubedit extends Form_Handler
{
    private $id;
    private $pub;
    private $relations;

    private $tid;
    private $pubtype;
    private $pubfields;
    private $itemurl;
    private $referer;
    private $goto;

    function initialize($view)
    {
        //// Parameters
        // process the input parameters
        $this->tid  = (isset($this->pubtype['tid']) && $this->pubtype['tid'] > 0) ? $this->pubtype['tid'] : FormUtil::getPassedValue('tid');
        $this->goto = FormUtil::getPassedValue('goto', '');

        //// Initialize
        // process a new or existing pub, and it's available actions
        if (!empty($this->id)) {
            $pubdata = ModUtil::apiFunc('Clip', 'user', 'get', array(
                'tid' => $this->tid,
                'id'  => $this->id,
                'checkperm' => true,
                'handleplugins' => false, // do not interfer with selected values on edit form
                'loadworkflow' => true
            ));

            // validate the pudblication
            if (!$pubdata) {
                LogUtil::registerError($this->__f('Error! No such publication [%s - %s] found.', array($this->tid, $this->id)));

                return $view->redirect(ModUtil::url('Clip', 'user', 'view', array('tid' => $this->tid)));
            }

            $this->pubAssign($pubdata);

            $actions = Zikula_Workflow_Util::getActionsForObject($pubdata, $this->pubtype->getTableName(), 'id', 'Clip');
        } else {
            // initial values
            $classname = 'Clip_Model_Pubdata'.$this->tid;
            $pubdata = new $classname();

            $this->pubDefault($pubdata);

            $actions = Zikula_Workflow_Util::getActionsByStateArray(str_replace('.xml', '', $this->pubtype->workflow), 'Clip');
        }

        //// Validation
        // if there are no actions the user is not allowed to change / submit / delete something.
        // We will redirect the user to the overview page
        if (count($actions) < 1) {
            LogUtil::registerError($this->__('No workflow actions found. This can be a permissions issue.'));

            return $view->redirect(ModUtil::url('Clip', 'user', 'view', array('tid' => $this->tid)));
        }

        // translate any gt string on the action parameters
        foreach (array_keys($actions) as $aid) {
            if (isset($actions[$aid]['parameters'])) {
                // check if the action parameter is translatable
                foreach (array_keys($actions[$aid]['parameters']) as $pname) {
                    foreach ($actions[$aid]['parameters'][$pname] as $k => $v) {
                        if (strpos($k, '__') === 0) {
                            unset($actions[$aid]['parameters'][$pname][$k]);
                            $k = substr($k, 2);
                            $actions[$aid]['parameters'][$pname][$k] = $this->__($v);
                        }
                    }
                }
                // set the button title with the description if not set
                if (!isset($actions[$aid]['parameters']['button']['title'])) {
                    $actions[$aid]['parameters']['button']['title'] = $this->__($actions[$aid]['description']);
                }
            }
        }

        //// Processing
        // handle the Doctrine_Record data as an array from here
        $data = $pubdata->toArray();

        // detect the relations
        $this->relations = array();
        // TODO fix this value through config
        $onlyown = false;

        foreach ($pubdata->getRelations($onlyown) as $key => $rel) {
            // set the data object
            $data[$key] = ($pubdata->hasReference($key)) ? $pubdata[$key] : null;
            // set additional relation fields
            $this->relations[$key] = array_merge($rel, array('alias' => $this->__($key)));
        }

        $view->assign('relations', $this->relations);

        // check for set_* default values
        foreach (array_keys($this->pubfields->toArray()) as $fieldname) {
            $val = FormUtil::getPassedValue('set_'.$fieldname, '');
            if (!empty($val)) {
                $data[$fieldname] = $val;
            }
        }

        // add the pub information to the render
        $view->assign('pubdata', $data);

        // stores the first referer and the item URL
        if (empty($this->referer)) {
            $viewurl = ModUtil::url('Clip', 'user', 'view', array('tid' => $this->tid), null, null, true);
            $this->referer = System::serverGetVar('HTTP_REFERER', $viewurl);
        }

        if (!empty($this->id)) {
            $this->itemurl = ModUtil::url('Clip', 'user', 'display', array('tid' => $this->tid, 'pid' => $this->pub['core_pid']), null, null, true);
        }

        $view->assign('actions', $actions);

        return true;
    }

    function handleCommand($view, $args)
    {
        if ($args['commandName'] == 'cancel') {
            return $view->redirect($this->referer);
        }

        if (!$view->isValid()) {
            return false;
        }

        $data = $view->getValues();

        // restore the core values
        $this->pubExtract($data['pubdata']);

        // perform the command
        $data = ModUtil::apiFunc('Clip', 'user', 'edit',
                                 array('data'        => $this->pub,
                                       'commandName' => $args['commandName'],
                                       'pubfields'   => $this->pubfields,
                                       'schema'      => str_replace('.xml', '', $this->pubtype['workflow'])));

        // see http://www.smarty.net/manual/en/caching.groups.php
        $tmp = Zikula_View::getInstance('Clip');
        // clear the view of the current publication
        $tmp->clear_cache(null, 'display'.$this->tid.'|'.$this->pub['core_pid']);
        // clear all page of publist
        $tmp->clear_cache(null, 'view'.$this->tid);
        unset($tmp);

        // core operations processing
        $goto = $this->itemurl;
        $ops  = isset($data['core_operations']) ? $data['core_operations'] : array();

        if ($data['core_indepot'] == 1 || (isset($ops['deletePub']) && $ops['deletePub'])) {
            // if the item moved to the depot or was deleted
            $urltid = ModUtil::url('Clip', 'user', 'view', array('tid' => $data['core_tid']));
            // check if the user comes of the display screen or not
            $goto = (strpos($this->referer, $this->itemurl) === 0) ? $this->referer : $urltid;

        } elseif (isset($ops['createPub']) && $ops['createPub']) {
            // the publication was created
            if ($data['core_online'] == 1) {
                $goto = ModUtil::url('Clip', 'user', 'display',
                                     array('tid' => $data['core_tid'],
                                           'pid' => $data['core_pid']));
            } else {
                // back to the pubtype pending template or referer page if it is not approved yet
                $goto = isset($ops['createPub']['goto']) ? $ops['createPub']['goto'] : $this->referer;
            }

        } elseif (!empty($ops)) {
            // check if an operation thrown a goto value
            foreach (array_keys($ops) as $op) {
                if (isset($ops[$op]['goto'])) {
                    $goto = $ops[$op]['goto'];
                }
            }
        }

        // check the goto parameter
        switch ($this->goto) {
            case 'stepmode':
                // stepmode can be used to go automatically from one workflowstep to the next
                $this->goto = ModUtil::url('Clip', 'user', 'edit',
                                       array('tid'  => $data['core_tid'],
                                             'id'   => $data['id'],
                                             'goto' => 'stepmode'));
                break;

            case 'referer':
                $this->goto = $this->referer;
                break;

            case 'editlist':
                $this->goto = ModUtil::url('Clip', 'admin', 'editlist',
                                       array('_id' => $data['core_tid'].'_'.$data['core_pid']));
                break;

            case 'admin':
                $this->goto = ModUtil::url('Clip', 'admin', 'publist', array('tid' => $data['core_tid']));
                break;

            case 'index':
                $this->goto = ModUtil::url('Clip', 'user', 'view', array('tid' => $data['core_tid']));
                break;

            case 'home':
                $this->goto = System::getHomepageUrl();
                break;

            default:
                //if (empty($this->goto)) {
                    $this->goto = $goto;
                //}
        }

        if (!$data) {
            return false;
        }

        return $view->redirect($this->goto);
    }

    /**
     * Setters and getters
     */
    public function ClipSetUp($id, $tid, $pubtype=null, $pubfields=null)
    {
        $this->id = $id;

        $this->tid = $tid;
        // pubtype
        if ($pubtype) {
            $this->pubtype = $pubtype;
        } else {
            $this->pubtype = Clip_Util::getPubType($tid);
        }
        // pubfields
        if ($pubfields) {
            $this->pubfields = $pubfields;
        } else {
            $this->pubfields = Clip_Util::getPubFields($tid);
        }
    }

    public function getPubfieldData($name, $field=null)
    {
        if (empty($name) || !isset($this->pubfields[$name])) {
            return false;
        }

        if ($field && isset($this->pubfields[$name][$field])) {
            return $this->pubfields[$name][$field];
        }

        return $this->pubfields[$name];
    }

    public function getTid()
    {
        return $this->tid;
    }

    /**
     * Publication data handlers
     */
    private function pubDefault(&$pubdata)
    {
        $pubdata['core_author']   = UserUtil::getVar('uid');
        $pubdata['core_language'] = '';

        $pubdata->pubPostProcess(array('handleplugins' => false, 'loadworkflow' => true));
        $this->pub = $pubdata;
    }

    private function pubAssign(&$pubdata)
    {
        $pubdata->pubPostProcess(array('handleplugins' => false, 'loadworkflow' => true));
        $this->pub = $pubdata;
    }

    private function pubExtract($data)
    {
        if (!empty($this->id)) {
            $this->pub->assignIdentifier($this->id);
        }
        // allow specify fixed PIDs
        if (isset($data['core_pid'])) {
            $this->pub['core_pid'] = $data['core_pid'];
        }
        // fill the relations data if present
        foreach (array_keys($this->relations) as $alias) {
            if (isset($data[$alias])) {
                $this->pub->link($alias, (array)$data[$alias]);
                unset($data[$alias]);
            }
        }

        // fill any other data
        $this->pub->fromArray($data);
    }
}
