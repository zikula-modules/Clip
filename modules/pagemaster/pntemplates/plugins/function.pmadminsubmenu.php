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
    $output = '<div class="z-menu pm-menu"><span class="z-menuitem-title">';

    // pubtype form link
    if ($func != 'create_tid') {
        $output .= '<a href="'.pnModURL('pagemaster', 'admin', 'create_tid', array('tid' => $tid)).'">'.__('Pubtype form').'</a> | ';
    } else {
        $output .= '<a>'.__('Pubtype form').'</a> | ';
    }

    // pub list link
    if ($func != 'publist') {
        $output .= '<a href="'.pnModURL('pagemaster', 'admin', 'publist', array('tid' => $tid)).'">'.__('Publications list').'</a> | ';
    } else {
        $output .= '<a>'.__('Publications list').'</a> | ';
    }

    // edit fields link
    if ($func != 'editpubfields') {
        $output .= '<a href="'.pnModURL('pagemaster', 'admin', 'editpubfields', array('tid' => $tid)).'">'.__('Publication fields').'</a> | ';
    } elseif ($params['id'] != '') {
        $output .= '<a href="'.pnModURL('pagemaster', 'admin', 'editpubfields', array('tid' => $tid)).'#newpubfield">'.__('Publication fields').'</a> | ';
    } else {
        $output .= '<a href="#newpubfield">'.__('Publication fields').'</a> | ';
    }

    // new article link
    $output .= '<a href="'.pnModURL('pagemaster', 'user', 'pubedit', array('tid' => $tid)).'">'.__('New Publication').'</a> | ';

    // show code links
    if ($func == 'showcode') {
        $output .= ($params['mode'] == 'input' ? '<a>' : '<a href="'.pnModURL('pagemaster', 'admin', 'showcode', array('tid' => $tid, 'mode' => 'input')).'">') . __('Show Form Code').'</a> | ';
        $output .= ($params['mode'] == 'outputlist' ? '<a>' : '<a href="'.pnModURL('pagemaster', 'admin', 'showcode', array('tid' => $tid, 'mode' => 'outputlist')).'">') . __('Show PubList Code').'</a> | ';
        $output .= ($params['mode'] == 'outputfull' ? '<a>' : '<a href="'.pnModURL('pagemaster', 'admin', 'showcode', array('tid' => $tid, 'mode' => 'outputfull')).'">') . __('Show PubView Code').'</a>';
    } else {
        $output .= '<a href="'.pnModURL('pagemaster', 'admin', 'showcode', array('tid' => $tid, 'mode' => 'input')).'">'.__('Show Form Code').'</a>';
    }

    $output .= '</span></div>';

    return $output;
}
