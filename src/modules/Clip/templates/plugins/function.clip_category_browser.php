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
 * Generates HTML for pubtype's Category Browsing.
 *
 * Additional parameters will be added to the category URL.
 *
 * Available parameters:
 *  - tid               (integer) Publication type ID.
 *  - field             (string) Fieldname of the pubfield which contains category.
 *  - tpl               (string) Optional filename of template (default: clip_category_browser.tpl).
 *  - count             (bool) Optional count available pubs in this category.
 *  - multiselect       (bool) Are more selection in one browser allowed (makes only sense for multilist fields).
 *  - globalmultiselect (bool) Are more then one selections in all available browsers allowed.
 *  - togglediv         (bool) This div will be toggled, if at least one entry is selected (if you wanna hidde cats as pulldownmenus).
 *  - cache             (bool) Enable render cache (if not already enabled).
 *  - assign            (string) Optional variable name to assign the output to.
 *
 * Examples:
 *
 *  <samp>{clip_category_browser tid=1 field='category'}</samp>
 *  <samp>{clip_category_browser tid=2 field='list' tpl='pubtype2folder/browser.tpl' togglediv=1 cache=1}</samp>
 *
 * @param array       $params All parameters passed to this plugin from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return mixed False on failure, or the HTML output.
 */
function smarty_function_clip_category_browser($params, Zikula_View &$view)
{
    if (!isset($params['field']) || !$params['field']) {
        $view->trigger_error($view->__f('Error! in %1$s: the %2$s parameter must be specified.', array('clip_category_browser', 'field')));
        return false;
    }

    $tid   = isset($params['tid']) ? $params['tid'] : $view->getTplVar('pubtype')->tid;
    $field = $params['field'];

    // get the plugin parametes
    $count             = isset($params['count']) ? $params['count'] : false;
    $togglediv         = isset($params['togglediv']) ? $params['togglediv'] : false;

    $assign            = isset($params['assign']) ? $params['assign'] : null;
    $operator          = isset($params['operator']) ? $params['operator'] : 'sub';
    $includenulllink   = isset($params['includenulllink']) ? $params['includenulllink'] : true;
    $multiselect       = isset($params['multiselect']) ? $params['multiselect'] : false;
    $globalmultiselect = isset($params['globalmultiselect']) ? $params['globalmultiselect'] : false;
    $cache             = false;
    $wascaching        = Zikula_View::CACHE_DISABLED;

    if (!$view->getCaching() && isset($params['cache']) && in_array((int)$params['cache'], array(0, 1, 2))) {
        $cache = (int)$params['cache'];
    } elseif ($view->getCaching()) {
        $wascaching = $view->getCaching();
        $view->setCaching(Zikula_View::CACHE_DISABLED);
    }

    if (isset($params['tpl']) && $params['tpl'] && $view->template_exists($params['tpl'])) {
        $template = $params['tpl'];
    } else {
        $template = 'browser_category.tpl';
    }

    // left any additional parameters
    unset($params['field']);
    unset($params['count']);
    unset($params['togglediv']);
    unset($params['tpl']);
    unset($params['assign']);
    unset($params['operator']);
    unset($params['includenulllink']);
    unset($params['multiselect']);
    unset($params['globalmultiselect']);
    unset($params['cache']);

    // category browser processing
    $filter     = FormUtil::getPassedValue('filter');
    $filter_arr = explode(',', $filter);
    $lang       = ZLanguage::getLanguageCode();

    // use cache only if the main view is not cached
    if ($cache) {
        $cache_id = "browser_category/$tid/$field/".$operator.'_count'.(int)$count.'_null'.(int)$includenulllink;
        $view->setCaching(1);

        if ($view->is_cached($template, $cache_id)) {
            $html = $view->fetch($template, $cache_id);
        }
    }

    if (!isset($html)) {
        $pubfield = Clip_Util::getPubFields($tid, $field);
        $plugin = Clip_Util_Plugins::get($pubfield['fieldplugin']);
        $id = $plugin->getRootCategoryID($pubfield['typedata']);

        $cats = CategoryUtil::getSubCategories($id);

        if (!$cats) {
            return LogUtil::registerError($view->__f('Error! No such category found for the ID passed [%s].', $id));
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
            $filter_act = $field.'^'.$operator.'^'.$cats[$k]['id'];

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
                            list($fx, $subx, $idx) = explode('^', $fv);

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
                $url = Clip_Util::url($tid, 'list', $args);
            } else {
                $args['filter'] = $new_filter;
                $url = Clip_Util::url($tid, 'list', $args);
            }

            if ($count) {
                if (isset($count_arr[$filter_act])) {
                    $cats[$k]['count'] = $count_arr[$filter_act];
                } else {
                    $pubarr = ModUtil::apiFunc('Clip', 'user', 'getall',
                                           array('tid'           => $tid,
                                                 'countmode'     => 'just',
                                                 'filter'        => !empty($filter_act) ? $filter_act : '()',
                                                 'checkperm'     => false,
                                                 'handleplugins' => false));

                    $cats[$k]['count'] = $count_arr[$filter_act] = $pubarr['pubcount'];
                }
            }

            $cats[$k]['depth']     = $depth;
            $cats[$k]['url']       = $url;
            $cats[$k]['fullTitle'] = isset($cats[$k]['display_name'][$lang]) ? $cats[$k]['display_name'][$lang] : $cats[$k]['name'];
            $cats[$k]['fullDesc']  = isset($cats[$k]['display_desc'][$lang]) ? $cats[$k]['display_desc'][$lang] : '';
        }

        if ($includenulllink) {
            $args = $params;
            $args['filter'] = $field.'^null';
            $nullcat = array(
                -1 => array(
                    'fullTitle' => $view->__('Uncategorized'),
                    'url'       => Clip_Util::url($tid, 'list', $args),
                    'depth'     => 0,
                    'selected'  => 0
                )
            );
            $cats = array_merge($nullcat, $cats);
        }

        $view->assign('cats', $cats);

        // assign the plugin options
        $options = array(
            'tid'       => $tid,
            'urltitle'  => Clip_Util::getPubType($tid, 'urltitle'),
            'field'     => $field,
            'count'     => $count,
            'togglediv' => $togglediv,
            'selected'  => $one_selected
        );
        $view->assign('options', $options);

        $html = $view->fetch($template, $cache ? $cache_id : null);
    }

    $view->setCaching($wascaching);

    if ($assign) {
        $view->assign($assign, $html);
    } else {
        return $html;
    }
}
