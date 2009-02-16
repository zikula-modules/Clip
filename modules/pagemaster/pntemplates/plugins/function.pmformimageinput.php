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
    var $columnDef = 'C(256)';
    var $title     = _PAGEMASTER_PLUGIN_IMAGE;
    var $upl_arr;

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

    function postRead($data, $field)
    {
        if (!empty($data)) {
            $arrTypeData = @unserialize($data);

            if (!is_array($arrTypeData)) {
                return LogUtil::registerError('pmformimageinput: '._PAGEMASTER_STOREDDATAINVALID);
            }

            $url = pnGetBaseURL().pnModGetVar('pagemaster', 'uploadpath');
            if (!empty($arrTypeData['tmb_name'])) {
                $this->upl_arr =  array(
                         'orig_name'    => $arrTypeData['orig_name'],
                         'thumbnailUrl' => !empty($arrTypeData['tmb_name']) ? $url.'/'.$arrTypeData['tmb_name'] : '',
                         'url'          => $url.'/'.$arrTypeData['file_name']
                );
            } else {
                $this->upl_arr = array(
                         'orig_name'    => '',
                         'thumbnailUrl' => '',
                         'url'          => ''
                );
            }

            return $this->upl_arr;

        } else {
            return NULL;
        }
    }

    function preSave($data, $field)
    {
        $id   = $data['id'];
        $tid  = $data['tid'];
        $data = $data[$field['name']];

        // ugly to get old image from DB
        if ($id != NULL) {
            $old_image = DBUtil::selectFieldByID('pagemaster_pubdata'.$tid, $field['name'], $id, 'id');
        }

        if (!empty($data['name'])) {
            $uploadpath = pnModGetVar('pagemaster', 'uploadpath');

            // delete the old file
            if ($id != NULL) {
                $old_image_arr = unserialize($old_image);
                unlink($uploadpath.'/'.$old_image_arr['tmb_name']);
                unlink($uploadpath.'/'.$old_image_arr['file_name']);
            }

            if (!empty($field['typedata']) && strpos($field['typedata'], ':')) {
                list($x, $y) = explode(':', $field['typedata']);
            }

            $thumbargs = array();
            if (isset($x) && $x > 0 && isset($y) && $y > 0) {
                $thumbargs = array(
                    'w' => $x,
                    'h' => $y
                );
            }

            $srcTempFilename = $data['tmp_name'];
            $ext             = strtolower(getExtension($data['name']));
            $randName        = getNewFileReference();
            $new_filename    = "{$randName}.{$ext}";
            $dstFilename     = "{$uploadpath}/{$new_filename}";

            copy($srcTempFilename, $dstFilename);

            // Check for the Thumbnails module and if we need it
            if (!empty($thumbargs) && pnModAvailable('Thumbnail')) {
                $new_filenameTmb = "{$randName}-tmb.{$ext}";
                $dstFilenameTmb  = "{$uploadpath}/{$new_filenameTmb}";
                $thumbargs = array_merge($wh, array('filename'    => $dstFilename,
                                                    'dstFilename' => $dstFilenameTmb));
                $dstName = pnModAPIFunc('Thumbnail', 'user', 'generateThumbnail', $thumbargs);
            } elseif (empty($thumbargs)) {
                // no thumbnail needed
                $new_filenameTmb = $new_filename;
            } else {
                // no thumbnail available
                $new_filenameTmb = '';
            }

            $arrTypeData = array(
                'orig_name' => $data['name'],
                'tmb_name'  => $new_filenameTmb,
                'file_name' => $new_filename
            );

            return serialize($arrTypeData);

        } elseif ($id != NULL) {
            // if it's not a new pub
            // return the old image if no new is selected
            return $old_image;
        }

        return NULL;
    }

    function getSaveTypeDataFunc($field)
    {
        $saveTypeDataFunc = 'function saveTypeData()
                             {
                                 $(\'typedata\').value = $F(\'pmplugin_x_px\')+\':\'+$F(\'pmplugin_y_px\');
                                 closeTypeData();
                             }';
        return $saveTypeDataFunc;
    }

    function getTypeHtml($field, $render)
    {
        $html = '<div class="pn-formrow">
                 <label for="pmplugin_x_px">x:</label><input type="text" id="pmplugin_x_px" name="pmplugin_x_px" />
                 <br />
                 <label for="pmplugin_y_px">y:</label><input type="text" id="pmplugin_y_px" name="pmplugin_y_px" />
                 </div>';
        return $html;
    }
}

function smarty_function_pmformimageinput($params, &$render) {
    return $render->pnFormRegisterPlugin('pmformimageinput', $params);
}
