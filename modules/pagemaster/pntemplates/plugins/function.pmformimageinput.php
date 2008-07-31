<?php
/**
 * PageMaster
 *
 * @copyright (c) 2008, PageMaster Team
 * @link        http://code.zikula.org/projects/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package     Zikula_3rd_party_Modules
 * @subpackage  pagemaster
 */

require_once('system/pnForm/plugins/function.pnformuploadinput.php');

class pmformimageinput extends pnFormUploadInput
{
	var $columnDef = 'C(256)';
	var $title     = 'Image Upload';

	function getFilename()
	{
		return __FILE__; // FIXME: may be found in smarty's data???
	}

	function postRead($data, $field)
	{
		if (!empty($data)) {
			$arrTypeData = @unserialize($data);

			if (!is_array($arrTypeData)) {
				return LogUtil::registerError('error in pmformimageinput: stored data is invalid');
			}

			$DirPM = pnModGetVar('pagemaster', 'uploadpath');
			if ($arrTypeData['tmb_name'] <> ''){
				$img_arr =  array(
                         'orig_name'    => $arrTypeData['orig_name'],
                         'thumbnailUrl' => $DirPM.'/'.$arrTypeData['tmb_name'],
                         'url'          => $DirPM.'/'.$arrTypeData['file_name']
				);
			}else{
				$img_arr = array(
                         'orig_name'    => '',
                         'thumbnailUrl' => '',
                         'url'          => ''
				);}
			return $img_arr;
				 
		} else {
			return NULL;
		}
	}

	function preSave($data, $field)
	{
		$id   = $data['id'];
		$tid  = $data['tid'];
		$data = $data[$field['name']];

		if ($data['name'] <> '' && !empty($_FILES)) {
			$uploadpath = pnModGetVar('pagemaster', 'uploadpath');

			//TODO: delete the old file
			list ($x, $y) = explode(':', $field['typedata']);
			$wh = array();
			if ($x > 0 and $y > 0) {
				$wh = array (
                    'w' => $x,
                    'h' => $y
				);
			}

			$srcTempFilename = $data['tmp_name'];
			$ext             = strtolower(getExtension($data['name']));
			$randName        = getNewFileReference();
			$new_filename    = $randName . '.' . $ext;
			$new_filenameTmb = $randName . '-tmb.' . $ext;
			$dstFilename     = $uploadpath . '/' . $new_filename;
			$dstFilenameTmb  = $uploadpath . '/' . $new_filenameTmb;

			copy($srcTempFilename, $dstFilename);

			$dstName = pnModAPIFunc('Thumbnail', 'user', 'generateThumbnail',
			array_merge($wh, array('filename'    => $dstFilename,
                                                           'dstFilename' => $dstFilenameTmb)));
			$arrTypeData = array(
                'orig_name' => $data['name'],
                'tmb_name'  => $new_filenameTmb,
                'file_name' => $new_filename
			);

			return serialize($arrTypeData);

		} elseif ($id != NULL) {
			// if it's not a new pub
			// return the old image if no new is selected
			return DBUtil::selectFieldByID('pagemaster_pubdata'.$tid, $field['name'], $id, 'id');;
		}

		return NULL;
	}

	function getSaveTypeDataFunc($field)
	{
		$saveTypeDataFunc = 'function saveTypeData()
                             {
                                 document.getElementById(\'typedata\').value = document.getElementById(\'pagemaster_x_px\').value+\':\'+document.getElementById(\'pagemaster_y_px\').value;
                                 document.getElementById(\'typeDataDiv\').style.display = \'none\';
                             }';
		return $saveTypeDataFunc;
	}

	function getTypeHtml($field, $render)
	{
		$html .= 'x: <input type="text" id="pagemaster_x_px" name="pagemaster_x_px" />';
		$html .= 'y: <input type="text" id="pagemaster_y_px" name="pagemaster_y_px" />';
		return $html;
	}
}

function smarty_function_pmformimageinput($params, &$render) {
	return $render->pnFormRegisterPlugin('pmformimageinput', $params);
}
