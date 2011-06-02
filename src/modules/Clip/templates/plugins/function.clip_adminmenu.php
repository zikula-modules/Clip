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
 * Builds and displays the admin submenu.
 *
 * Available parameters:
 *  - tid (integer) Publication type ID.
 *
 * Example:
 *
 *  <samp>{clip_adminmenu tid=$pubtype.tid}</samp>
 *
 * @param array       $params All parameters passed to this plugin from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return mixed False on failure, HTML output otherwise.
 */
function smarty_function_clip_adminmenu($params, Zikula_View &$view)
{
    include_once('modules/Clip/templates/plugins/function.clip_url.php');

    $tid = (int)$params['tid'];

    if (!$tid) {
        return LogUtil::registerError($view->__f('%1$s: Invalid publication type ID passed [%2$s].', array('{clip_adminmenu}', DataUtil::formatForDisplay($tid))));
    }

    $pubtype = Clip_Util::getPubType($tid);

    // build the output
    $output  = '<div class="z-menu"><span class="z-menuitem-title clip-breadcrumbs">';
    $output .= '<span class="clip-option">';
    $args = array('func' => 'pubtypeinfo', 'args' => array('tid' => $tid));
    $output .= '<a href="'.smarty_function_clip_url($args, $view).'">'.$view->__('Info').'</a>';
    $output .= '</span> | ';

    $func = FormUtil::getPassedValue('func', 'main');

    // pubtype form link
    $output .= '<span>';
    if ($func != 'pubtype') {
        $output .= DataUtil::formatForDisplayHTML('<a href="'.ModUtil::url('Clip', 'admin', 'pubtype', array('tid' => $tid)).'">'.$view->__('Edit').'</a>');
    } else {
        $output .= DataUtil::formatForDisplayHTML('<a href="#">'.$view->__('Edit').'</a>');
    }
    $output .= '</span> | ';

    // edit fields link
    $output .= '<span>';
    if ($func != 'pubfields') {
        $output .= DataUtil::formatForDisplayHTML('<a href="'.ModUtil::url('Clip', 'admin', 'pubfields', array('tid' => $tid)).'">'.$view->__('Fields').'</a>');
    } elseif (isset($params['field']) && $params['field']) {
        $output .= DataUtil::formatForDisplayHTML('<a href="'.ModUtil::url('Clip', 'admin', 'pubfields', array('tid' => $tid)).'#newpubfield">'.$view->__('Fields').'</a>');
    } else {
        $output .= DataUtil::formatForDisplayHTML('<a href="#newpubfield">'.$view->__('Fields').'</a>');
    }
    $output .= '</span> | ';

    // relations link
    $output .= '<span>';
    $output .= DataUtil::formatForDisplayHTML('<a href="'.ModUtil::url('Clip', 'admin', 'relations', array('tid' => $tid, 'withtid1' => $tid, 'op' => 'or', 'withtid2' => $tid)).'">'.$view->__('Relations').'</a>');

    // show code links
    $args = array('func' => 'generator', 'args' => array('tid' => $tid, 'code' => 'form'));
    if ($func == 'generator') {
        $output .= '<br />';
        $output .= '<span class="clip-option">'.DataUtil::formatForDisplay($view->__('Generate templates')).'</span><span class="clip-option">&raquo;</span>';
        $args['args']['code'] = 'form';
        $output .= '<span>'.($params['code'] == 'form'      ? '<a href="#">' : '<a href="'.smarty_function_clip_url($args, $view).'">') . $view->__('Input template').'</a></span> | ';
        $args['args']['code'] = 'list';
        $output .= '<span>'.($params['code'] == 'list'      ? '<a href="#">' : '<a href="'.smarty_function_clip_url($args, $view).'">') . $view->__('List template').'</a></span> | ';
        $args['args']['code'] = 'display';
        $output .= '<span>'.($params['code'] == 'display'   ? '<a href="#">' : '<a href="'.smarty_function_clip_url($args, $view).'">') . $view->__('Display template').'</a></span> | ';
        $args['args']['code'] = 'blocklist';
        $output .= '<span>'.($params['code'] == 'blocklist' ? '<a href="#">' : '<a href="'.smarty_function_clip_url($args, $view).'">') . $view->__('List block').'</a></span> | ';
        $args['args']['code'] = 'blockpub';
        $output .= '<span>'.($params['code'] == 'blockpub'  ? '<a href="#">' : '<a href="'.smarty_function_clip_url($args, $view).'">') . $view->__('Pub block').'</a></span>';
    } else {
        $output .= '</span> | ';
        $output .= '<span><a href="'.smarty_function_clip_url($args, $view).'">'.$view->__('Generate templates').'</a></span>';
    }

    $output .= '</span></div>';

    return $output;
}
