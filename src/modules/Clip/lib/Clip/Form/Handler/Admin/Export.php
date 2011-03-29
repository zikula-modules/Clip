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
 * Form handler to manage data export.
 */
class Clip_Form_Handler_Admin_Export extends Zikula_Form_AbstractHandler
{
    private $returnurl;

    /**
     * Initialize function
     */
    function initialize($view)
    {
        // stores the return URL
        if (empty($this->returnurl)) {
            $adminurl = ModUtil::url('Clip', 'admin');
            $this->returnurl = System::serverGetVar('HTTP_REFERER', $adminurl);
            // default output
            $view->assign('outputto', 1);
        }

        $outputs = array(
            array(
                'text'  => $this->__('File'),
                'value' => 0
            ),
            array(
                'text'  => $this->__('Browser'),
                'value' => 1
            )
        );

        $view->assign('pubtypes', Clip_Util::getPubtypesSelector())
             ->assign('formats', Clip_Util::getFormatsSelector(false))
             ->assign('outputs', $outputs);

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

        if (!$view->isValid()) {
            return false;
        }

        $data = $view->getValues();

        // handle the commands
        switch ($args['commandName'])
        {
            // export
            case 'export':
                // validate filename if it's output to file
                if ($data['outputto'] == 0 && !$data['filename']) {
                    $plugin = $view->getPluginById('filename');
                    $plugin->setError($this->__('There must be a filename for the output.'));
                    return false;
                }

                // build the export instance
                $batch = new Clip_Export_Batch($data);

                // select and add the sections to export

                // * pubtype
                $tbl = Doctrine_Core::getTable('Clip_Model_Pubtype');
                $query = $tbl->createQuery();
                $query->where('tid = ?', $data['tid']);
                $params = array(
                    'idfield' => 'pid',
                    'name'    => 'pubtypes',
                    'rowname' => 'pubtype',
                    'query'   => $query
                );
                $section = new Clip_Export_Section($params);
                $batch->addSection($section);

                // * pubfields
                $tbl = Doctrine_Core::getTable('Clip_Model_Pubfield');
                $query = $tbl->createQuery();
                $query->where('tid = ?', $data['tid']);
                $params = array(
                    'idfield' => 'id',
                    'name'    => 'pubfields',
                    'rowname' => 'pubfield',
                    'query'   => $query
                );
                $section = new Clip_Export_Section($params);
                $batch->addSection($section);

                // * pubdata
                $tbl = Doctrine_Core::getTable('Clip_Model_Pubdata'.$data['tid']);
                $query = $tbl->createQuery();
                $params = array(
                    'idfield'  => 'id',
                    'name'     => 'pubdata'.$data['tid'],
                    'rowname'  => 'pub',
                    'pagesize' => 30,
                    'query'    => $query
                );
                $section = new Clip_Export_Section($params);
                $batch->addSection($section);

                // * workflows
                DBUtil::loadDBUtilDoctrineModel('workflows', 'Clip_Model_Workflow');
                $tbl = Doctrine_Core::getTable('Clip_Model_Workflow');
                $query = $tbl->createQuery();
                $query->where('module = ?', 'Clip')
                      ->where('obj_table = ?', 'clip_pubdata'.$data['tid']);
                $params = array(
                    'idfield'  => 'id',
                    'name'     => 'workflows'.$data['tid'],
                    'rowname'  => 'workflow',
                    'addfrom'  => array('pubdata'.$data['tid'] => 'obj_id'),
                    'pagesize' => 30,
                    'query'    => $query
                );
                $section = new Clip_Export_Section($params);
                $batch->addSection($section);

                // execute the export
                $batch->execute();

                // get the output
                $batch->output();
                break;
        }

        return $view->redirect($this->returnurl);
    }
}