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

class pmformimageinput extends pnFormUploadInput
{
	var $columnDef = 'C(512)';
	var $title;
	var $upl_arr;

    function __construct()
    {
        $dom = ZLanguage::getModuleDomain('pagemaster');
        //! field type name
        $this->title = __('Image Upload', $dom);

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
	    $dom = ZLanguage::getModuleDomain('pagemaster');

		// this plugin return an array by default
	    $upl_arr = array();

		// if the data is not empty, process it
	    if (!empty($data)) {
			$arrTypeData = @unserialize($data);

			if (!is_array($arrTypeData)) {
				return LogUtil::registerError('pmformimageinput: '.__('Stored data is invalid', $dom));
			}

			$url = pnGetBaseURL().pnModGetVar('pagemaster', 'uploadpath');
			if (!empty($arrTypeData['orig_name'])) {
				$upl_arr =  array(
                         'orig_name'    => $arrTypeData['orig_name'],
                         'preUrl'        => !empty($arrTypeData['pre_name']) ? $url.'/'.$arrTypeData['pre_name'] : '',
                         'fullUrl'      => !empty($arrTypeData['full_name']) ? $url.'/'.$arrTypeData['full_name'] : '',
                         'thumbnailUrl' => !empty($arrTypeData['tmb_name']) ? $url.'/'.$arrTypeData['tmb_name'] : '',
                         'url'          => $url.'/'.$arrTypeData['file_name']
                );
            } else {
                $upl_arr = array(
                         'orig_name'    => '',
                         'preUrl'        => '',
                         'fullUrl'      => '',
                         'thumbnailUrl' => '',
                         'url'          => ''
                         );
            }
		}

        return $upl_arr;
    }

    static function preSave($data, $field)
    {
        $id   = $data['id'];
        $tid  = $data['tid'];
        $PostData = $data[$field['name']];

        // ugly to get old image from DB
        if ($id != NULL) {
            $old_image = DBUtil::selectFieldByID('pagemaster_pubdata'.$tid, $field['name'], $id, 'id');
        }

        if (!empty($PostData['name'])) {
            $uploadpath = pnModGetVar('pagemaster', 'uploadpath');

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
            $tmpargs = array();
            $preargs = array();
            $fullargs = array();
            if (!empty($field['typedata']) && strpos($field['typedata'], ':')) {
                list($tmpx, $tmpy ,$prex, $prey, $fullx, $fully) = explode(':', $field['typedata']);
                if ((int)$tmpx > 0)
                    $tmpargs['w'] = (int)$tmpx ;
                if ((int)$tmpy > 0)
                    $tmpargs['h'] = (int)$tmpy;
                if ((int)$prex > 0)
                    $preargs['w'] = (int)$prex ;
                if ((int)$prey > 0)
                    $preargs['h'] = (int)$prey ;
                if ((int)$fullx > 0)
                    $fullargs['w'] = (int)$fullx ;
                if ((int)$fully > 0)
                    $fullargs['h'] = (int)$fully ;
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
				list($tmpx, $tmpy ,$prex, $prey, $fullx, $fully) = explode(':', $field['typedata']);
				if ((int)$tmpx > 0)
				    $tmpargs['w'] = (int)$tmpx ;
				if ((int)$tmpy > 0)
				    $tmpargs['h'] = (int)$tmpy;
				if ((int)$prex > 0)
				    $preargs['w'] = (int)$prex ;
				if ((int)$prey > 0)
				    $preargs['h'] = (int)$prey ;
				if ((int)$fullx > 0)
				    $fullargs['w'] = (int)$fullx ;
				if ((int)$fully > 0)
				    $fullargs['h'] = (int)$fully ;
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

    static function getTypeHtml($field, $render)
    {
        $html = '<div class="z-formrow">
                     <label for="pmplugin_tmpx_px">Thumbnail x:</label>
                     <input type="text" id="pmplugin_tmpx_px" name="pmplugin_tmpx_px" />
                 </div>
                 <div class="z-formrow">
                     <label for="pmplugin_tmpy_px">Thumbnail y:</label>
                     <input type="text" id="pmplugin_tmpy_px" name="pmplugin_tmpy_px" />
                     <br />
                 </div>
                 <div class="z-formrow">
                     <label for="pmplugin_pre_px">Preview x:</label>
                     <input type="text" id="pmplugin_previewx_px" name="pmplugin_previewx_px" />
                 </div>
                 <div class="z-formrow">
                     <label for="pmplugin_pre_px">Preview y:</label>
                     <input type="text" id="pmplugin_previewy_px" name="pmplugin_previewy_px" />
                     <br />
                 </div>
                 <div class="z-formrow">
                   <label for="pmplugin_full_px">Full x:</label>
                   <input type="text" id="pmplugin_fullx_px" name="pmplugin_fullx_px" />
                 </div>
                 <div class="z-formrow">
                   <label for="pmplugin_full_px">Full y:</label>
                   <input type="text" id="pmplugin_fully_px" name="pmplugin_fully_px" />
                 </div>';

        return $html;
    }
}
