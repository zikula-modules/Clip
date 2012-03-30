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
 *  - tid (integer) Publication type ID (optional).
 *
 * Example:
 *
 *  <samp>{clip_adminmenu}</samp>
 *
 * @param array       $params All parameters passed to this plugin from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return mixed False on failure, HTML output otherwise.
 */
function smarty_function_clip_adminmenu($params, Zikula_View &$view)
{
    include_once('modules/Clip/templates/plugins/function.clip_url.php');

    $pubtype = $view->getTplVar('pubtype');

    $tid = isset($params['tid']) ? (int)$params['tid'] : ($pubtype ? $pubtype['tid'] : $view->getTplVar('tid'));

    if (!$tid || !Clip_Util::validateTid($tid)) {
        return;
    }

    // build the output
    $args = array('func' => 'pubtypeinfo', 'args' => array('tid' => $tid));
    $func = FormUtil::getPassedValue('func', 'main');

    $output  = '<div id="clip-adminmenu" class="z-menu"><span class="z-menuitem-title">';
    $output .= '<span>';
    if ($func != 'pubtypeinfo') {
        $output .= '<a href="'.smarty_function_clip_url($args, $view).'">'.$view->__('Info').'</a>';
    } else {
        $output .= $view->__('Info');
    }
    $output .= '</span> | ';

    // pubtype form link
    $args['func'] = 'pubtype';

    $output .= '<span>';
    if ($func != 'pubtype') {
        $output .= '<a href="'.smarty_function_clip_url($args, $view).'">'.$view->__('Edit').'</a>';
    } else {
        $output .= $view->__('Edit');
    }
    $output .= '</span> | ';

    // edit fields link
    $args['func'] = 'pubfields';

    $output .= '<span>';
    if ($func != 'pubfields') {
        $output .= '<a href="'.smarty_function_clip_url($args, $view).'">'.$view->__('Fields').'</a>';
    } elseif (isset($params['field']) && $params['field']) {
        $args['fragment'] = 'newpubfield';
        $output .= '<a href="'.smarty_function_clip_url($args, $view).'">'.$view->__('Fields').'</a>';
        unset($args['fragment']);
    } else {
        $output .= $view->__('Fields');
    }
    $output .= '</span> | ';

    // relations link
    $args['func'] = 'relations';
    $args['args'] = array('tid' => $tid, 'withtid1' => $tid, 'op' => 'or', 'withtid2' => $tid);

    $output .= '<span>';
    if ($func != 'relations') {
        $output .= '<a href="'.smarty_function_clip_url($args, $view).'">'.$view->__('Relations').'</a>';
    } else {
        $output .= $view->__('Relations');
    }
    $output .= '</span>';

    // show code links
    $args['func'] = 'generator';
    $args['args'] = array('tid' => $tid, 'code' => 'edit');

    if ($func == 'generator') {
        $output .= '<br />';
        $output .= '<span>'.$view->__('Generate templates').'</span> <span>&raquo;</span> ';

        $links = array();

        $args['args']['code'] = 'main';
        $links[] = $params['code'] == 'main'      ? $view->__('Main')       : '<a class="tooltips" title="'.$view->__('Publication type main template').'" href="'.smarty_function_clip_url($args, $view).'">'.$view->__('Main').'</a>';

        $args['args']['code'] = 'list';
        $links[] = $params['code'] == 'list'      ? $view->__('List')       : '<a class="tooltips" title="'.$view->__('Publications list template').'" href="'.smarty_function_clip_url($args, $view).'">'.$view->__('List').'</a>';

        $args['args']['code'] = 'display';
        $links[] = $params['code'] == 'display'   ? $view->__('Display')    : '<a class="tooltips" title="'.$view->__('Publication display template').'" href="'.smarty_function_clip_url($args, $view).'">'.$view->__('Display').'</a>';

        $args['args']['code'] = 'edit';
        $links[] = $params['code'] == 'edit'      ? $view->__('Form')       : '<a class="tooltips" title="'.$view->__('Publication input form template').'" href="'.smarty_function_clip_url($args, $view).'">'.$view->__('Form').'</a>';

        $args['args']['code'] = 'blocklist';
        $links[] = $params['code'] == 'blocklist' ? $view->__('List block') : '<a class="tooltips" title="'.$view->__('List block template').'" href="'.smarty_function_clip_url($args, $view).'">'.$view->__('List block').'</a>';

        $args['args']['code'] = 'blockpub';
        $links[] = $params['code'] == 'blockpub'  ? $view->__('Pub block')  : '<a class="tooltips" title="'.$view->__('Publication block template').'" href="'.smarty_function_clip_url($args, $view).'">'.$view->__('Pub block').'</a>';

        $output .= '<span>'.implode('</span> | <span>', $links).'</span>';
    } else {
        $output .= ' | ';
        $output .= '<span><a href="'.smarty_function_clip_url($args, $view).'">'.$view->__('Generate templates').'</a></span>';
    }

    $output .= '</span></div>';

    return $output;
}
