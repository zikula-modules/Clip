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
    $dom = ZLanguage::getModuleDomain('PageMaster');

    $tid = (int)$params['tid'];

    if (!$tid) {
        return LogUtil::registerError(__f('Error! Missing argument [%s].', 'tid', $dom));
    }

    $pubtype = PMgetPubType($tid);

    // build the output
    $output  = '<div class="z-menu"><span class="z-menuitem-title">';
    $output .= '<span class="pm-option">'.$pubtype['title'].'</span><span class="pm-option">&raquo;</span>';

    $func = FormUtil::getPassedValue('func', 'main');

    // pubtype form link
    $output .= '<span>';
    if ($func != 'pubtype') {
        $output .= '<a href="'.pnModURL('PageMaster', 'admin', 'pubtype', array('tid' => $tid)).'">'.__('Options', $dom).'</a>';
    } else {
        $output .= '<a>'.__('Options', $dom).'</a>';
    }
    $output .= '</span> | ';

    // edit fields link
    $output .= '<span>';
    if ($func != 'pubfields') {
        $output .= '<a href="'.pnModURL('PageMaster', 'admin', 'pubfields', array('tid' => $tid)).'">'.__('Fields', $dom).'</a>';
    } elseif ($params['id'] != '') {
        $output .= '<a href="'.pnModURL('PageMaster', 'admin', 'pubfields', array('tid' => $tid)).'#newpubfield">'.__('Fields', $dom).'</a>';
    } else {
        $output .= '<a href="#newpubfield">'.__('Fields', $dom).'</a>';
    }
    $output .= '</span> | ';

    // new article link
    $output .= '<span>';
    $output .= '<a href="'.pnModURL('PageMaster', 'user', 'pubedit', array('tid' => $tid, 'goto' => 'referer')).'">'.__('New publication', $dom).'</a>';
    $output .= '</span> | ';

    // pub list link
    $output .= '<span>';
    if ($func != 'publist') {
        $output .= '<a href="'.pnModURL('PageMaster', 'admin', 'publist', array('tid' => $tid)).'">'.__('Publication list', $dom).'</a>';
    } else {
        $output .= '<a>'.__('Publication list', $dom).'</a>';
    }
    $output .= '</span> | ';

    // show code links
    if ($func == 'showcode') {
        $output .= '<span>'.($params['mode'] == 'input'      ? '<a>' : '<a href="'.pnModURL('PageMaster', 'admin', 'showcode', array('tid' => $tid, 'mode' => 'input')).'">') . __('Input template', $dom).'</a></span> | ';
        $output .= '<span>'.($params['mode'] == 'outputlist' ? '<a>' : '<a href="'.pnModURL('PageMaster', 'admin', 'showcode', array('tid' => $tid, 'mode' => 'outputlist')).'">') . __('List template', $dom).'</a></span> | ';
        $output .= '<span>'.($params['mode'] == 'outputfull' ? '<a>' : '<a href="'.pnModURL('PageMaster', 'admin', 'showcode', array('tid' => $tid, 'mode' => 'outputfull')).'">') . __('Display template', $dom).'</a></span>';
    } else {
        $output .= '<span><a href="'.pnModURL('PageMaster', 'admin', 'showcode', array('tid' => $tid, 'mode' => 'input')).'">'.__('Generate templates', $dom).'</a></span>';
    }

    $output .= '</span></div>';

    return $output;
}
