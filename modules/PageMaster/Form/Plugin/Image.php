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

class PageMaster_Form_Plugin_Image extends Form_Plugin_UploadInput
{
	public $columnDef = 'C(512)';
	public $title;
	public $upl_arr;

    public $config;

    function __construct()
    {
        $dom = ZLanguage::getModuleDomain('PageMaster');
        //! field type name
        $this->title = __('Image Upload', $dom);

        parent::__construct();
    }

	function getFilename()
    {
        return __FILE__;
    }

    function render(&$render)
    {
        $input_html = parent::render($render);

        return $input_html.' '.$this->upl_arr['orig_name'];
    }

    function load(&$render, &$params)
    {
        $this->loadValue($render, $render->get_template_vars());
    }

    function loadValue(&$render, &$values)
    {
        if (isset($values[$this->dataField]) && !empty($values[$this->dataField])) {
            $this->upl_arr = unserialize($values[$this->dataField]);
        }
    }

	static function postRead($data, $field)
	{
	    $dom = ZLanguage::getModuleDomain('PageMaster');

		// this plugin return an array by default
	    $upl_arr = array();

		// if the data is not empty, process it
	    if (!empty($data)) {
			$arrTypeData = @unserialize($data);

			if (!is_array($arrTypeData)) {
				return LogUtil::registerError('pmformimageinput: '.__('Stored data is invalid', $dom));
			}

			$url = pnGetBaseURL().pnModGetVar('PageMaster', 'uploadpath');
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
            $old_image = DBUtil::selectFieldByID('pagemaster_pubdata'.$tid, $field['name'], $id, 'id');
        }

        if (!empty($PostData['name'])) {
            $uploadpath = pnModGetVar('PageMaster', 'uploadpath');

            // delete the old file
            if ($id != NULL) {
                $old_image_arr = unserialize($old_image);
                unlink($uploadpath.'/'.$old_image_arr['tmb_name']);
                unlink($uploadpath.'/'.$old_image_arr['pre_name']);
                unlink($uploadpath.'/'.$old_image_arr['full_name']);
                unlink($uploadpath.'/'.$old_image_arr['file_name']);
            }

            $srcFilename     = $PostData['tmp_name'];
            $ext             = strtolower(PMgetExtension($PostData['name']));
            $randName        = PMgetNewFileReference();
            $newFileNameOrig = $randName.'.'.$ext;
            $newDestOrig     = "{$uploadpath}/{$newFileNameOrig}";
            copy($srcFilename, $newDestOrig);

            $tmpargs  = array();
            $preargs  = array();
            $fullargs = array();
            if (!empty($field['typedata']) && strpos($field['typedata'], ':')) {
                $this->parseConfig($field['typedata']);
                list($tmpx, $tmpy ,$prex, $prey, $fullx, $fully) = $this->config;
                if ($tmpx > 0)
                    $tmpargs['w'] = $tmpx ;
                if ($tmpy > 0)
                    $tmpargs['h'] = $tmpy;
                if ($prex > 0)
                    $preargs['w'] = $prex ;
                if ($prey > 0)
                    $preargs['h'] = $prey ;
                if ($fullx > 0)
                    $fullargs['w'] = $fullx ;
                if ($fully > 0)
                    $fullargs['h'] = $fully ;
            } 

			$srcFilename =   $PostData['tmp_name'];
			$ext             = strtolower(PMgetExtension($PostData['name']));
			$randName        = PMgetNewFileReference();
			$newFileNameOrig = $randName.'.'.$ext;
			$newDestOrig     = "{$uploadpath}/{$newFileNameOrig}";
			copy($srcFilename, $newDestOrig);

			$tmpargs = array();
			$preargs = array();
			$fullargs = array();
			if (!empty($field['typedata']) && strpos($field['typedata'], ':')) {
                $this->parseConfig($field['typedata']);
                list($tmpx, $tmpy ,$prex, $prey, $fullx, $fully) = $this->config;
				if ($tmpx > 0)
				    $tmpargs['w'] = $tmpx ;
				if ($tmpy > 0)
				    $tmpargs['h'] = $tmpy;
				if ($prex > 0)
				    $preargs['w'] = $prex ;
				if ($prey > 0)
				    $preargs['h'] = $prey ;
				if ($fullx > 0)
				    $fullargs['w'] = $fullx ;
				if ($fully > 0)
				    $fullargs['h'] = $fully ;
			}

			// Check for the Thumbnails module and if we need it
			if (!empty($tmpargs) && pnModAvailable('Thumbnail')) {
				$newFilenameTmp = "{$randName}-tmb.{$ext}";
				$newDestTmp  = "{$uploadpath}/{$newFilenameTmp}";
				$tmpargs['filename'] = $newDestOrig;
				$tmpargs['dstFilename'] = $newDestTmp;
				$dstName = pnModAPIFunc('Thumbnail', 'user', 'generateThumbnail', $tmpargs);
            } elseif (empty($tmpargs)) {
                // no thumbnail needed
                $newFilenameTmp = $newFileNameOrig;
            }

            if (!empty($preargs) && pnModAvailable('Thumbnail')) {
                $newFilenamePre = "{$randName}-pre.{$ext}";
                $newDestPre  = "{$uploadpath}/{$newFilenamePre}";
                $preargs['filename'] = $newDestOrig;
                $preargs['dstFilename'] = $newDestPre;
                $dstName = pnModAPIFunc('Thumbnail', 'user', 'generateThumbnail', $preargs);
            } elseif (empty($tmpargs)) {
                // no thumbnail needed
                $newFilenamePre = $newFileNameOrig;
            }

            if (!empty($fullargs) && pnModAvailable('Thumbnail')) {
                $newFilenameFull = "{$randName}-full.{$ext}";
                $newDestFull  = "{$uploadpath}/{$newFilenameFull}";
                $fullargs['filename'] = $newDestOrig;
                $fullargs['dstFilename'] = $newDestFull;
                $dstName = pnModAPIFunc('Thumbnail', 'user', 'generateThumbnail', $fullargs);
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

    function getTypeHtml($field, $render)
    {
        $dom = ZLanguage::getModuleDomain('PageMaster');
        
        if (pnModAvailable('Thumbnail')) {
            $typedata = isset($render->_tpl_vars['typedata']) ? $render->_tpl_vars['typedata'] : false;
            $this->parseConfig($typedata);

            // TODO Fieldsets and help text explaining how they work
            $html = '<div class="z-formrow">
                         <label for="pmplugin_tmpx_px">'.__('Thumbnail width', $dom).':</label>
                         <input type="text" value="'.$this->config[0].'" id="pmplugin_tmpx_px" name="pmplugin_tmpx_px" />
                     </div>
                     <div class="z-formrow">
                         <label for="pmplugin_tmpy_px">'.__('Thumbnail height', $dom).':</label>
                         <input type="text" value="'.$this->config[1].'" id="pmplugin_tmpy_px" name="pmplugin_tmpy_px" />
                         <br />
                     </div>
                     <div class="z-formrow">
                         <label for="pmplugin_pre_px">'.__('Preview width', $dom).':</label>
                         <input type="text" value="'.$this->config[2].'" id="pmplugin_previewx_px" name="pmplugin_previewx_px" />
                     </div>
                     <div class="z-formrow">
                         <label for="pmplugin_pre_px">'.__('Preview height', $dom).':</label>
                         <input type="text" value="'.$this->config[3].'" id="pmplugin_previewy_px" name="pmplugin_previewy_px" />
                         <br />
                     </div>
                     <div class="z-formrow">
                         <label for="pmplugin_full_px">'.__('Full width', $dom).':</label>
                         <input type="text" value="'.$this->config[4].'" id="pmplugin_fullx_px" name="pmplugin_fullx_px" />
                     </div>
                     <div class="z-formrow">
                         <label for="pmplugin_full_px">'.__('Full height', $dom).':</label>
                         <input type="text" value="'.$this->config[5].'" id="pmplugin_fully_px" name="pmplugin_fully_px" />
                     </div>';
        } else {
            $html = '<div class="z-warningmsg">
                         '.__('Warning! The Thumbnails module is not available. This plugin needs it to build the Preview and Thumbnail of each uploaded Image.', $dom).'
                     </div>';
        }

        return $html;
    }

    /**
     * Parse configuration
     */
    function parseConfig($typedata = '', $args = array())
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
