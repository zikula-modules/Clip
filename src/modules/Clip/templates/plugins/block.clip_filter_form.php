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
 * Block to define a filter form.
 *
 * Available parameters:
 *  - class: CSS class to add to the form.
 *  - any additional parameter is passed to the form manager object.
 *
 * Example:
 *
 *  <samp>{clip_filter_form}{clip_filter_plugin p='String' id='core_title'}{/clip_filter_form}</samp>
 *
 * @param array       $params  All parameters passed to this plugin from the template.
 * @param mixed       $content Content inside the block.
 * @param Zikula_View &$view   Reference to the {@link Zikula_View} object.
 *
 * @return mixed
 */
function smarty_block_clip_filter_form($params, $content, Zikula_View &$view)
{
    // only works inside list templates
    if ($view->getTplVar('func') != 'list') {
        return;
    }

    $type  = $view->getRequest()->getGet()->get('type', 'user');
    $class = 'clip-filter-form z-form'.((isset($params['class']) && $params['class']) ? ' '.$params['class'] : '');
    unset($params['class']);

    // process the filter object
    if (is_null($content)) {
        // initial call to the block
        PageUtil::addVar('javascript', 'prototype');
        // register the manager object
        $filter = new Clip_Filter_Form($params, $view);
        $view->register_object('clip_filter', $filter);
    } else {
        // second call, unregister the manager object
        $filter = $view->get_registered_object('clip_filter');
        $view->unregister_object('clip_filter');
    }

    // do not process an empty form
    if (!trim($content)) {
        return;
    }

    $dom    = ZLanguage::getModuleDomain('Clip');
    $action = System::getBaseUrl().System::getVar('entrypoint', 'index.php');

    $output = '<div class="'.$class.'">'."\n".
                   '<fieldset id="'.$filter->getId().'wrapper" style="display: none">'."\n".
                       //'<legend>'.__('Filter', $dom).'</legend>'."\n".
                       $content."\n".
                       '<form id="'.$filter->getId().'form" method="get" action="'.$action.'" style="display: inline">'."\n".
                           '<input type="hidden" name="module" value="Clip" />'."\n".
                           '<input type="hidden" name="type" value="'.$type.'" />'."\n".
                           '<input type="hidden" name="func" value="list" />'."\n".
                           '<input type="hidden" name="tid" value="'.$view->getTplVar('pubtype')->tid.'" />'."\n";

    foreach ($filter->getFilterNames() as $id => $filterName) {
        $output .= '<input type="hidden" id="'.$id.'" name="'.$filterName.'" value="" />'."\n";
    }

    $output .=             '<span class="z-nowrap z-buttons">'."\n".
                               '<input type="submit" value="'.__('Filter', $dom).'" class="z-bt-filter z-bt-small" />'."\n".
                           '</span>'."\n".
                       '</form>'."\n".
                   '</fieldset>'."\n".
               '</div>'."\n".
               $filter->getFormScript();

    return $output;
}
