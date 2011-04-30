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
 * Generates HTML vor Category Browsing
 * Additional parameters will be added to the category URL
 *
 * @param $args['tid']               Publication type ID
 * @param $args['field']             Fieldname of the pubfield which contains category
 * @param $args['tpl']               Optional filename of template
 * @param $args['count']             Optional count available pubs in this category
 * @param $args['multiselect']       Are more selection in one browser allowed (makes only sense for multilist fields)
 * @param $args['globalmultiselect'] Are more then one selections in all available browsers allowed
 * @param $args['togglediv']         This div will be toggled, if at least one entry is selected (if you wanna hidde cats as pulldownmenus)
 * @param $args['cache']             Enable smarty cache
 * @param $args['cache_count']       Enable count cache (apc is required)
 * @param $args['assign']            Optional

 * @return html of category tree
 */
function smarty_function_clip_category_browser($params, &$view)
{
    $dom = ZLanguage::getModuleDomain('Clip');

    $tid   = $params['tid'];
    $field = $params['field'];

    if (!$tid) {
        return LogUtil::registerError(__f('Error! Missing argument [%s].', 'tid', $dom));
    }

    if (!$field) {
        return LogUtil::registerError(__f('Error! Missing argument [%s].', 'field', $dom));
    }

    // get the plugin parametes
    $count             = isset($params['count']) ? $params['count'] : false;
    $togglediv         = isset($params['togglediv']) ? $params['togglediv'] : false;

    $template          = isset($params['tpl']) ? $params['tpl'] : 'clip_category_browser.tpl';
    $assign            = isset($params['assign']) ? $params['assign'] : null;
    $operator          = isset($params['operator']) ? $params['operator'] : 'sub';
    $includenulllink   = isset($params['includenulllink']) ? $params['includenulllink'] : true;
    $multiselect       = isset($params['multiselect']) ? $params['multiselect'] : false;
    $globalmultiselect = isset($params['globalmultiselect']) ? $params['globalmultiselect'] : false;
    $cache             = isset($params['cache']) ? $params['cache'] : false;
    $cache_count       = isset($params['cache_count']) ? $params['cache_count'] : true;

    // left any additional parameters
    if (isset($params['field'])) { unset($params['field']); }
    if (isset($params['count'])) { unset($params['count']); }
    if (isset($params['togglediv'])) { unset($params['togglediv']); }
    if (isset($params['tpl'])) { unset($params['tpl']); }
    if (isset($params['assign'])) { unset($params['assign']); }
    if (isset($params['operator'])) { unset($params['operator']); }
    if (isset($params['includenulllink'])) { unset($params['includenulllink']); }
    if (isset($params['multiselect'])) { unset($params['multiselect']); }
    if (isset($params['globalmultiselect'])) { unset($params['globalmultiselect']); }
    if (isset($params['cache'])) { unset($params['cache']); }
    if (isset($params['cache_count'])) { unset($params['cache_count']); }

    // category browser processing
    $filter     = FormUtil::getPassedValue('filter');
    $filter_arr = explode(',', $filter);
    $lang       = ZLanguage::getLanguageCode();

    $cacheid = $tid.'-'.$field;
    $render  = Zikula_View::getInstance('Clip', $cache, $cacheid);

    if ($cache) {
        if ($render->is_cached($template,$cacheid)) {
            return $render->fetch($template,$cacheid);
        }
    }

    $pubfields = Clip_Util::getPubFields($tid);
    $plugin = Clip_Util::getPlugin($pubfields[$field]['fieldplugin']);
    $id = $plugin->getRootCategoryID($pubfields[$field]['typedata']);

    $cats = CategoryUtil::getSubCategories($id);

    if (!$cats) {
        return LogUtil::registerError(__f('Error! No such category found for the ID passed [%s].', $id, $dom));
    }

    if ($count) {
        if (function_exists('apc_fetch') && $cache_count) {
            $count_arr_old = $count_arr = apc_fetch('clip_cat_browser_count_'.$tid);
        }
    }

    $one_selected = false;
    $keys = array_keys($cats);

    $catIDs = array();
    foreach ($keys as $k) {
        $catIDs[] = $cats[$k]['id'];
    }

    $basedepth = -1;
    foreach ($keys as $k) {
        $cats[$k]['selected'] = 0;

        $old_filter = '';
        $filter_act = $field.':'.$operator.':'.$cats[$k]['id'];

        $depth = StringUtil::countInstances($cats[$k]['path'], '/');
        if (strlen($cats[$k]['path']) > 1) {
            $depth++;
        }
        if ($basedepth == -1) {
            $basedepth = $depth;
        }
        $depth = $depth - $basedepth;

        // check if this cat is filtered
        foreach ($filter_arr as $fv)
        {
            if ($fv == $filter_act) {
                $cats[$k]['selected'] = 1;
                $one_selected = true;
            } else {
                if ($multiselect) {
                    $old_filter .= ','.$fv;
                } else {
                    // just add if from another browser
                    if ($globalmultiselect) {
                        list($fx, $subx, $idx) = explode(':', $fv);

                        if (!in_array($idx, $catIDs)) {
                            $old_filter .= ','.$fv;
                        }
                    }
                }
            }
        }

        // delete the ,
        $old_filter = substr($old_filter, 1);

        if ($cats[$k]['selected'] == 1) {
            $new_filter = $old_filter;
        } else {
            if (!empty($old_filter)) {
                $new_filter = $old_filter.','.$filter_act;
            } else {
                $new_filter = $filter_act;
            }
        }

        $args = $params;
        if ($new_filter == '') {
            $url = ModUtil::url('Clip', 'user', 'view', $args);
        } else {
            $args['filter'] = $new_filter;
            $url = ModUtil::url('Clip', 'user', 'view', $args);
        }

        if ($count) {
            if (isset($count_arr[$filter_act])) {
                $cats[$k]['count'] = $count_arr[$filter_act];
            } else {
                $pubarr = ModUtil::apiFunc('Clip', 'user', 'getall',
                                       array('tid'                => $tid,
                                             'countmode'          => 'just',
                                             'filter'             => !empty($filter_act) ? $filter_act : '()',
                                             'checkPerm'          => false,
                                             'handlePluginFields' => false));

                $cats[$k]['count'] = $count_arr[$filter_act] = $pubarr['pubcount'];
            }
        }

        $cats[$k]['depth']     = $depth;
        $cats[$k]['url']       = $url;
        $cats[$k]['fullTitle'] = isset($cats[$k]['display_name'][$lang]) ? $cats[$k]['display_name'][$lang] : $cats[$k]['name'];
    }

    if ($includenulllink) {
        $args = $params;
        $args['filter'] = $field.':null';
        $nullcat = array(
            -1 => array(
                'fullTitle' => __('Uncategorized', $dom),
                'url'       => ModUtil::url('Clip', 'user', 'view', $args),
                'depth'     => 0
            )
        );
        $cats = array_merge($nullcat, $cats);
    }

    // TODO Remove this in 1.3 to use DBUtil cache
    if (function_exists('apc_store') && $count && $cache_count && $count_arr_old <> $count_arr) {
        apc_store('clip_cat_browser_count_'.$tid, $count_arr, 3600);
    }

    $render->assign('cats', $cats);

    // assign the plugin options
    $options = array(
        'tid'       => $tid,
        'field'     => $field,
        'count'     => $count,
        'togglediv' => $togglediv,
        'selected'  => $one_selected
    );
    $render->assign('options', $options);

    $html = $render->fetch($template, $cacheid);

    if ($assign) {
        $view->assign($assign, $html);
    } else {
        return $html;
    }
}
