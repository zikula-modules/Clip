<?php
/**
 * PageMaster
 *
 * @copyright   (c) PageMaster Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package     Zikula_3rdParty_Modules
 * @subpackage  pagemaster
 */

$modinfo = ModUtil::getInfoFromName('PageMaster');

if ($modinfo['state'] == ModUtil::STATE_ACTIVE) {
    $pubtypes = array_keys(PageMaster_Util::getPubType(-1)->toArray());
    sort($pubtypes);

    foreach ($pubtypes as $tid) {
        $code = PageMaster_Generator::pubmodel($tid);
//echo "<pre>$code</pre>";
        eval($code);
        $code = PageMaster_Generator::pubtable($tid);
        eval($code);
    }

    PageMaster_Generator::evalrelations();
//die();
}
