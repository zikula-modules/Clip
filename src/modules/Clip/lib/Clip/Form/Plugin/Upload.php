<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Form_Plugin
 */

class Clip_Form_Plugin_Upload extends Form_Plugin_UploadInput
{
    public $pluginTitle;
    public $columnDef = 'C(512)';

    public $upl_arr;

    function setup()
    {
        //! field type name
        $this->pluginTitle = $this->__('File Upload');
    }

    function getFilename()
    {
        return __FILE__;
    }

    /**
     * Form Framework methods.
     */
    function load($view, &$params)
    {
        $this->loadValue($view, $view->get_template_vars());
    }

    function loadValue($view, &$values)
    {
        if (isset($values[$this->dataField]) && !empty($values[$this->dataField])) {
            $this->upl_arr = unserialize($values[$this->dataField]);
        }
    }

    function render($view)
    {
        $input_html = parent::render($view);

        return $input_html.' '.$this->upl_arr['orig_name'];
    }

    /**
     * Clip processing methods.
     */
    function postRead($data, $field)
    {
        $this->upl_arr = array();

        // if there's some data, process it
        if (!empty($data)) {
            $arrTypeData = @unserialize($data);

            if (!is_array($arrTypeData)) {
                return LogUtil::registerError('Plugin_Upload: '.$this->__('Stored data is invalid'));
            }

            $path = ModUtil::getVar('Clip', 'uploadpath');
            $url  = System::getBaseUrl().$path;
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

    static function preSave($data, $field)
    {
        $id   = $data['id'];
        $tid  = $data['tid'];
        $data = $data[$field['name']];

        if ($id != NULL) {
            // if it's not a new pub get the old upload
            $old_upload = Doctrine_Core::getTable('Clip_Model_Pubdata'.$tid)
                          ->selectFieldBy($field['name'], $id, 'id');
        }

        if (!empty($data['name'])) {
            $uploadpath = ModUtil::getVar('Clip', 'uploadpath');

            // delete the old file
            if ($id != NULL) {
                $old_upload_arr = unserialize($old_upload);
                unlink($uploadpath.'/'.$old_upload_arr['file_name']);
            }

            $srcTempFilename = $data['tmp_name'];
            $ext             = strtolower(FileUtil::getExtension($data['name']));
            $randName        = Clip_Util::getNewFileReference();
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
