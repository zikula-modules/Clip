<?php
/**
 * PageMaster
 *
 * @copyright   (c) PageMaster Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @version     $ Id $
 * @package     Zikula_3rdParty_Modules
 * @subpackage  pagemaster
 */

require_once('system/pnForm/plugins/function.pnformuploadinput.php');

class pmformuploadinput extends pnFormUploadInput
{
    var $columnDef = 'C(512)';
    var $title;
    var $upl_arr;

    function __construct()
    {
        $dom = ZLanguage::getModuleDomain('PageMaster');
        //! field type name
        $this->title = __('File Upload', $dom);

        parent::__construct();
    }

    function getFilename()
    {
        return __FILE__; // FIXME: may be found in smarty's data???
    }

    function render(&$render)
    {
        $input_html = parent::render($render);

        return $input_html.' '.$this->upl_arr['orig_name'];
    }

    function load($render, &$params)
    {
        $this->loadValue($render, $render->get_template_vars());
    }

    function loadValue(&$render, &$values)
    {
        if (isset($values[$this->dataField]) && !empty($values[$this->dataField])) {
            $this->upl_arr = unserialize($values[$this->dataField]);
        }
    }

    function postRead($data, $field)
    {
        $dom = ZLanguage::getModuleDomain('PageMaster');

        // if there's some data, process it
        if (!empty($data)) {
            $arrTypeData = @unserialize($data);

            if (!is_array($arrTypeData)) {
                return LogUtil::registerError('pmformuploadinput: '.__('Stored data is invalid', $dom));
            }

            $path = pnModGetVar('PageMaster', 'uploadpath');
            $url  = pnGetBaseURL().$path;
            if (!empty($arrTypeData['file_name'])) {
                $this->upl_arr =  array(
                         'orig_name' => $arrTypeData['orig_name'],
                         'file_name' => $url.'/'.$arrTypeData['file_name'],
                         'file_size' => isset($arrTypeData['file_size']) && $arrTypeData['file_size'] ? $arrTypeData['file_size'] : filesize("$path/$arrTypeData[file_name]")
                );
            } else {
                $this->upl_arr = array(
                         'orig_name' => '',
                         'file_name' => '',
                         'file_size' => 0
                );
            }
        }

        return $this->upl_arr;
    }

    function preSave($data, $field)
    {
        $id   = $data['id'];
        $tid  = $data['tid'];
        $data = $data[$field['name']];

        if ($id != NULL) {
            // if it's not a new pub get the old upload
            $old_upload = DBUtil::selectFieldByID('pagemaster_pubdata'.$tid, $field['name'], $id, 'id');
        }

        if (!empty($data['name'])) {
            $uploadpath = pnModGetVar('PageMaster', 'uploadpath');

            // delete the old file
            if ($id != NULL) {
                $old_upload_arr = unserialize($old_upload);
                unlink($uploadpath.'/'.$old_upload_arr['file_name']);
            }

            $srcTempFilename = $data['tmp_name'];
            $ext             = strtolower(PMgetExtension($data['name']));
            $randName        = PMgetNewFileReference();
            $new_filename    = "{$randName}.{$ext}";
            $dstFilename     = "{$uploadpath}/{$new_filename}";

            copy($srcTempFilename, $dstFilename);

            $arrTypeData = array (
                'orig_name' => $data['name'],
                'file_name' => $new_filename,
                'file_size' => filesize($dstFilename)
            );

            return serialize($arrTypeData);

        } elseif ($id != NULL) {
            // if it's not a new pub
            // return the old upload if no new is selected
            return $old_upload;
        }

        return NULL;
    }
}
