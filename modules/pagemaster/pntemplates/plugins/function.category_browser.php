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
 * Generates HTML vor Category Browsing
 * @author kundi
 * @param $args['tid'] tid
 * @param $args['field'] fieldname of the pubfield which contains category
 * @param $args['template'] optional filename of template
 * @param $args['count'] optional count available pubs in this category
 * @param $args['multiselect'] are more selection in one browser allowed (makes only sense for multilist fields)
 * @param $args['globalmultiselect'] are more then one selections in all available browsers allowed
 * @param $args['togglediv'] this div will be toggled, if at least one entry is selected (if you wanna hidde cats as pulldownmenus)
 * @param $args['cache'] enable cache
 * @param $args['assign'] optional

 * @return html of category tree
 */
function smarty_function_category_browser($params, &$smarty)
{
    $tid               = $params['tid'];
    $field             = $params['field'];
    $template          = isset($params['template']) ? $params['template'] : 'pagemaster_category_browser.htm';
    $count             = isset($params['count']) ? $params['count'] : false;
    $togglediv         = isset($params['togglediv']) ? $params['togglediv'] : false;
    $assign            = isset($params['assign']) ? $params['assign'] : null;
    $multiselect       = isset($params['multiselect']) ? $params['multiselect'] : false;
    $globalmultiselect = isset($params['globalmultiselect']) ? $params['globalmultiselect'] : false;
    $cache             = isset($params['cache']) ? $params['cache'] : false;

    $filter = FormUtil::getPassedValue('filter');
    $filter_arr = explode(',', $filter);
    $lang = pnUserGetLang();

    if (!$tid) {
        return 'Required parameter [tid] not provided in smarty_function_category_browser';
    }

    if (!$field) {
        return 'Required parameter [field] not provided in smarty_function_category_browser';
    }

    $cacheid = $tid . '-' . $field;
    $render  = pnRender::getInstance('pagemaster',$cache, $cacheid);
    
    if ($cache){
        if ( $render->is_cached($template,$cacheid)) {
            return $render->fetch($template,$cacheid);
        }
    }
    
    Loader::loadClass('CategoryUtil');

    $result = null;
    
    $pubfields = getPubFields($tid);
    $id = $pubfields[$field]['typedata'];
    
    $cats = CategoryUtil::getSubCategories($id);

    if ($cats) {
        if ($count) {
            // get it only once
            $pubtype = getPubType($tid);
        }
        $one_selected = false;

        foreach ($cats as $k => $v) {
            $path = $v['path'];
            $old_filter = '';
            $depth = StringUtil::countInstances($path, '/');
            $filter_act = $field.':sub:'.$v['id'];
            $v['selected'] = 0;
            if (strlen($path) > 1) {
                $depth++;
            }
            $depth = $depth-7;

            // check if this cat is filtered

            foreach($filter_arr as $fkey => $fv)
            {
                if ($fv == $filter_act) {
                    $v['selected'] = 1;
                    $one_selected = true;
                } else {
                    if ($multiselect) {
                        $old_filter = $old_filter.','.$fv;
                    } else {
                        // just add if from another browser
                        if ($globalmultiselect) {
                            list($fx, $subx, $idx) = explode(':', $fv);
                            $found = false;
                            foreach ($cats as $k2 => $v2) {
                                if ($v2['id'] == $idx)
                                    $found = true;
                            }
                            if (!$found) {
                                $old_filter = $old_filter.','.$fv;
                            }
                        }
                    }
                }
            }

            //delete the ,
            $old_filter = substr($old_filter, 1);

            if ($v['selected'] == 1) {
                $new_filter = $old_filter;
            } else {
                if (!empty($old_filter)) {
                    $new_filter = $old_filter.','.$filter_act;
                } else {
                    $new_filter = $filter_act;
                }
            }

            if ($new_filter == '') {
                $url = pnModURL('pagemaster', 'user', 'main',
                                array('tid'    => $tid));
            } else {
                $url = pnModURL('pagemaster', 'user', 'main',
                                array('tid'    => $tid,
                                      'filter' => $new_filter));
            }

            if ($count) {
                $pubarr = pnModAPIFunc('pagemaster', 'user', 'pubList',
                                       array('tid'                => $tid,
                                             'countmode'          => 'just',
                                             'filter'             => $filter_act,
                                             'checkPerm'          => false,
                                             'pubfields'          => $pubfields,
                                             'pubtype'            => $pubtype,
                                             'handlePluginFields' => false));
                $v['count'] = $pubarr['pubcount'];
            }

            $v['depth'] = $depth;
            $v['url'] = $url;
            $v['fullTitle'] = $v['display_name'][$lang];
            $cat_arr[] = $v;
        }
    } else {
        return "No category for id [$id] in smarty_function_category_browser";
    }

    $render->assign('cats', $cat_arr);

    $html = $render->fetch($template, $cacheid);
    if ($togglediv <> false and $one_selected) {
        $html .= '<script type=\'text/javascript\'>';
        //$html .= 'Effect.toggle(\''.$setup['field'].'\', \'blind\', {duration:0.0});';
        $html .= 'document.getElementById(\''.$togglediv.'\').style.display = \'block\';';
        $html .= '</script>';
    }

    if ($assign) {
        $smarty->assign($params['assign'], $html);
    } else {
        return $html;
    }
}
