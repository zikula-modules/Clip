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
        $id   = $data['id'];
        $tid  = $data['tid'];
        $data = $data[$field['name']];

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

        } elseif ($id != NULL) {
            // if it's not a new pub
            // return the old image if no new is selected
            return DBUtil::selectFieldByID('pagemaster_pubdata'.$tid, $field['name'], $id, 'id');

        }

        return NULL;
    }
}

function smarty_function_pmformuploadinput($params, &$render) {
    return $render->pnFormRegisterPlugin('pmformuploadinput', $params);
}
