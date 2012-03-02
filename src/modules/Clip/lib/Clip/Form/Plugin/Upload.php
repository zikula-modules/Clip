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

class Clip_Form_Plugin_Upload extends Zikula_Form_Plugin_UploadInput
{
    // plugin definition
    public $pluginTitle;
    public $columnDef = 'C(1024)';
    public $upl_arr = array();
    public $config = array();

    // Clip data handling
    public $alias;
    public $tid;
    public $rid;
    public $pid;
    public $field;

    public function setup()
    {
        $this->setDomain(ZLanguage::getModuleDomain('Clip'));

        //! field type name
        $this->pluginTitle = $this->__('File Upload');
    }

    public function getFilename()
    {
        return __FILE__;
    }

    /**
     * Form Framework methods.
     */
    public function readParameters(Zikula_Form_View $view, &$params)
    {
        unset($params['fieldconfig']);

        parent::readParameters($view, $params);
    }

    public function load(Zikula_Form_View $view, &$params)
    {
        $this->loadValue($view, $view->get_template_vars());
    }

    public function loadValue(Zikula_Form_View $view, &$values)
    {
        if ($this->dataBased) {
            if (isset($values[$this->group][$this->alias][$this->tid][$this->rid][$this->pid][$this->field])) {
                if ($values[$this->group][$this->alias][$this->tid][$this->rid][$this->pid][$this->field]) {
                    $this->upl_arr = unserialize($values[$this->group][$this->alias][$this->tid][$this->rid][$this->pid][$this->field]);
                }
            }
        }
    }

    public function saveValue(Zikula_Form_View $view, &$data)
    {
        // check for additional checkboxes (delete image, regen thumbnails)
        $checkboxes = array('delete', 'thumbs');
        foreach ($checkboxes as $checkbox) {
            $cid = $this->id.'_'.$checkbox;
            $this->result[$checkbox] = isset($data[$cid]) ? $data[$cid] : false;
        }

        // store the result in the data array
        if ($this->dataBased) {
            if (!array_key_exists($this->group, $data)) {
                $data[$this->group] = array($this->alias => array($this->tid => array($this->rid => array($this->pid => array()))));
            }
            $data[$this->group][$this->alias][$this->tid][$this->rid][$this->pid][$this->field] = $this->result;
        }
    }

    public function render(Zikula_Form_View $view)
    {
        $input_html = parent::render($view);
        $note_html  = $this->upl_arr ? ' <em class="z-formnote z-sub">'.$this->upl_arr['orig_name'].'</em>' : '';

        return $input_html.$note_html;
    }

    /**
     * Clip processing methods.
     */
    public function postRead(&$pub, $field)
    {
        $fieldname = $field['name'];
        $data = $pub[$fieldname];

        // default
        $upl_arr = array(
                 'orig_name' => '',
                 'file_name' => '',
                 'file_size' => 0,
                 'extension' => ''
        );

        // if there's some data, process it
        if (!empty($data)) {
            $arrTypeData = @unserialize($data);

            if (!is_array($arrTypeData)) {
                $pub[$fieldname] = $upl_arr;
                return LogUtil::registerError('Plugin_Upload: '.$this->__('Stored data is invalid'));
            }

            $path = ModUtil::getVar('Clip', 'uploadpath');
            $url  = System::getBaseUrl().$path;
            if (!empty($arrTypeData['file_name'])) {
                $upl_arr = array(
                               'orig_name' => $arrTypeData['orig_name'],
                               'file_name' => $arrTypeData['file_name'],
                               'url'       => $url.'/'.$arrTypeData['file_name'],
                               'file_size' => isset($arrTypeData['file_size']) && $arrTypeData['file_size'] ? $arrTypeData['file_size'] : filesize("$path/$arrTypeData[file_name]"),
                               'extension' => FileUtil::getExtension($arrTypeData['file_name'])
                           );
            }
        }

        $pub[$fieldname] = $upl_arr;
    }

    public static function preSave($data, $field)
    {
        $postData = $data[$field['name']];

        if ($data['id'] != NULL) {
            // if it's not a new pub get the old upload
            $old_upload = (string)Doctrine_Core::getTable('ClipModels_Pubdata'.$data['core_tid'])
                                  ->selectFieldBy($field['name'], $data['id'], 'id');
        }

        if ($postData != $old_upload && !empty($postData['name'])) {
            $uploadpath = ModUtil::getVar('Clip', 'uploadpath');

            // delete the old file
            if ($data['id'] != NULL) {
                $old_upload_arr = unserialize($old_upload);
                unlink($uploadpath.'/'.$old_upload_arr['file_name']);
            }

            $srcTempFilename = $postData['tmp_name'];
            $ext             = strtolower(FileUtil::getExtension($postData['name']));
            $randName        = Clip_Util::getNewFileReference();
            $new_filename    = "{$randName}.{$ext}";
            $dstFilename     = "{$uploadpath}/{$new_filename}";

            copy($srcTempFilename, $dstFilename);

            $arrTypeData = array (
                'orig_name' => $postData['name'],
                'file_name' => $new_filename,
                'file_size' => filesize($dstFilename)
            );

            return serialize($arrTypeData);

        } elseif ($data['id'] != NULL) {
            // if it's not a new pub
            // return the old upload if no new is selected
            return $old_upload;
        }

        return NULL;
    }

    public static function getOutputDisplay($field)
    {
        $full = '        <div class="z-formrow">'."\n".
                '            <span class="z-label">{$pubfields.'.$field['name'].'|clip_translate}:</span>'."\n".
                '            {if $pubdata.'.$field['name'].'.file_name}'."\n".
                '                <div class="z-formnote">'."\n".
                '                    {$pubdata.'.$field['name'].'.orig_name}<br />'."\n".
                '                    <a href="{clip_downloadurl field=\''.$field['name'].'\'}">{gt text=\''.no__('Download').'\'}</a>'."\n".
                '                </div>'."\n".
                '            {else}'."\n".
                '                <span class="z-formnote">{gt text=\''.no__('No file uploaded.').'\'}</span>'."\n".
                '            {/if}'."\n".
                '            <pre class="z-formnote">{clip_dump var=$pubdata.'.$field['name'].'}</pre>'."\n".
                '        </div>';

        return array('full' => $full);
    }
}
