<?php
/**
 * Clip
 *
 * @copyright   Zikula Foundation 2009 - Zikula Application Framework
 * @link        http://www.zikula.org
 * @license     GNU/LGPLv3 (or at your option, any later version).
 * @package     Clip
 * @subpackage  View_Plugins
 */

/**
 * Clip alpha filter.
 *
 * Available parameters:
 *  - field       Name of the field to use in the filter (default: core_title).
 *  - forwardvars Comma- semicolon- or space-delimited list of POST and GET variables to forward in the pager links. If unset, all vars are forwarded.
 *  - addvars     Comma- semicolon- or space-delimited list of additional variable and value pairs to forward in the links. eg "foo=2,bar=4".
 *  - class       Class for the pager (default: z-pager z-pagerabc).
 *  - class_num   Class for the pager links (<a> tags).
 *  - class_numon Class for the active page.
 *  - separator   String to put between the letters, eg "|" makes " A | B | C | D | ...".
 *  - printempty  Print empty selection ('-').
 *  - names       String or array of names to select from (array or csv).
 *  - values      Optional parameter for the previous names (array or cvs).
 *  - modname     Module name for the links (optional).
 *  - type        Type of the function for the links (optional).
 *  - func        Function name for the links (optional).
 *
 * Example:
 *
 *  <samp>{clip_pagerabc class='abcpager' class_num='abclink' class_numon='abclink_on'}</samp>
 *
 *  Results on links like:
 *  index.php?module=Clip&func=list&tid=**&filter=core_author:eq:2&filter1=core_title:likefirst:**
 *  from the default list.
 *
 * @param array       $params All parameters passed to this plugin from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string HTML output.
 */
function smarty_function_clip_pagerabc($params, Zikula_View &$view)
{
    $pubtype = $view->getTplVar('pubtype');

    if (!isset($params['field'])) {
        $params['field'] = 'core_title';
    }

    if ($params['field'] == 'core_title') {
        $params['field'] = $pubtype->getTitleField();
    }

    if (!isset($params['varname'])) {
        $params['varname'] = 'filter';
    }

    if (!isset($params['separator'])) {
        $params['separator'] = '|';
    }

    if (!isset($params['printempty']) || !is_bool($params['printempty'])) {
        $params['printempty'] = true;
    }

    // set a default class
    if (!isset($params['class'])) {
        $params['class'] = 'z-pager z-pagerabc';
    }

    if (!isset($params['class_num'])) {
        $params['class_num'] = 'z-pagerabclink';
    }

    if (!isset($params['class_numon'])) {
        $params['class_numon'] = 'z-pagerselected';
    }


    $pager = array();

    if (!empty($params['names'])) {
        if (!is_array($params['names'])) {
            $pager['names'] = explode(';', $params['names']);
        }
        if (!empty($params['values'])) {
            if (!is_array($params['values'])) {
                $pager['values'] = explode(';', $params['values']);
            }
            if (count($pager['values']) != count($pager['names'])) {
                LogUtil::registerError($view->__('clip_pagerabc: Values length must be the same of the Names'));
                $pager['values'] = $pager['names'];
            }
        } else {
            $pager['values'] = $pager['names'];
        }
    } else {
        // predefined abc
        $alphabet = __('A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z');
        $pager['names'] = $pager['values'] = explode(',', $alphabet);
    }

    $pager['varname'] = $params['varname'];
    unset($params['varname']);
    unset($params['names']);
    unset($params['values']);


    $pager['module'] = isset($params['modname']) ? $params['modname'] : FormUtil::getPassedValue('module', null, 'GETPOST', FILTER_SANITIZE_STRING);
    $pager['func'] = isset($params['func']) ? $params['func'] : FormUtil::getPassedValue('func', 'main', 'GETPOST', FILTER_SANITIZE_STRING);
    $pager['type'] = isset($params['type']) ? $params['type'] : FormUtil::getPassedValue('type', 'user', 'GETPOST', FILTER_SANITIZE_STRING);

    $allVars = array_merge($_POST, $_GET);

    $pager['args'] = array();
    if (empty($pager['module'])) {
        $pager['module'] = System::getVar('startpage');
        $starttype = System::getVar('starttype');
        $pager['type'] = !empty($starttype) ? $starttype : 'user';
        $startfunc = System::getVar('startfunc');
        $pager['func'] = !empty($startfunc) ? $startfunc : 'main';

        $startargs = explode(',', System::getVar('startargs'));
        foreach ($startargs as $arg) {
            if (!empty($arg)) {
                $argument = explode('=', $arg);
                if ($argument[0] == $pager['varname']) {
                    $allVars[$argument[0]] = $argument[1];
                }
            }
        }
    }

    // If $forwardvars set, add only listed vars to query string, else add all POST and GET vars
    if (isset($params['forwardvars'])) {
        if (!is_array($params['forwardvars'])) {
            $params['forwardvars'] = preg_split('/[,;\s]/', $params['forwardvars'], -1, PREG_SPLIT_NO_EMPTY);
        }
        foreach ((array)$params['forwardvars'] as $key => $var) {
            if (!empty($var) && (!empty($allVars[$var]))) {
                $pager['args'][$var] = $allVars[$var];
            }
        }
    } else {
        $pager['args'] = array_merge($pager['args'], $allVars);
    }

    if (isset($params['additionalvars'])) {
        if (!is_array($params['additionalvars'])) {
            $params['additionalvars'] = preg_split('/[,;\s]/', $params['additionalvars'], -1, PREG_SPLIT_NO_EMPTY);
        }
        foreach ((array)$params['additionalvars'] as $var) {
            $additionalvar = preg_split('/=/', $var);
            if (!empty($var) && !empty($additionalvar[1])) {
                $pager['args'][$additionalvar[0]] = $additionalvar[1];
            }
        }
    }
    unset($pager['args']['module']);
    unset($pager['args']['func']);
    unset($pager['args']['type']);
    // disable all the present filters
    if (isset($pager['args']['filter'])) {
        unset($pager['args']['filter']);
    }
    $i = 1;
    while (true) {
        if (!isset($pager['args']['filter'.$i])) {
            break;
        }
        unset($pager['args']['filter'.$i]);
        $i++;
    }

    // begin to fill the output
    $output = '<div class="'.$params['class'].'">'."\n";

    $style = '';
    if ($params['printempty']) {
        if (!empty($params['class_num'])) {
            $style = 'class="'.$params['class_num'].'"';
        }
        $urltemp = DataUtil::formatForDisplay(ModUtil::url($pager['module'], $pager['type'], $pager['func'], $pager['args']));
        $output .= ' <a '.$style.' href="'.$urltemp.'"> -'."\n</a> ".$params['separator'];
    }

    $style = '';
    foreach (array_keys($pager['names']) as $i) {
        if (!empty($params['class_numon'])) {
            if (isset($allVars['filter']) && $allVars['filter'] == "{$params['field']}:likefirst:{$pager['values'][$i]}") {
                $style = ' class="'.$params['class_numon'].'"';
            } elseif (!empty($params['class_num'])) {
                $style = ' class="'.$params['class_num'].'"';
            } else {
                $style = '';
            }
        }
        $pager['args']['filter'] = "{$params['field']}:likefirst:{$pager['values'][$i]}";
        $urltemp = DataUtil::formatForDisplay(ModUtil::url($pager['module'], $pager['type'], $pager['func'], $pager['args']));
        if ($i > 0) {
            $output .= $params['separator'];
        }
        $output .= ' <a'.$style.' href="'.$urltemp.'">'.$pager['names'][$i]."</a> \n";
    }
    $output .= "</div>\n";

    return $output;
}
