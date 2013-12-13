<?php/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://github.com/zikula-modules/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage View_Plugins
 */
/**
 * Plugin to include a filter inside a list template.
 * If the filter template does not exists, auto generate one.
 *
 * Available parameters:
 *   - template: Filter template to use (filter_$template.tpl)
 *
 * Examples:
 *
 *  <samp>{clip_filter}</samp>
 *  <samp>{clip_filter template='mini'}</samp>
 *
 * @param array       $params All parameters passed to this plugin from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return string Filter form output.
 */
function smarty_function_clip_filter($params, Zikula_View &$view)
{
    // only works inside list templates
    if ($view->getTplVar('func') != 'list') {
        return;
    }
    $pubtype = $view->getTplVar('pubtype');
    $tpl = isset($params['template']) ? $params['template'] : '';
    $tpl = $pubtype->folder . '/filter' . ($tpl ? "_{$tpl}" : '') . '.tpl';
    if ($view->template_exists($tpl)) {
        $output = $view->fetch($tpl);
    } else {
        $code = Clip_Generator::listfilter($pubtype->tid);
        if (!$code) {
            // no filterable fields
            return;
        }
        // FIXME check if this works fine
        $caching = $view->getCaching();
        $view->setForceCompile(true)->setCaching(Zikula_View::CACHE_DISABLED)->assign('filter_generic_code', $code);
        $output = $view->fetch('var:filter_generic_code');
        $view->setForceCompile(false)->setCaching($caching);
    }
    return $output;
}