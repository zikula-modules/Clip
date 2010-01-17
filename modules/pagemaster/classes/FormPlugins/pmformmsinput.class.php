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

require_once ('system/pnForm/plugins/function.pnformtextinput.php');

class pmformmsinput extends pnFormTextInput
{
    var $columnDef = 'C(255)';
    var $title = 'Mediashare';

    function getFilename()
    {
        return __FILE__; // FIXME: may be found in smarty's data???
    }

    static function postRead($data, $field)
    {
        $lang =ZLanguage::getLanguageCode();
        Loader :: loadClass('CategoryUtil');
        $cat = CategoryUtil :: getCategoryByID($data);

        //compatible mode to pagesetter
        $cat['fullTitle'] = $cat['display_name'][$lang];
        $cat['value'] = $cat['name'];
        $cat['title'] = $cat['name'];
        return $cat;
    }
}
