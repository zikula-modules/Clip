<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://github.com/zikula-modules/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage View_Plugins
 */
/**
 * Plugin to display the main editor's panel list.
 *
 * Available parameters:
 *  - data (array) Grouptypes array with its pubypes, accessible to the current user.
 *
 * @param array       $params All parameters passed to this plugin from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return mixed False on failure, HTML output of the grouptypes and pubtypes list.
 */
function smarty_function_clip_editorpanel($params, Zikula_View $view)
{
    if (!isset($params['data'])) {
        $view->trigger_error($view->__f('Error! in %1$s: the %2$s parameter must be specified.', array('clip_editorpanel', 'data')));
        return false;
    }
    // get the localized name
    $lang = ZLanguage::getLanguageCode();
    $sysl = System::getVar('language_i18n');
    foreach ($params['data'] as $k => &$group) {
        // name
        if (is_array($group['name'])) {
            if (isset($group['name'][$lang])) {
                $group['name'] = $group['name'][$lang];
            } elseif ($sysl != $lang && isset($group['name'][$sysl])) {
                $group['name'] = $group['name'][$sysl];
            } else {
                $group['name'] = current($group['name']);
            }
        }
        if (!$group['name']) {
            $group['name'] = __f('Group %s', $group['gid'], $dom);
        }
        $group['name'] = DataUtil::formatForDisplay($group['name']);
    }
    // initialize the output
    $output = '<ul class="clip-editorlist">' . '
';
    // control vars initial state
    $level = 1;
    $last = array();
    foreach ($params['data'] as $k => &$group) {
        // checks for level transitions
        // a higher node
        if ($level > $group['level']) {
            for ($i = $level; $i >= $group['level']; $i--) {
                // adds the pubtype items at the final of the groups
                foreach ($last[$i]['pubtypes'] as $pubtype) {
                    _smarty_function_clip_editorpanel_addpubtype($output, $pubtype, $i + 2);
                }
                if (!empty($last[$i]['pubtypes']) || isset($last[$i]['haschilds'])) {
                    $output .= str_repeat('  ', $i + 1) . '</ul>' . '
';
                }
                $output .= str_repeat('  ', $i) . '</li>' . '
';
            }
        } elseif ($level < $group['level']) {
            // adds the sub-list if not already added because
            if (empty($last[$level]['pubtypes'])) {
                $output .= str_repeat('  ', $group['level']) . '<ul id="clip-s' . $last[$level]['gid'] . '">' . '
';
            }
            // marks the last item
            $last[$level]['haschilds'] = true;
        } elseif ($k != 0) {
            // process from the second group onwards
            if ($last[$level]['pubtypes'] || isset($last[$level]['haschilds'])) {
                // display the last group pubtypes
                foreach ($last[$level]['pubtypes'] as $pubtype) {
                    _smarty_function_clip_editorpanel_addpubtype($output, $pubtype, $level + 2);
                }
                $output .= str_repeat('  ', $group['level']) . '  </ul>' . '
';
            }
            // close the previously opened item
            $output .= str_repeat('  ', $group['level']) . '</li>' . '
';
        }
        // adds the group item
        $output .= str_repeat('  ', $group['level']) . '<li class="clip-grouptype">' . '
';
        $output .= str_repeat('  ', $group['level']) . '  <strong id="clip-g' . $group['gid'] . '">' . '
';
        $output .= str_repeat('  ', $group['level']) . '    ' . DataUtil::formatForDisplay($group['name']) . '
';
        $output .= str_repeat('  ', $group['level']) . '  </strong>' . '
';
        // open a sub-list for the child pubtypes
        if (!empty($group['pubtypes'])) {
            $output .= str_repeat('  ', $group['level']) . '  <ul id="clip-s' . $group['gid'] . '">' . '
';
        }
        // update the control vars
        $level = $group['level'];
        $last[$level] = $group;
    }
    // close any group left open
    for ($i = $level; $i >= 1; $i--) {
        // adds the pubtype items at the final of the groups
        foreach ($last[$i]['pubtypes'] as $pubtype) {
            _smarty_function_clip_editorpanel_addpubtype($output, $pubtype, $i + 2);
        }
        if ($last[$i]['pubtypes'] || isset($last[$i]['haschilds'])) {
            $output .= str_repeat('  ', $i + 1) . '</ul>' . '
';
        }
        $output .= str_repeat('  ', $i) . '</li>' . '
';
    }
    $output .= '</ul>';
    return $output;
}
function _smarty_function_clip_editorpanel_addpubtype(&$output, $pubtype, $level)
{
    $output .= str_repeat('  ', $level) . '<li class="clip-pubtype">' . '
';
    $output .= str_repeat('  ', $level) . '  <a href="' . ModUtil::url('Clip', 'editor', 'list', array('tid' => $pubtype['tid'])) . '">' . DataUtil::formatForDisplay($pubtype['title']) . '</a>' . '
';
    if (!empty($pubtype['description'])) {
        $output .= str_repeat('  ', $level) . '  <span>' . DataUtil::formatForDisplay($pubtype['description']) . '</span>' . '
';
    }
    $output .= str_repeat('  ', $level) . '</li>' . '
';
}