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

class Clip_Form_Plugin_Image extends Zikula_Form_Plugin_UploadInput
{
    public $pluginTitle;
    public $columnDef = 'C(1024)';
    public $upl_arr = array();

    public $config = array();

    public function setup()
    {
        $this->setDomain(ZLanguage::getModuleDomain('Clip'));
        
        //! field type name
        $this->pluginTitle = $this->__('Image Upload');
    }

    public function getFilename()
    {
        return __FILE__;
    }

    /**
     * Form Framework methods.
     */
    public function readParameters($view, &$params)
    {
        $this->parseConfig($view->eventHandler->getPubfieldData($params['id'], 'typedata'));

        parent::readParameters($view, $params);
    }

    public function load($view, &$params)
    {
        $this->loadValue($view, $view->get_template_vars());
    }

    public function loadValue($view, $values)
    {
        if ($this->group == null) {
            if (isset($values[$this->dataField]) && !empty($values[$this->dataField])) {
                $this->upl_arr = unserialize($values[$this->dataField]);
            }
        } else {
            if (isset($values[$this->group][$this->dataField]) && !empty($values[$this->group][$this->dataField])) {
                $this->upl_arr = unserialize($values[$this->group][$this->dataField]);
            }
        }
    }

    public function saveValue(Zikula_Form_View $view, &$pub)
    {
        // check for additional checkboxes (delete image, regen thumbnails)
        $checkboxes = array('delete', 'thumbs');
        foreach ($checkboxes as $checkbox) {
            $cid = $this->dataField.'_'.$checkbox;
            if ($this->group == null) {
                $this->result[$checkbox] = isset($pub[$cid]) ? $pub[$cid] : false;
            } else {
                $this->result[$checkbox] = isset($pub[$this->group][$cid]) ? $pub[$this->group][$cid] : false;
            }
        }

        // store the result in the data array
        if ($this->dataBased) {
            if ($this->group == null) {
                $pub[$this->dataField] = $this->result;
            } else {
                if (!array_key_exists($this->group, $pub)) {
                    $pub[$this->group] = array();
                }
                $pub[$this->group][$this->dataField] = $this->result;
            }
        }
    }

    public function render($view)
    {
        $input_html = parent::render($view);
        $note_html  = $this->upl_arr && $this->upl_arr['orig_name'] ? ' <em class="z-formnote z-sub">'.$this->upl_arr['orig_name'].'</em>' : '';

        return $input_html.$note_html;
    }

    public function renderBegin($view)
    {
        return $this->render($view);
    }

    public function renderContent($view, $content)
    {
        return $content;
    }

    public function renderEnd($view)
    {
        return '';
    }

    /**
     * Clip processing methods.
     */
    protected function hasThumbnails($field)
    {
        $this->parseConfig($field['typedata']);

        $has = false;
        for ($i = 0; $i <= 5; $i++) {
            if ($this->config[$i] > 0) {
                $has = false;
                break;
            }
        }

        return $has;
    }

    public function postRead(&$pub, $field)
    {
        $fieldname = $field['name'];
        $data = $pub[$fieldname];

        // default
        $upl_arr = array(
                       'orig_name'    => '',
                       'full_name'    => '',
                       'preUrl'       => '',
                       'fullUrl'      => '',
                       'thumbnailUrl' => '',
                       'url'          => '',
                       'extension'    => '',
                       'thumbnails'   => $this->hasThumbnails($field)
                   );

        // if the data is not empty, process it
        if (!empty($data)) {
            $arrTypeData = @unserialize($data);

            if (!is_array($arrTypeData)) {
                $pub[$fieldname] = $upl_arr;
                return LogUtil::registerError('Plugin_Image: '.$this->__('Stored data is invalid.'));
            }

            $url = System::getBaseUrl().ModUtil::getVar('Clip', 'uploadpath');
            if (!empty($arrTypeData['orig_name'])) {
                $upl_arr =  array(
                                'orig_name'    => $arrTypeData['orig_name'],
                                'file_name'    => $arrTypeData['file_name'],
                                'preUrl'       => !empty($arrTypeData['pre_name']) ? $url.'/'.$arrTypeData['pre_name'] : '',
                                'fullUrl'      => !empty($arrTypeData['full_name']) ? $url.'/'.$arrTypeData['full_name'] : '',
                                'thumbnailUrl' => !empty($arrTypeData['tmb_name']) ? $url.'/'.$arrTypeData['tmb_name'] : '',
                                'url'          => $url.'/'.$arrTypeData['file_name'],
                                'extension'    => FileUtil::getExtension($arrTypeData['orig_name']),
                                'thumbnails'   => $upl_arr['thumbnails']
                            );
            }
        }

        $pub[$fieldname] = $upl_arr;
    }

    public function preSave($pub, $field)
    {
        $newData = $pub[$field['name']];

        // get old image from DB if the pub exists
        if ($pub['id']) {
            $oldData = (string)Doctrine_Core::getTable('ClipModels_Pubdata'.$pub['core_tid'])
                               ->selectFieldBy($field['name'], $pub['id'], 'id');

            // evaluate if preSave is triggered by the Pub Record without changes
            if ($newData == $oldData) {
                // $newData is serialized too
                return $oldData;
            }

            // unserialize the old data
            $oldData = $data = ($oldData ? unserialize($oldData) : '');
        } else {
            $oldData = null;
            $data = array();
        }

        // check if there's a new upload
        $newUpload = !empty($newData['name']) && $newData['error'] == 0;

        if ($newUpload || $oldData) {
            $uploadpath = ModUtil::getVar('Clip', 'uploadpath');
            $extension  = strtolower(FileUtil::getExtension($newData['name'] ? $newData['name'] : $oldData['file_name']));
            // FIXME validate a supported image format uploaded
        }

        $this->parseConfig($field['typedata']);

        // delete the files if requested to or if there's a new upload
        if ($oldData && ($newUpload || $newData['delete'] || $newData['thumbs'])) {
            $toDelete = array('tmb_name', 'pre_name', 'full_name');
            if ($newData['delete']) {
                $toDelete[] = 'file_name';
                $data['orig_name'] = '';
            } else {
                // rename the file_name if the preserve name is enabled now
                if ($this->config[6] && file_exists($uploadpath.'/'.$oldData['file_name']) && $oldData['file_name'] != $oldData['orig_name']) {
                    rename($uploadpath.'/'.$oldData['file_name'], $uploadpath.'/'.$oldData['orig_name']);
                    $data['file_name'] = $oldData['orig_name'];
                }
            }

            foreach ($toDelete as $k) {
                if ($oldData[$k] && file_exists($uploadpath.'/'.$oldData[$k])) {
                    unlink($uploadpath.'/'.$oldData[$k]);
                }
                $data[$k] = '';
            }
        }

        // process the upload if there's one
        if ($newUpload) {
            $data['orig_name'] = $newData['name'];
            $filename  = $this->config[6] ? DataUtil::formatPermalink(FileUtil::getFilebase($newData['name'])) : Clip_Util::getNewFileReference();
            $data['file_name'] = "$filename.$extension";
            move_uploaded_file($newData['tmp_name'], "{$uploadpath}/{$data['file_name']}");
        }

        // thumbnail regeneration
        if ($newUpload || $oldData && !$newData['delete'] && $newData['thumbs']) {
            $tmbargs  = array();
            $preargs  = array();
            $fullargs = array();

            list($tmbx, $tmby ,$prex, $prey, $fullx, $fully) = $this->config;
            if ($tmbx > 0) {
                $tmbargs['w'] = $tmbx;
            }
            if ($tmby > 0) {
                $tmbargs['h'] = $tmby;
            }
            if ($prex > 0) {
                $preargs['w'] = $prex;
            }
            if ($prey > 0) {
                $preargs['h'] = $prey;
            }
            if ($fullx > 0) {
                $fullargs['w'] = $fullx;
            }
            if ($fully > 0) {
                $fullargs['h'] = $fully;
            }

            // Check for the Thumbnails module and if we need it
            if (!empty($tmbargs) && ModUtil::available('Thumbnail')) {
                $data['tmb_name'] = str_replace(".$extension", "-tmb.$extension", $data['file_name']);
                $tmbargs['filename']    = "{$uploadpath}/{$data['file_name']}";
                $tmbargs['dstFilename'] = "{$uploadpath}/{$data['tmb_name']}";
                $dstName = ModUtil::apiFunc('Thumbnail', 'user', 'generateThumbnail', $tmbargs);

            } elseif ($newUpload) {
                // no thumbnail needed
                $data['tmb_name'] = '';
            }

            if (!empty($preargs) && ModUtil::available('Thumbnail')) {
                $data['pre_name'] = str_replace(".$extension", "-pre.$extension", $data['file_name']);
                $preargs['filename']    = "{$uploadpath}/{$data['file_name']}";
                $preargs['dstFilename'] = "{$uploadpath}/{$data['pre_name']}";
                $dstName = ModUtil::apiFunc('Thumbnail', 'user', 'generateThumbnail', $preargs);

            } elseif ($newUpload) {
                // no thumbnail needed
                $data['pre_name'] = '';
            }

            if (!empty($fullargs) && ModUtil::available('Thumbnail')) {
                $data['full_name'] = str_replace(".$extension", "-full.$extension", $data['file_name']);
                $fullargs['filename']    = "{$uploadpath}/{$data['file_name']}";
                $fullargs['dstFilename'] = "{$uploadpath}/{$data['full_name']}";
                $dstName = ModUtil::apiFunc('Thumbnail', 'user', 'generateThumbnail', $fullargs);

            } elseif ($newUpload) {
                // no thumbnail needed
                $data['full_name'] = '';
            }
        }

        if ($data) {
            return serialize($data);
        }

        return $oldData;
    }

    public static function getOutputDisplay($field)
    {
        $full = '        <div class="z-formrow">'."\n".
                '            <span class="z-label">{$pubfields.'.$field['name'].'|clip_translate}:</span>'."\n".
                '            {if $pubdata.'.$field['name'].'.file_name}'."\n".
                '                <div class="z-formnote">'."\n".
                '                    {$pubdata.'.$field['name'].'.orig_name}<br />'."\n".
              //'                    <img src="{$pubdata.'.$field['name'].'.thumbnailUrl}" title="{gt text=\''.no__('Thumbnail').'\'}" alt="{gt text=\''.no__('Thumbnail').'\'}" />'."\n".
              //'                    <br />'."\n".
                '                    <img width="250" src="{$pubdata.'.$field['name'].'.url}" title="{gt text=\''.no__('Image').'\'}" alt="{gt text=\''.no__('Image').'\'}" />'."\n".
                '                    <pre>{clip_array array=$pubdata.'.$field['name'].'}</pre>'."\n".
                '                </div>'."\n".
                '            {else}'."\n".
                '                <span class="z-formnote">{gt text=\''.no__('No image uploaded.').'\'}</span>'."\n".
                '            {/if}'."\n".
                '        </div>';

        return array('full' => $full);
    }

    public static function getOutputEdit($field)
    {
        $gtdelete = no__('Delete the image');
        $gtregen  = no__('Regenerate thumbnails');

        $full = "\n".
                '                <div class="z-formrow">'."\n".
                '                    {formlabel for=\''.$field['name'].'\' text=$pubfields.'.$field['name'].'.title|clip_translate'.((bool)$field['ismandatory'] ? ' mandatorysym=true' : '').'}'."\n".
                '                    {clip_form_block id=\''.$field['name'].'\' group=\'pubdata\'}'."\n".
                '                    {if $pubfields.'.$field['name'].'.description|clip_translate}'."\n".
                '                        <span class="z-formnote z-sub">{$pubfields.'.$field['name'].'.description|clip_translate}</span>'."\n".
                '                    {/if}'."\n".
                '                    {if isset($pubdata.id) and $pubobj.'.$field['name'].'.file_name}'."\n".
                '                        <span class="z-formlist clip-edit-suboptions">'."\n".
                '                            {formcheckbox id=\''.$field['name'].'_delete\' group=\'pubdata\'} {formlabel for=\''.$field['name'].'_delete\' __text=\''.$gtdelete.'\'}'."\n".
                '                            {if $pubdata.'.$field['name'].'.thumbnails}'."\n".
                '                            <br />'."\n".
                '                            {formcheckbox id=\''.$field['name'].'_thumbs\' group=\'pubdata\'} {formlabel for=\''.$field['name'].'_thumbs\' __text=\''.$gtregen.'\'}'."\n".
                '                            {/if}'."\n".
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
                    $(\'typedata\').value = $F(\'clipplugin_tmpx_px\')+\':\'+$F(\'clipplugin_tmpy_px\')+\':\'+$F(\'clipplugin_previewx_px\')+\':\'+$F(\'clipplugin_previewy_px\')+\':\'+$F(\'clipplugin_fullx_px\')+\':\'+$F(\'clipplugin_fully_px\')+\':\'+$F(\'clipplugin_preservename\');

                    Zikula.Clip.Pubfields.ConfigClose();
                }';
    }

    public function getConfigHtml($field, $view)
    {
        $this->parseConfig($view->_tpl_vars['field']['typedata']);

        if (ModUtil::available('Thumbnail')) {
            // TODO Fieldsets and help text explaining how they work
            $html = '<div class="z-formrow">
                         <label for="clipplugin_preservename">'.$this->__('Preserve filename').':</label>
                         <input type="checkbox" value="1" id="clipplugin_preservename" name="clipplugin_preservename" '.($this->config[6] ? ' checked="checked"' : '').' />
                     </div>
                     <div class="z-formrow">
                         <label for="clipplugin_tmpx_px">'.$this->__('Thumbnail width').':</label>
                         <input type="text" value="'.$this->config[0].'" id="clipplugin_tmpx_px" name="clipplugin_tmpx_px" />
                     </div>
                     <div class="z-formrow">
                         <label for="clipplugin_tmpy_px">'.$this->__('Thumbnail height').':</label>
                         <input type="text" value="'.$this->config[1].'" id="clipplugin_tmpy_px" name="clipplugin_tmpy_px" />
                         <br />
                     </div>
                     <div class="z-formrow">
                         <label for="clipplugin_pre_px">'.$this->__('Preview width').':</label>
                         <input type="text" value="'.$this->config[2].'" id="clipplugin_previewx_px" name="clipplugin_previewx_px" />
                     </div>
                     <div class="z-formrow">
                         <label for="clipplugin_pre_px">'.$this->__('Preview height').':</label>
                         <input type="text" value="'.$this->config[3].'" id="clipplugin_previewy_px" name="clipplugin_previewy_px" />
                         <br />
                     </div>
                     <div class="z-formrow">
                         <label for="clipplugin_full_px">'.$this->__('Full width').':</label>
                         <input type="text" value="'.$this->config[4].'" id="clipplugin_fullx_px" name="clipplugin_fullx_px" />
                     </div>
                     <div class="z-formrow">
                         <label for="clipplugin_full_px">'.$this->__('Full height').':</label>
                         <input type="text" value="'.$this->config[5].'" id="clipplugin_fully_px" name="clipplugin_fully_px" />
                     </div>';
        } else {
            $html = '<div class="z-warningmsg">
                         '.$this->__('Warning! The Thumbnails module is not available. This plugin needs it to build the Preview and Thumbnail of each uploaded Image.').'
                     </div>';
        }

        return $html;
    }

    /**
     * Parse configuration
     */
    public function parseConfig($typedata='', $args=array())
    {
        // config string: "$tmpx:$tmpy:$prex:$prey:$fullx:$fully"
        $this->config = explode(':', $typedata);

        // validate all the values
        $this->config = array(
            0 => (int)$this->config[0],
            1 => isset($this->config[1]) ? (int)$this->config[1] : 0,
            2 => isset($this->config[2]) ? (int)$this->config[2] : 0,
            3 => isset($this->config[3]) ? (int)$this->config[3] : 0,
            4 => isset($this->config[4]) ? (int)$this->config[4] : 0,
            5 => isset($this->config[5]) ? (int)$this->config[5] : 0,
            6 => isset($this->config[6]) ? (bool)$this->config[6] : false
        );
    }
}
