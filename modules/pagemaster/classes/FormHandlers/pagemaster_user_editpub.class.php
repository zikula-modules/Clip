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
 * pnForm handler for updating pubdata tables.
 *
 * @author kundi
 */
class pagemaster_user_editpub
{
    var $tid;
    var $core_author;
    var $core_pid;
    var $core_revision;
    var $id;
    var $pubfields;
    var $pubtype;
    var $tablename;
    var $goto;

    function initialize(&$render)
    {
        $dom = ZLanguage::getModuleDomain('pagemaster');

        $this->goto = FormUtil::getPassedValue('goto', '');

        if (!empty($this->id)) {
            $pubdata = DBUtil::selectObjectByID($this->tablename, $this->id, 'id');

            $this->core_author   = $pubdata['core_author'];
            $this->core_pid      = $pubdata['core_pid'];
            $this->core_revision = $pubdata['core_revision'];

            $actions = PmWorkflowUtil::getActionsForObject($pubdata, $this->tablename, 'id', 'pagemaster');

        } else {
            $pubdata = array();
            $this->core_author = pnUserGetVar('uid');
            $actions = PmWorkflowUtil::getActionsByState(str_replace('.xml', '', $this->pubtype['workflow']), 'pagemaster');
        }

        $tid = ($this->pubtype['tid'] > 0) ? $this->pubtype['tid'] : FormUtil::getPassedValue('tid');

        // if there are no actions the user is not allowed to change / submit / delete something.
        // We will redirect the user to the overview page
        if (count($actions) < 1) {
            LogUtil::registerError(__('No workflow actions found. This can be a permissions issue.', $dom));

            return $render->pnFormRedirect(pnModURL('pagemaster', 'user', 'main', array('tid' => $tid)));
        }

        // check for set_* default values
        $fieldnames = array_keys($this->pubfields);

        foreach ($fieldnames as $fieldname)
        {
            $val = FormUtil::getPassedValue('set_'.$fieldname, '');
            if (!empty($val)) {
                $pubdata[$fieldname] = $val;
            }
        }

        if (count($pubdata) > 0) {
            $render->assign($pubdata);
        }

        $render->assign('actions', $actions);
        return true;
    }

    function handleCommand(&$render, &$args)
    {
        if (!$render->pnFormIsValid()) {
            return false;
        }

        $data = $render->pnFormGetValues();

        $data['tid']           = $this->tid;
        $data['id']            = $this->id;
        $data['core_author']   = $this->core_author;
        $data['core_pid']      = $this->core_pid;
        $data['core_revision'] = $this->core_revision;

        $data = pnModAPIFunc('pagemaster', 'user', 'editPub',
                             array('data'        => $data,
                                   'commandName' => $args['commandName'],
                                   'pubfields'   => $this->pubfields,
                                   'schema'      => str_replace('.xml', '', $this->pubtype['workflow'])));

        // see http://www.smarty.net/manual/en/caching.groups.php
        $pnr = pnRender::getInstance('pagemaster');
        // clear the view of the current publication
        $pnr->clear_cache(null, 'viewpub'.$this->tid.'|'.$this->core_pid);
        // clear all page of publist
        $pnr->clear_cache(null, 'publist'.$this->tid);
        unset($pnr);

        // somebody change this always back, pls let it be like this, otherwise stepmode does not work!
        // if the item moved to the depot
        if ($data[$args['commandName']]['core_indepot'] == 1) {
            $this->goto = pnModURL('pagemaster', 'user', 'main',
                                   array('tid' => $data['tid']));

        } elseif ($this->goto == 'stepmode') {
            // stepmode can be used to go automaticaly from one workflowstep to the next
            $this->goto = pnModURL('pagemaster', 'user', 'pubedit',
                                   array('tid'  => $data['tid'],
                                         'id'   => $data['id'],
                                         'goto' => 'stepmode'));

         } elseif (empty($this->goto)) {
            $this->goto = pnModURL('pagemaster', 'user', 'viewpub',
                                   array('tid' => $data['tid'],
                                         'pid' => $data['core_pid']));
        }

        if (empty($data)) {
            return false;
        }

        return $render->pnFormRedirect($this->goto);
    }
}
