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
        $this->parseConfig($params['fieldconfig']);
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

        if ($this->upl_arr && $this->upl_arr['orig_name']) {
            $url = System::getBaseUrl().ModUtil::getVar('Clip', 'uploadpath').'/'.$this->upl_arr['file_name'];
            $input_html .= ' <em class="z-formnote z-sub"><a href="'.$url.'">'.$this->upl_arr['orig_name'].'</a></em>';
        }

        return $input_html;
    }

    public function renderBegin(Zikula_Form_View $view)
    {
        $view->assign('fieldid', $this->id);

        return $this->render($view);
    }

    public function renderContent(Zikula_Form_View $view, $content)
    {
        return $content;
    }

    public function renderEnd(Zikula_Form_View $view)
    {
        return '';
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
                 'url'       => '',
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
                               'url'       => $url.'/'.$arrTypeData['file_name'],
                               'file_name' => $arrTypeData['file_name'],
                               'file_size' => isset($arrTypeData['file_size']) && $arrTypeData['file_size'] ? $arrTypeData['file_size'] : filesize("$path/$arrTypeData[file_name]"),
                               'extension' => FileUtil::getExtension($arrTypeData['file_name'])
                           );
            }
        }

        $pub[$fieldname] = $upl_arr;
    }

    public function preSave($pub, $field)
    {
        $newData = $pub[$field['name']];

        if ($pub['id']) {
            // if it's not a new pub get the old upload
            $oldData = (string)Doctrine_Core::getTable('ClipModels_Pubdata'.$pub['core_tid'])
                               ->selectFieldBy($field['name'], $pub['id'], 'id');

            // evaluate if preSave is triggered by the Pub Record without changes
            if ($newData == $oldData) {
                // $newData is serialized too
                return $oldData;
            }

            // unserialize the old data
            $oldData = $data = ($oldData ? unserialize($oldData) : array());
        } else {
            $oldData = null;
            $data = array();
        }

        // check if there's a new upload
        $newUpload = !empty($newData['name']) && $newData['error'] == 0;
        $oldUpload = $oldData && $oldData['file_name'];

        if ($newUpload || $oldUpload) {
            $extension  = strtolower(FileUtil::getExtension($newData['name'] ? $newData['name'] : $oldData['file_name']));
            // FIXME validate the supported file format uploaded
        }

        $uploadpath = ModUtil::getVar('Clip', 'uploadpath');
        $this->parseConfig($field['typedata']);

        // delete the files if requested to or if there's a new upload
        if ($oldUpload && ($newUpload || $newData['delete'])) {
            if ($oldData['file_name'] && file_exists($uploadpath.'/'.$oldData['file_name'])) {
                unlink($uploadpath.'/'.$oldData['file_name']);
            }
            $data['orig_name'] = '';
            $data['file_name'] = '';
            $data['file_size'] = 0;

        } elseif ($oldUpload) {
            // rename the file_name if the preserve name is enabled now
            if ($this->config['preserve'] && file_exists($uploadpath.'/'.$oldData['file_name']) && $oldData['file_name'] != $oldData['orig_name']) {
                rename($uploadpath.'/'.$oldData['file_name'], $uploadpath.'/'.$oldData['orig_name']);
                $data['file_name'] = $oldData['orig_name'];
            }
        }

        // process the upload if there's one
        if ($newUpload) {
            $data['orig_name'] = $newData['name'];
            $data['file_size'] = $newData['size'];
            $filename  = $this->config['preserve'] ? DataUtil::formatPermalink(FileUtil::getFilebase($newData['name'])) : Clip_Util::getNewFileReference();
            $data['file_name'] = "$filename.$extension";
            move_uploaded_file($newData['tmp_name'], "{$uploadpath}/{$data['file_name']}");
        }

        if ($data) {
            return serialize($data);
        }

        return $oldData ? $oldData : '';
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

    public static function getOutputEdit($field)
    {
        $gtdelete = no__('Delete the file');

        $full = "\n".
                '                <div class="z-formrow">'."\n".
                '                    {clip_form_label for=\''.$field['name'].'\' text=$pubfields.'.$field['name'].'.title|clip_translate'.((bool)$field['ismandatory'] ? ' mandatorysym=true' : '').'}'."\n".
                '                    {clip_form_block field=\''.$field['name'].'\'}'."\n".
                '                    {if $pubfields.'.$field['name'].'.description|clip_translate}'."\n".
                '                        <span class="z-formnote z-sub">{$pubfields.'.$field['name'].'.description|clip_translate}</span>'."\n".
                '                    {/if}'."\n".
                '                    {if $pubdata.id and $pubdata.'.$field['name'].'.file_name}'."\n".
                '                        <span class="z-formlist clip-edit-suboptions">'."\n".
                '                            {formcheckbox id="`$fieldid`_delete"} {formlabel for="`$fieldid`_delete" __text=\''.$gtdelete.'\'}'."\n".
                '                        </span>'."\n".
                '                    {/if}'."\n".
                '                    {/clip_form_block}'."\n".
                '                </div>'."\n";

        return array('full' => $full);
    }

    /**
     * Clip admin methods.
     */
    public static function getConfigSaveJSFunc($field)
    {
        return 'function()
                {
                    $(\'typedata\').value = Number($F(\'clipplugin_preservename\'));

                    Zikula.Clip.Pubfields.ConfigClose();
                }';
    }

    public function getConfigHtml($field, $view)
    {
        $this->parseConfig($view->_tpl_vars['field']['typedata']);

        $html = '<div class="z-formrow">
                     <label for="clipplugin_preservename">'.$this->__('Preserve filename').':</label>
                     <input type="checkbox" value="1" id="clipplugin_preservename" name="clipplugin_preservename" '.($this->config['preserve'] ? ' checked="checked"' : '').' />
                     <span class="z-formnote z-sub">'.$this->__('Preserve the file name of the original file uploaded instead generate a random one.').'</span>
                 </div>';

        return $html;
    }

    /**
     * Parse configuration
     */
    public function parseConfig($typedata='', $args=array())
    {
        // config string: "$preserve"

        // validate all the values
        $this->config = array(
            'preserve' => (bool)$typedata
        );
    }
}
