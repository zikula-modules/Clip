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
    $args = array('func' => 'pubtypeinfo', 'args' => array('tid' => $tid));

    $output  = '<div id="clip-adminmenu" class="z-menu"><span class="z-menuitem-title">';
    $output .= '<span>';
    $output .= '<a href="'.smarty_function_clip_url($args, $view).'">'.$view->__('Info').'</a>';
    $output .= '</span> | ';

    $func = FormUtil::getPassedValue('func', 'main');

    // pubtype form link
    $args['func'] = 'pubtype';

    $output .= '<span>';
    if ($func != 'pubtype') {
        $output .= '<a href="'.smarty_function_clip_url($args, $view).'">'.$view->__('Edit').'</a>';
    } else {
        $output .= '<a href="#">'.$view->__('Edit').'</a>';
    }
    $output .= '</span> | ';

    // edit fields link
    $args['func'] = 'pubfields';

    $output .= '<span>';
    if ($func != 'pubfields') {
        $output .= '<a href="'.smarty_function_clip_url($args, $view).'">'.$view->__('Fields').'</a>';
    } elseif (isset($params['field']) && $params['field']) {
        $output .= '<a href="'.smarty_function_clip_url($args, $view).'#newpubfield">'.$view->__('Fields').'</a>';
    } else {
        $output .= '<a href="#newpubfield">'.$view->__('Fields').'</a>';
    }
    $output .= '</span> | ';

    // relations link
    $args['func'] = 'relations';
    $args['args'] = array('tid' => $tid, 'withtid1' => $tid, 'op' => 'or', 'withtid2' => $tid);

    $output .= '<span>';
    $output .= '<a href="'.smarty_function_clip_url($args, $view).'">'.$view->__('Relations').'</a>';

    // show code links
    $args['func'] = 'generator';
    $args['args'] = array('tid' => $tid, 'code' => 'form');

    if ($func == 'generator') {
        $output .= '<br />';
        $output .= '<span>'.$view->__('Generate templates').'</span><span>&raquo;</span>';

        $links = array();

        $args['args']['code'] = 'main';
        $links[] = $params['code'] == 'main'      ? $view->__('Index')      : '<a class="tooltips" title="'.$view->__('Publication type main template').'" href="'.smarty_function_clip_url($args, $view).'">'.$view->__('Index').'</a>';

        $args['args']['code'] = 'list';
        $links[] = $params['code'] == 'list'      ? $view->__('List')       : '<a class="tooltips" title="'.$view->__('Publications list template').'" href="'.smarty_function_clip_url($args, $view).'">'.$view->__('List').'</a>';

        $args['args']['code'] = 'display';
        $links[] = $params['code'] == 'display'   ? $view->__('Display')    : '<a class="tooltips" title="'.$view->__('Publication display template').'" href="'.smarty_function_clip_url($args, $view).'">'.$view->__('Display').'</a>';

        $args['args']['code'] = 'form';
        $links[] = $params['code'] == 'form'      ? $view->__('Form')       : '<a class="tooltips" title="'.$view->__('Publication input form template').'" href="'.smarty_function_clip_url($args, $view).'">'.$view->__('Form').'</a>';

        $args['args']['code'] = 'blocklist';
        $links[] = $params['code'] == 'blocklist' ? $view->__('List block') : '<a class="tooltips" title="'.$view->__('List block template').'" href="'.smarty_function_clip_url($args, $view).'">'.$view->__('List block').'</a>';

        $args['args']['code'] = 'blockpub';
        $links[] = $params['code'] == 'blockpub'  ? $view->__('Pub block')  : '<a class="tooltips" title="'.$view->__('Publication block template').'" href="'.smarty_function_clip_url($args, $view).'">'.$view->__('Pub block').'</a>';

        $output .= '<span>'.implode('</span> | <span>', $links).'</span>';
    } else {
        $output .= '</span> | ';
        $output .= '<span><a href="'.smarty_function_clip_url($args, $view).'">'.$view->__('Generate templates').'</a></span>';
    }

    $output .= '</span></div>';

    return $output;
}
