<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage View_Plugins
 */

/**
 * Displays the admin sub menu.
 *
 * @param  $params['tid'] tid.
 */
function smarty_function_clip_submenu($params, $view)
{
    $dom = ZLanguage::getModuleDomain('Clip');
    include_once('modules/Clip/templates/plugins/function.clip_url.php');

    $tid = (int)$params['tid'];

    if (!$tid) {
        return LogUtil::registerError(__f('Error! Missing argument [%s].', 'tid', $dom));
    }

    $pubtype = Clip_Util::getPubType($tid);

    // build the output
    $output  = '<div class="z-menu"><span class="z-menuitem-title clip-breadcrumbs">';
    $output .= '<span class="clip-option">';
    $args = array('func' => 'pubtypeinfo', 'args' => array('tid' => $tid));
    $output .= '<a href="'.smarty_function_clip_url($args, $view).'">'.__('Info', $dom).'</a>';
    $output .= '</span> | ';

    $func = FormUtil::getPassedValue('func', 'main');

    // pubtype form link
    $output .= '<span>';
    if ($func != 'pubtype') {
        $output .= DataUtil::formatForDisplayHTML('<a href="'.ModUtil::url('Clip', 'admin', 'pubtype', array('tid' => $tid)).'">'.__('Edit', $dom).'</a>');
    } else {
        $output .= DataUtil::formatForDisplayHTML('<a href="#">'.__('Edit', $dom).'</a>');
    }
    $output .= '</span> | ';

    // edit fields link
    $output .= '<span>';
    if ($func != 'pubfields') {
        $output .= DataUtil::formatForDisplayHTML('<a href="'.ModUtil::url('Clip', 'admin', 'pubfields', array('tid' => $tid)).'">'.__('Fields', $dom).'</a>');
    } elseif (isset($params['field']) && $params['field']) {
        $output .= DataUtil::formatForDisplayHTML('<a href="'.ModUtil::url('Clip', 'admin', 'pubfields', array('tid' => $tid)).'#newpubfield">'.__('Fields', $dom).'</a>');
    } else {
        $output .= DataUtil::formatForDisplayHTML('<a href="#newpubfield">'.__('Fields', $dom).'</a>');
    }
    $output .= '</span> | ';

    // relations link
    $output .= '<span>';
    $output .= DataUtil::formatForDisplayHTML('<a href="'.ModUtil::url('Clip', 'admin', 'relations', array('withtid1' => $tid, 'op' => 'or', 'withtid2' => $tid)).'">'.__('Relations', $dom).'</a>');

    // show code links
    $args = array('func' => 'generator', 'args' => array('tid' => $tid, 'code' => 'form'));
    if ($func == 'generator') {
        $output .= '<br />';
        $output .= '<span class="clip-option">'.DataUtil::formatForDisplay(__('Generate templates', $dom)).'</span><span class="clip-option">&raquo;</span>';
        $args['args']['code'] = 'form';
        $output .= '<span>'.($params['code'] == 'form'      ? '<a href="#">' : '<a href="'.smarty_function_clip_url($args, $view).'">') . __('Input template', $dom).'</a></span> | ';
        $args['args']['code'] = 'list';
        $output .= '<span>'.($params['code'] == 'list'      ? '<a href="#">' : '<a href="'.smarty_function_clip_url($args, $view).'">') . __('List template', $dom).'</a></span> | ';
        $args['args']['code'] = 'display';
        $output .= '<span>'.($params['code'] == 'display'   ? '<a href="#">' : '<a href="'.smarty_function_clip_url($args, $view).'">') . __('Display template', $dom).'</a></span> | ';
        $args['args']['code'] = 'blocklist';
        $output .= '<span>'.($params['code'] == 'blocklist' ? '<a href="#">' : '<a href="'.smarty_function_clip_url($args, $view).'">') . __('List block', $dom).'</a></span> | ';
        $args['args']['code'] = 'blockpub';
        $output .= '<span>'.($params['code'] == 'blockpub'  ? '<a href="#">' : '<a href="'.smarty_function_clip_url($args, $view).'">') . __('Pub block', $dom).'</a></span>';
    } else {
        $output .= '</span> | ';
        $output .= '<span><a href="'.smarty_function_clip_url($args, $view).'">'.__('Generate templates', $dom).'</a></span>';
    }

    $output .= '</span></div>';

    return $output;
}
