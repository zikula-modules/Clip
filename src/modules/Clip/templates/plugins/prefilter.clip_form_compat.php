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
 * Clip compatibility plugin for older form templates.
 *
 * This will be removed on the 1.0 release.
 *
 * @param string      $source Precompiled source.
 * @param Zikula_View $view   Reference to Zikula_View instance.
 *
 * @return string Modified precompiled source.
 */
function smarty_prefilter_clip_form_compat($source, $view)
{
    $source = str_replace('clip_form_genericplugin id', 'clip_form_plugin field', $source);
    $source = str_replace('clip_form_block id', 'clip_form_block field', $source);
    $source = str_replace('clip_form_relation id', 'clip_form_relation field', $source);
 
    $source = str_replace(" group='pubdata'", '', $source);

    $source = str_replace("formlanguageselector id='core_language'", "clip_form_plugin field='core_language'", $source);
    $source = str_replace("formdateinput id='core_publishdate'", "clip_form_plugin field='core_publishdate'", $source);
    $source = str_replace("formdateinput id='core_expiredate'", "clip_form_plugin field='core_expiredate'", $source);
    $source = str_replace("formcheckbox id='core_visible'", "clip_form_plugin field='core_visible'", $source);
    $source = str_replace("formcheckbox id='core_locked'", "clip_form_plugin field='core_locked'", $source);

    $source = preg_replace("/formcheckbox id='.*?_(delete|thumbs)'/", 'formcheckbox id="`$fieldid`_$1"', $source);

    $source = str_replace('z-buttons z-formbuttons', 'z-buttons', $source);

    // return the modified source
    return $source;
}
