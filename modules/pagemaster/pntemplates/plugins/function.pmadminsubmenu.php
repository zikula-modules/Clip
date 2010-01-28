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
 *
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
    if ($func != 'pubtype') {
        $output .= '<a href="'.pnModURL('pagemaster', 'admin', 'pubtype', array('tid' => $tid)).'">'.__('Pubtype form').'</a> | ';
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
    if ($func != 'pubfields') {
        $output .= '<a href="'.pnModURL('pagemaster', 'admin', 'pubfields', array('tid' => $tid)).'">'.__('Publication fields').'</a> | ';
    } elseif ($params['id'] != '') {
        $output .= '<a href="'.pnModURL('pagemaster', 'admin', 'pubfields', array('tid' => $tid)).'#newpubfield">'.__('Publication fields').'</a> | ';
    } else {
        $output .= '<a href="#newpubfield">'.__('Publication fields').'</a> | ';
    }

    // new article link
    $output .= '<a href="'.pnModURL('pagemaster', 'user', 'pubedit', array('tid' => $tid)).'">'.__('New publication').'</a> | ';

    // show code links
    if ($func == 'showcode') {
        $output .= ($params['mode'] == 'input' ? '<a>' : '<a href="'.pnModURL('pagemaster', 'admin', 'showcode', array('tid' => $tid, 'mode' => 'input')).'">') . __('Show form code').'</a> | ';
        $output .= ($params['mode'] == 'outputlist' ? '<a>' : '<a href="'.pnModURL('pagemaster', 'admin', 'showcode', array('tid' => $tid, 'mode' => 'outputlist')).'">') . __('Show pub list code').'</a> | ';
        $output .= ($params['mode'] == 'outputfull' ? '<a>' : '<a href="'.pnModURL('pagemaster', 'admin', 'showcode', array('tid' => $tid, 'mode' => 'outputfull')).'">') . __('Show pub view code').'</a>';
    } else {
        $output .= '<a href="'.pnModURL('pagemaster', 'admin', 'showcode', array('tid' => $tid, 'mode' => 'input')).'">'.__('Show form code').'</a>';
    }

    $output .= '</span></div>';

    return $output;
}
