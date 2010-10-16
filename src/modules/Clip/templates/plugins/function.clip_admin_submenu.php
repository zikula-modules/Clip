<?php
/**
 * Clip
 *
 * @copyright   (c) Clip Team
 * @link        http://code.zikula.org/clip/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @version     $ Id $
 * @package     Zikula_3rdParty_Modules
 * @subpackage  clip
 */

/**
 * Displays the admin sub menu
 *
 * @author mateo
 * @param $args['tid'] tid
 */
function smarty_function_clip_admin_submenu($params, &$smarty)
{
    $dom = ZLanguage::getModuleDomain('Clip');

    $tid = (int)$params['tid'];

    if (!$tid) {
        return LogUtil::registerError(__f('Error! Missing argument [%s].', 'tid', $dom));
    }

    $pubtype = Clip_Util::getPubType($tid);

    // build the output
    $output  = '<div class="z-menu"><span class="z-menuitem-title">';
    $output .= '<span class="clip-option">'.$pubtype['title'].'</span><span class="clip-option">&raquo;</span>';

    $func = FormUtil::getPassedValue('func', 'main');

    // pubtype form link
    $output .= '<span>';
    if ($func != 'pubtype') {
        $output .= '<a href="'.ModUtil::url('Clip', 'admin', 'pubtype', array('tid' => $tid)).'">'.__('Options', $dom).'</a>';
    } else {
        $output .= '<a>'.__('Options', $dom).'</a>';
    }
    $output .= '</span> | ';

    // edit fields link
    $output .= '<span>';
    if ($func != 'pubfields') {
        $output .= '<a href="'.ModUtil::url('Clip', 'admin', 'pubfields', array('tid' => $tid)).'">'.__('Fields', $dom).'</a>';
    } elseif (isset($params['field']) != '') {
        $output .= '<a href="'.ModUtil::url('Clip', 'admin', 'pubfields', array('tid' => $tid)).'#newpubfield">'.__('Fields', $dom).'</a>';
    } else {
        $output .= '<a href="#newpubfield">'.__('Fields', $dom).'</a>';
    }
    $output .= '</span> | ';

    // new article link
    $output .= '<span>';
    $output .= '<a href="'.ModUtil::url('Clip', 'user', 'edit', array('tid' => $tid, 'goto' => 'referer')).'">'.__('New publication', $dom).'</a>';
    $output .= '</span> | ';

    // pub list link
    $output .= '<span>';
    if ($func != 'publist') {
        $output .= '<a href="'.ModUtil::url('Clip', 'admin', 'publist', array('tid' => $tid)).'">'.__('Publication list', $dom).'</a>';
    } else {
        $output .= '<a>'.__('Publication list', $dom).'</a>';
    }

    // show code links
    if ($func == 'showcode') {
        $output .= '<br />';
        $output .= '<span class="clip-option">'.__('Generate templates', $dom).'</span><span class="clip-option">&raquo;</span>';
        $output .= '<span>'.($params['mode'] == 'input'      ? '<a>' : '<a href="'.ModUtil::url('Clip', 'admin', 'showcode', array('tid' => $tid, 'mode' => 'input')).'">') . __('Input template', $dom).'</a></span> | ';
        $output .= '<span>'.($params['mode'] == 'outputlist' ? '<a>' : '<a href="'.ModUtil::url('Clip', 'admin', 'showcode', array('tid' => $tid, 'mode' => 'outputlist')).'">') . __('List template', $dom).'</a></span> | ';
        $output .= '<span>'.($params['mode'] == 'outputfull' ? '<a>' : '<a href="'.ModUtil::url('Clip', 'admin', 'showcode', array('tid' => $tid, 'mode' => 'outputfull')).'">') . __('Display template', $dom).'</a></span>';
    } else {
        $output .= '</span> | ';
        $output .= '<span><a href="'.ModUtil::url('Clip', 'admin', 'showcode', array('tid' => $tid, 'mode' => 'input')).'">'.__('Generate templates', $dom).'</a></span>';
    }

    $output .= '</span></div>';

    return $output;
}
