<?php
/**
 * Clip
 *
 * @copyright   (c) Clip Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @version     $ Id $
 * @package     Zikula_3rdParty_Modules
 * @subpackage  pagemaster
 */

class Clip_Form_Plugin_Image extends Form_Plugin_UploadInput
{
    public $pluginTitle;
    public $columnDef = 'C(512)';
    public $upl_arr;

    public $config;

    function setup()
    {
        $dom = ZLanguage::getModuleDomain('Clip');
        $this->setDomain($dom);

        //! field type name
        $this->pluginTitle = $this->__('Image Upload');
    }

    function getFilename()
    {
        return __FILE__;
    }

    static function getPluginOutput($field)
    {
        $full = '    {if $pubdata.'.$field['name'].'.url neq \'\'}'."\n".
                '        <div class="z-formrow">'."\n".
                '            <span class="z-label">{gt text=\''.$field['title'].'\'}:</span>'."\n".
                '            <span class="z-formnote">'."\n".
                '                {$pubdata.'.$field['name'].'.orig_name}<br />'."\n".
                '                <img src="{$pubdata.'.$field['name'].'.thumbnailUrl}" title="{gt text=\''.no__('Thumbnail').'\'}" alt="{gt text=\''.no__('Thumbnail').'\'}" />'."\n".
              //'                <br />'."\n".
              //'                <img src="{$pubdata.'.$field['name'].'.url}" title="{gt text=\''.no__('Image').'\'}" alt="{gt text=\''.no__('Image').'\'}" />'."\n".
                '                <pre>{pmarray array=$pubdata.'.$field['name'].'}</pre>'."\n".
                '            <span>'."\n".
                '        </div>'."\n".
                '    {/if}';

        return array('full' => $full);
    }

    function render($view)
    {
        $input_html = parent::render($view);

        return $input_html.' '.$this->upl_arr['orig_name'];
    }

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

    static function postRead($data, $field)
    {
        // this plugin return an array by default
        $upl_arr = array();

        // if the data is not empty, process it
        if (!empty($data)) {
            $arrTypeData = @unserialize($data);

            if (!is_array($arrTypeData)) {
                return LogUtil::registerError('pmformimageinput: '.$this->__('Stored data is invalid.'));
            }

            $url = System::getBaseUrl().ModUtil::getVar('Clip', 'uploadpath');
            if (!empty($arrTypeData['orig_name'])) {
                $upl_arr =  array(
                                'orig_name'    => $arrTypeData['orig_name'],
                                'preUrl'       => !empty($arrTypeData['pre_name']) ? $url.'/'.$arrTypeData['pre_name'] : '',
                                'fullUrl'      => !empty($arrTypeData['full_name']) ? $url.'/'.$arrTypeData['full_name'] : '',
                                'thumbnailUrl' => !empty($arrTypeData['tmb_name']) ? $url.'/'.$arrTypeData['tmb_name'] : '',
                                'url'          => $url.'/'.$arrTypeData['file_name']
                );
            } else {
                $upl_arr = array(
                               'orig_name'    => '',
                               'preUrl'       => '',
                               'fullUrl'      => '',
                               'thumbnailUrl' => '',
                               'url'          => ''
                           );
            }
        }

        return $upl_arr;
    }

    function preSave($data, $field)
    {
        $id   = $data['id'];
        $tid  = $data['tid'];
        $PostData = $data[$field['name']];

        // ugly to get old image from DB
        if ($id != NULL) {
            $old_image = Doctrine_Core::getTable('Clip_Model_Pubdata'.$tid)
                         ->selectFieldBy($field['name'], $id, 'id');
        }

        if (!empty($PostData['name'])) {
            $uploadpath = ModUtil::getVar('Clip', 'uploadpath');

            // delete the old file
            if ($id != NULL) {
                $old_image_arr = unserialize($old_image);
                unlink($uploadpath.'/'.$old_image_arr['tmb_name']);
                unlink($uploadpath.'/'.$old_image_arr['pre_name']);
                unlink($uploadpath.'/'.$old_image_arr['full_name']);
                unlink($uploadpath.'/'.$old_image_arr['file_name']);
            }

            $srcFilename     = $PostData['tmp_name'];
            $ext             = strtolower(FileUtil::getExtension($PostData['name']));
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

            $srcFilename =   $PostData['tmp_name'];
            $ext             = strtolower(FileUtil::getExtension($PostData['name']));
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
                'orig_name' => $PostData['name'],
                'tmb_name'  => $newFilenameTmp,
                'pre_name'  => $newFilenamePre,
                'full_name' => $newFilenameFull,
                'file_name' => $newFileNameOrig
            );

            return serialize($arrTypeData);

        } elseif ($id != NULL) {
            // if it's not a new pub
            // return the old image if no new is selected
            return $old_image;
        }

        return NULL;
    }

    static function getSaveTypeDataFunc($field)
    {
        $saveTypeDataFunc = 'function saveTypeData()
                             {
                                 $(\'typedata\').value = $F(\'pmplugin_tmpx_px\')+\':\'+$F(\'pmplugin_tmpy_px\')+\':\'+$F(\'pmplugin_previewx_px\')+\':\'+$F(\'pmplugin_previewy_px\')+\':\'+$F(\'pmplugin_fullx_px\')+\':\'+$F(\'pmplugin_fully_px\');
                                 closeTypeData();
                             }';

        return $saveTypeDataFunc;
    }

    function getTypeHtml($field, $view)
    {
        if (ModUtil::available('Thumbnail')) {
            $typedata = isset($view->_tpl_vars['typedata']) ? $view->_tpl_vars['typedata'] : false;
            $this->parseConfig($typedata);

            // TODO Fieldsets and help text explaining how they work
            $html = '<div class="z-formrow">
                         <label for="pmplugin_tmpx_px">'.$this->__('Thumbnail width').':</label>
                         <input type="text" value="'.$this->config[0].'" id="pmplugin_tmpx_px" name="pmplugin_tmpx_px" />
                     </div>
                     <div class="z-formrow">
                         <label for="pmplugin_tmpy_px">'.$this->__('Thumbnail height').':</label>
                         <input type="text" value="'.$this->config[1].'" id="pmplugin_tmpy_px" name="pmplugin_tmpy_px" />
                         <br />
                     </div>
                     <div class="z-formrow">
                         <label for="pmplugin_pre_px">'.$this->__('Preview width').':</label>
                         <input type="text" value="'.$this->config[2].'" id="pmplugin_previewx_px" name="pmplugin_previewx_px" />
                     </div>
                     <div class="z-formrow">
                         <label for="pmplugin_pre_px">'.$this->__('Preview height').':</label>
                         <input type="text" value="'.$this->config[3].'" id="pmplugin_previewy_px" name="pmplugin_previewy_px" />
                         <br />
                     </div>
                     <div class="z-formrow">
                         <label for="pmplugin_full_px">'.$this->__('Full width').':</label>
                         <input type="text" value="'.$this->config[4].'" id="pmplugin_fullx_px" name="pmplugin_fullx_px" />
                     </div>
                     <div class="z-formrow">
                         <label for="pmplugin_full_px">'.$this->__('Full height').':</label>
                         <input type="text" value="'.$this->config[5].'" id="pmplugin_fully_px" name="pmplugin_fully_px" />
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
        $this->config = array();

        // $tmpx, $tmpy ,$prex, $prey, $fullx, $fully 
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
