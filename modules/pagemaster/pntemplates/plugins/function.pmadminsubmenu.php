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
    $dom = ZLanguage::getModuleDomain('pagemaster');

    $tid = (int)$params['tid'];

    if (!$tid) {
        return LogUtil::registerError(__f('Error! Missing argument [%s].', 'tid', $dom));
    }

    $func = FormUtil::getPassedValue('func', 'main');

    // build the output
    $output = '<div class="z-menu pm-menu"><span class="z-menuitem-title">';

    // pubtype form link
    if ($func != 'pubtype') {
        $output .= '<a href="'.pnModURL('pagemaster', 'admin', 'pubtype', array('tid' => $tid)).'">'.__('Pubtype form', $dom).'</a> | ';
    } else {
        $output .= '<a>'.__('Pubtype form', $dom).'</a> | ';
    }

    // edit fields link
    if ($func != 'pubfields') {
        $output .= '<a href="'.pnModURL('pagemaster', 'admin', 'pubfields', array('tid' => $tid)).'">'.__('Publication fields', $dom).'</a> | ';
    } elseif ($params['id'] != '') {
        $output .= '<a href="'.pnModURL('pagemaster', 'admin', 'pubfields', array('tid' => $tid)).'#newpubfield">'.__('Publication fields', $dom).'</a> | ';
    } else {
        $output .= '<a href="#newpubfield">'.__('Publication fields', $dom).'</a> | ';
    }

    // new article link
    $output .= '<a href="'.pnModURL('pagemaster', 'user', 'pubedit', array('tid' => $tid)).'">'.__('New publication', $dom).'</a> | ';

    // pub list link
    if ($func != 'publist') {
        $output .= '<a href="'.pnModURL('pagemaster', 'admin', 'publist', array('tid' => $tid)).'">'.__('Publications list', $dom).'</a> | ';
    } else {
        $output .= '<a>'.__('Publications list', $dom).'</a> | ';
    }

    // show code links
    if ($func == 'showcode') {
        $output .= ($params['mode'] == 'input'      ? '<a>' : '<a href="'.pnModURL('pagemaster', 'admin', 'showcode', array('tid' => $tid, 'mode' => 'input')).'">') . __('Show form code', $dom).'</a> | ';
        $output .= ($params['mode'] == 'outputlist' ? '<a>' : '<a href="'.pnModURL('pagemaster', 'admin', 'showcode', array('tid' => $tid, 'mode' => 'outputlist')).'">') . __('Show pub list code', $dom).'</a> | ';
        $output .= ($params['mode'] == 'outputfull' ? '<a>' : '<a href="'.pnModURL('pagemaster', 'admin', 'showcode', array('tid' => $tid, 'mode' => 'outputfull')).'">') . __('Show pub view code', $dom).'</a>';
    } else {
        $output .= '<a href="'.pnModURL('pagemaster', 'admin', 'showcode', array('tid' => $tid, 'mode' => 'input')).'">'.__('Show form code', $dom).'</a>';
    }

    $output .= '</span></div>';

    return DataUtil::formatForDisplayHTML($output);
}
