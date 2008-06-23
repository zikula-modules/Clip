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

class pmformuploadinput extends pnFormUploadInput
{
    var $columnDef = 'C(512)';
    var $title     = 'Any Upload';

    function getFilename()
    {
        return __FILE__; // FIXME: may be found in smarty's data???
    }

    function postRead($data, $field)
    {
        return unserialize($data);
    }

    function preSave($data, $field)
    {
        if ($data <> '' and !empty ($_FILES)) {
            $uploadpath = pnModGetVar('pagemaster', 'uploadpath');
            // TODO: delete the old file
            $srcTempFilename = $data['tmp_name'];
            $ext             = strtolower(getExtension($data['name']));
            $randName        = getNewFileReference();
            $new_filename    = $randName . '.' . $ext;
            $dstFilename     = $uploadpath . '/' . $new_filename;

            copy($srcTempFilename, $dstFilename);

            $arrTypeData = array (
                'orig_name' => $data['name'],
                'file_name' => $dstFilename
            );
            return serialize($arrTypeData);
        }
    }
}

function smarty_function_pmformuploadinput($params, &$render) {
    return $render->pnFormRegisterPlugin('pmformuploadinput', $params);
}
