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

    function setup()
    {
        $this->setDomain(ZLanguage::getModuleDomain('Clip'));
        
        //! field type name
        $this->pluginTitle = $this->__('Image Upload');
    }

    function getFilename()
    {
        return __FILE__;
    }

    /**
     * Form Framework methods.
     */
    function readParameters($view, &$params)
    {
        $this->parseConfig($view->eventHandler->getPubfieldData($params['id'], 'typedata'));

        parent::readParameters($view, $params);
    }

    function load($view, &$params)
    {
        $this->loadValue($view, $view->get_template_vars());
    }

    function loadValue($view, $values)
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

    function render($view)
    {
        $input_html = parent::render($view);
        $note_html  = $this->upl_arr ? ' <em class="z-formnote z-sub">'.$this->upl_arr['orig_name'].'</em>' : '';

        return $input_html.$note_html;
    }

    /**
     * Clip processing methods.
     */
    function postRead($data, $field)
    {
        // this plugin return an array by default
        $upl_arr = array(
                       'orig_name'    => '',
                       'preUrl'       => '',
                       'fullUrl'      => '',
                       'thumbnailUrl' => '',
                       'url'          => '',
                       'extension'    => ''
                   );

        // if the data is not empty, process it
        if (!empty($data)) {
            $arrTypeData = @unserialize($data);

            if (!is_array($arrTypeData)) {
                return LogUtil::registerError('Plugin_Image: '.$this->__('Stored data is invalid.'));
            }

            $url = System::getBaseUrl().ModUtil::getVar('Clip', 'uploadpath');
            if (!empty($arrTypeData['orig_name'])) {
                $upl_arr =  array(
                                'orig_name'    => $arrTypeData['orig_name'],
                                'preUrl'       => !empty($arrTypeData['pre_name']) ? $url.'/'.$arrTypeData['pre_name'] : '',
                                'fullUrl'      => !empty($arrTypeData['full_name']) ? $url.'/'.$arrTypeData['full_name'] : '',
                                'thumbnailUrl' => !empty($arrTypeData['tmb_name']) ? $url.'/'.$arrTypeData['tmb_name'] : '',
                                'url'          => $url.'/'.$arrTypeData['file_name'],
                                'extension'    => FileUtil::getExtension($arrTypeData['orig_name'])
                            );
            }
        }

        return $upl_arr;
    }

    function preSave($data, $field)
    {
        $postData = $data[$field['name']];
        // FIXME validate a supported image format uploaded

        // ugly to get old image from DB
        if ($data['id'] != NULL) {
            $old_image = (string)Doctrine_Core::getTable('Clip_Model_Pubdata'.$data['core_tid'])
                                 ->selectFieldBy($field['name'], $data['id'], 'id');
        }

        if ($postData != $old_image && !empty($postData['name'])) {
            $uploadpath = ModUtil::getVar('Clip', 'uploadpath');

            // delete the old file
            if ($data['id'] != NULL) {
                $old_image_arr = unserialize($old_image);
                unlink($uploadpath.'/'.$old_image_arr['tmb_name']);
                unlink($uploadpath.'/'.$old_image_arr['pre_name']);
                unlink($uploadpath.'/'.$old_image_arr['full_name']);
                unlink($uploadpath.'/'.$old_image_arr['file_name']);
            }

            $srcFilename     = $postData['tmp_name'];
            $ext             = strtolower(FileUtil::getExtension($postData['name']));
            $randName        = Clip_Util::getNewFileReference();
            $newFileNameOrig = $randName.'.'.$ext;
            $newDestOrig     = "{$uploadpath}/{$newFileNameOrig}";
            copy($srcFilename, $newDestOrig);

            $tmpargs  = array();
            $preargs  = array();
            $fullargs = array();
            if (!empty($field['typedata']) && strpos($field['typedata'], ':')) {
                $this->parseConfig($field['typedata']);
                list($tmpx, $tmpy ,$prex, $prey, $fullx, $fully) = $this->config;
                if ($tmpx > 0) {
                    $tmpargs['w'] = $tmpx;
                }
                if ($tmpy > 0) {
                    $tmpargs['h'] = $tmpy;
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
            }

            $srcFilename =   $postData['tmp_name'];
            $ext             = strtolower(FileUtil::getExtension($postData['name']));
            $randName        = Clip_Util::getNewFileReference();
            $newFileNameOrig = $randName.'.'.$ext;
            $newDestOrig     = "{$uploadpath}/{$newFileNameOrig}";
            copy($srcFilename, $newDestOrig);

            $tmpargs = array();
            $preargs = array();
            $fullargs = array();
            if (!empty($field['typedata']) && strpos($field['typedata'], ':')) {
                $this->parseConfig($field['typedata']);
                list($tmpx, $tmpy ,$prex, $prey, $fullx, $fully) = $this->config;
                if ($tmpx > 0) {
                    $tmpargs['w'] = $tmpx;
                }
                if ($tmpy > 0) {
                    $tmpargs['h'] = $tmpy;
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
            }

            // Check for the Thumbnails module and if we need it
            if (!empty($tmpargs) && ModUtil::available('Thumbnail')) {
                $newFilenameTmp = "{$randName}-tmb.{$ext}";
                $newDestTmp  = "{$uploadpath}/{$newFilenameTmp}";
                $tmpargs['filename'] = $newDestOrig;
                $tmpargs['dstFilename'] = $newDestTmp;
                $dstName = ModUtil::apiFunc('Thumbnail', 'user', 'generateThumbnail', $tmpargs);
            } elseif (empty($tmpargs)) {
                // no thumbnail needed
                $newFilenameTmp = $newFileNameOrig;
            }

            if (!empty($preargs) && ModUtil::available('Thumbnail')) {
                $newFilenamePre = "{$randName}-pre.{$ext}";
                $newDestPre  = "{$uploadpath}/{$newFilenamePre}";
                $preargs['filename'] = $newDestOrig;
                $preargs['dstFilename'] = $newDestPre;
                $dstName = ModUtil::apiFunc('Thumbnail', 'user', 'generateThumbnail', $preargs);
            } elseif (empty($tmpargs)) {
                // no thumbnail needed
                $newFilenamePre = $newFileNameOrig;
            }

            if (!empty($fullargs) && ModUtil::available('Thumbnail')) {
                $newFilenameFull = "{$randName}-full.{$ext}";
                $newDestFull  = "{$uploadpath}/{$newFilenameFull}";
                $fullargs['filename'] = $newDestOrig;
                $fullargs['dstFilename'] = $newDestFull;
                $dstName = ModUtil::apiFunc('Thumbnail', 'user', 'generateThumbnail', $fullargs);
            } elseif (empty($tmpargs)) {
                // no thumbnail needed
                $newFilenameFull = $newFileNameOrig;
            }

            $arrTypeData = array(
                'orig_name' => $postData['name'],
                'tmb_name'  => $newFilenameTmp,
                'pre_name'  => $newFilenamePre,
                'full_name' => $newFilenameFull,
                'file_name' => $newFileNameOrig
            );

            return serialize($arrTypeData);

        } elseif ($data['id'] != NULL) {
            // if it's not a new pub
            // return the old image if no new is selected
            return $old_image;
        }

        return NULL;
    }

    static function getOutputDisplay($field)
    {
        $full = '    <div class="z-formrow">'."\n".
                '        <span class="z-label">{$pubfields.'.$field['name'].'|clip_translate}:</span>'."\n".
                '        {if $pubdata.'.$field['name'].'.url}'."\n".
                '            <span class="z-formnote">'."\n".
                '                {$pubdata.'.$field['name'].'.orig_name}<br />'."\n".
              //'                <img src="{$pubdata.'.$field['name'].'.thumbnailUrl}" title="{gt text=\''.no__('Thumbnail').'\'}" alt="{gt text=\''.no__('Thumbnail').'\'}" />'."\n".
              //'                <br />'."\n".
                '                <img width="250" src="{$pubdata.'.$field['name'].'.url}" title="{gt text=\''.no__('Image').'\'}" alt="{gt text=\''.no__('Image').'\'}" />'."\n".
                '                <pre>{clip_array array=$pubdata.'.$field['name'].'}</pre>'."\n".
                '            </span>'."\n".
                '        {else}'."\n".
                '            <span class="z-formnote">{gt text=\''.no__('No image uploaded.').'\'}</span>'."\n".
                '        {/if}'."\n".
                '    </div>';

        return array('full' => $full);
    }

    /**
     * Clip admin methods.
     */
    static function getSaveTypeDataFunc($field)
    {
        $saveTypeDataFunc = 'function saveTypeData()
                             {
                                 $(\'typedata\').value = $F(\'clipplugin_tmpx_px\')+\':\'+$F(\'clipplugin_tmpy_px\')+\':\'+$F(\'clipplugin_previewx_px\')+\':\'+$F(\'clipplugin_previewy_px\')+\':\'+$F(\'clipplugin_fullx_px\')+\':\'+$F(\'clipplugin_fully_px\');

                                 closeTypeData();
                             }';

        return $saveTypeDataFunc;
    }

    function getTypeHtml($field, $view)
    {
        $this->parseConfig($view->_tpl_vars['field']['typedata']);

        if (ModUtil::available('Thumbnail')) {
            // TODO Fieldsets and help text explaining how they work
            $html = '<div class="z-formrow">
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
    function parseConfig($typedata='', $args=array())
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
            5 => isset($this->config[5]) ? (int)$this->config[5] : 0
        );
    }
}
