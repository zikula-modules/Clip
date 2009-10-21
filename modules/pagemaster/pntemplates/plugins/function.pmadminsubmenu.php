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

/**
 * Displays the admin sub menu
 * @author mateo
 * @param $args['tid'] tid
 */
function smarty_function_pmadminsubmenu($params, &$smarty)
{
    $tid = $params['tid'];

    if (!$tid) {
        return 'Required parameter [tid] not provided in smarty_function_pmadminsubmenu';
    }

    $func = FormUtil::getPassedValue('func', 'main');

    // build the output
    $output = '<div class="pn-menu pm-menu"><span class="pn-menuitem-title">';

    // pubtype form link
    if ($func != 'create_tid') {
        $output .= '<a href="'.pnModURL('pagemaster', 'admin', 'create_tid', array('tid' => $tid)).'">'.pnML('_PAGEMASTER_PUBTYPE_FORM').'</a> | ';
    } else {
        $output .= '<a>'.pnML('_PAGEMASTER_PUBTYPE_FORM').'</a> | ';
    }

    // pub list link
    if ($func != 'publist') {
        $output .= '<a href="'.pnModURL('pagemaster', 'admin', 'publist', array('tid' => $tid)).'">'.pnML('_PAGEMASTER_LIST').'</a> | ';
    } else {
        $output .= '<a>'.pnML('_PAGEMASTER_LIST').'</a> | ';
    }

    // edit fields link
    if ($func != 'editpubfields') {
        $output .= '<a href="'.pnModURL('pagemaster', 'admin', 'editpubfields', array('tid' => $tid)).'">'.pnML('_PAGEMASTER_EDIT_FIELDS').'</a> | ';
    } elseif ($params['id'] != '') {
        $output .= '<a href="'.pnModURL('pagemaster', 'admin', 'editpubfields', array('tid' => $tid)).'#newpubfield">'.pnML('_PAGEMASTER_EDIT_FIELDS').'</a> | ';
    } else {
        $output .= '<a href="#newpubfield">'.pnML('_PAGEMASTER_EDIT_FIELDS').'</a> | ';
    }

    // new article link
    $output .= '<a href="'.pnModURL('pagemaster', 'user', 'pubedit', array('tid' => $tid)).'">'.pnML('_PAGEMASTER_PUBTYPE_NEWARTICLE').'</a> | ';

    // show code links
    if ($func == 'showcode') {
        $output .= ($params['mode'] == 'input' ? '<a>' : '<a href="'.pnModURL('pagemaster', 'admin', 'showcode', array('tid' => $tid, 'mode' => 'input')).'">') . pnML('_PAGEMASTER_PUBTYPE_SHOWINPUTCODE').'</a> | ';
        $output .= ($params['mode'] == 'outputlist' ? '<a>' : '<a href="'.pnModURL('pagemaster', 'admin', 'showcode', array('tid' => $tid, 'mode' => 'outputlist')).'">') . pnML('_PAGEMASTER_PUBTYPE_SHOWOUTPUTCODELIST').'</a> | ';
        $output .= ($params['mode'] == 'outputfull' ? '<a>' : '<a href="'.pnModURL('pagemaster', 'admin', 'showcode', array('tid' => $tid, 'mode' => 'outputfull')).'">') . pnML('_PAGEMASTER_PUBTYPE_SHOWOUTPUTCODEFULL').'</a>';
    } else {
        $output .= '<a href="'.pnModURL('pagemaster', 'admin', 'showcode', array('tid' => $tid, 'mode' => 'input')).'">'.pnML('_PAGEMASTER_PUBTYPE_SHOWINPUTCODE').'</a>';
    }

    $output .= '</span></div>';

    return $output;
}
