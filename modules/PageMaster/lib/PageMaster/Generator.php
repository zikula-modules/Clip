<?php
/**
 * PageMaster
 *
 * @copyright   (c) PageMaster Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package     Zikula_3rdParty_Modules
 * @subpackage  pagemaster
 */

/**
 * PageMaster Template Generator.
 */
class PageMaster_Generator
{
    public static function pubview($tid, $pubdata)
    {
        $dom = ZLanguage::getModuleDomain('PageMaster');

        $template_code = "\n".
                '{hitcount pid=$pubdata.core_pid tid=$pubdata.core_tid}'."\n".
                "\n".
                '<h2>{gt text=$pubtype.title}</h2>'."\n".
                "\n".
                '{include file=\'pagemaster_generic_navbar.tpl\' section=\'display\'}'."\n".
                "\n".
                '{if $pubtype.description neq \'\'}'."\n".
                '    <div class="pm-pubtype-desc">{gt text=$pubtype.description}</div>'."\n".
                '{/if}'."\n".
                "\n".
                '<div class="z-form pm-pub-details">';

        $pubfields = PageMaster_Util::getPubFields($tid);

        foreach ($pubdata as $key => $pubfield)
        {
            $template_code_add       = '';
            $template_code_fielddesc = '';
            $snippet_body            = '';

            // check if field is to handle special
            if (isset($pubfields[$key])) {
                $field = $pubfields[$key];

                $template_code_fielddesc = '{gt text=\''.$field['title'].'\'}:';

                // handle some special plugins
                // FIXME move this to each plugin?
                $pluginClassname = PageMaster_Util::processPluginClassname($field['fieldplugin']);
                switch ($pluginClassname)
                {
                    // text plugin
                    case 'PageMaster_Form_Plugin_Text':
                        $snippet_body = '{$pubdata.'.$key.'|safehtml|modcallhooks:\'PageMaster\'}';
                        break;

                    // image plugin
                    case 'PageMaster_Form_Plugin_Image':
                        $template_code_add =
                                '    {if $pubdata.'.$field['name'].'.url neq \'\'}'."\n".
                                '        <div class="z-formrow">'."\n".
                                '            <span class="z-label">'.$template_code_fielddesc.'</span>'."\n".
                                '            <span class="z-formnote">'."\n".
                                '                {$pubdata.'.$field['name'].'.orig_name}<br />'."\n".
                                '                <img src="{$pubdata.'.$field['name'].'.thumbnailUrl}" title="{gt text=\''.no__('Thumbnail', $dom).'\'}" alt="{gt text=\''.no__('Thumbnail', $dom).'\'}" /><br />'."\n".
                                '                <img src="{$pubdata.'.$field['name'].'.url}" title="{gt text=\''.no__('Image', $dom).'\'}" alt="{gt text=\''.no__('Image', $dom).'\'}" />'."\n".
                                '                <pre>{pmarray array=$pubdata.'.$key.'}</pre>'."\n".
                                '            <span>'."\n".
                                '        </div>'."\n".
                                '    {/if}';
                        break;

                    // list input
                    case 'PageMaster_Form_Plugin_List':
                        $template_code_add =
                                '    {if !empty($pubdata.'.$field['name'].')}'."\n".
                                '        <div class="z-formrow">'."\n".
                                '            <span class="z-label">'.$template_code_fielddesc.'</span>'."\n".
                                '            <span class="z-formnote">{$pubdata.'.$key.'.fullTitle}<span>'."\n".
                                '            <pre>{pmarray array=$pubdata.'.$key.'}</pre>'."\n".
                                '        </div>'."\n".
                                '    {/if}';
                        break;

                    // multilist input
                    case 'PageMaster_Form_Plugin_MultiList':
                        $template_code_add =
                                '    {if !empty($pubdata.'.$field['name'].')}'."\n".
                                '        <div class="z-formrow">'."\n".
                                '            <span class="z-label">'.$template_code_fielddesc.'</span>'."\n".
                                '            <span class="z-formnote">'."\n".
                                '                <ul>'."\n".
                                '                    {foreach from=$pubdata.'.$key.' item=\'item\'}'."\n".
                                '                        <li>{$item.fullTitle}</li>'."\n".
                                '                    {/foreach}'."\n".
                                '                </ul>'."\n".
                                '            <span>'."\n".
                                '        </div>'."\n".
                                '    {/if}';
                        break;

                    // publication input
                    case 'PageMaster_Form_Plugin_Pub':
                        $plugin = PageMaster_Util::getPlugin('PageMaster_Form_Plugin_Pub');
                        $plugin->parseConfig($field['typedata']);
                        $template_code_add =
                                '    {if !empty($pubdata.'.$key.')}'."\n".
                                '        <div class="z-formrow">'."\n".
                                '            <span class="z-label">'.$template_code_fielddesc.'</span>'."\n".
                                '            <span class="z-formnote">'."\n".
                                '                <pre>{pmarray array=$pubdata.'.$key.'}</pre>'."\n".
                                '                {*modapifunc modname=\'PageMaster\' func=\'get\' tid=\''.$plugin->config['tid'].'\' pid=$pubdata.'.$key.' assign=\''.$key.'_pub\' checkPerm=true handlePluginFields=true getApprovalState=true*}'."\n".
                                '            <span>'."\n".
                                '        </div>'."\n".
                                '    {/if}';
                        break;
                }
            }

            // if it was no special field handle it normal
            if (empty($template_code_add)) {
                if (empty($template_code_fielddesc)) {
                    $template_code_fielddesc = $key.':';
                }

                if (empty($snippet_body)) {
                    // filter some core fields (uids)
                    if (in_array($key, array('core_author', 'cr_uid', 'lu_uid'))) {
                        $snippet_body = "\n".
                                '                {$pubdata.'.$key.'|userprofilelink}'."\n".
                                '                <span class="z-sub">[{$pubdata.'.$key.'|safehtml}]</span>'."\n".
                                '            ';

                        // flags
                    } elseif (in_array($key, array('core_creator', 'core_online', 'core_indepot', 'core_showinmenu', 'core_showinlist'))) {
                        $snippet_body = '{$pubdata.'.$key.'|yesno}';

                        // generic arrays
                    } elseif (is_array($pubfield)) {
                        $snippet_body = '<pre>{pmarray array=$pubdata.'.$key.'}</pre>';

                        // generic strings
                    } else {
                        $snippet_body = '{$pubdata.'.$key.'|safehtml}';
                    }
                }

                // build the final snippet
                $template_code_add =
                        '    {if !empty($pubdata.'.$key.')}'."\n".
                        '        <div class="z-formrow">'."\n".
                        '            <span class="z-label">'.$template_code_fielddesc.'</span>'."\n".
                        '            <span class="z-formnote">'.$snippet_body.'<span>'."\n".
                        '        </div>'."\n".
                        '    {/if}';
            }

            // add the snippet to the final template
            $template_code .= "\n".$template_code_add."\n";
        }

        // Add the Hooks support for display
        $template_code .= '</div>'."\n".
                "\n".
                '{modurl modname=\'PageMaster\' func=\'display\' tid=$pubdata.core_tid pid=$pubdata.core_pid assign=\'returnurl\'}'."\n".
                '{modcallhooks hookobject=\'item\' hookaction=\'display\' hookid=$pubdata.core_uniqueid module=\'PageMaster\' returnurl=$returnurl}'.
                "\n";

        return $template_code;
    }

    public static function pubedit($tid)
    {
        $title_newpub  = no__('New publication');
        $title_editpub = no__('Edit publication');

        $template_code = "\n".
                '<h2>{gt text=$pubtype.title}</h2>'."\n".
                "\n".
                '{include file=\'pagemaster_generic_navbar.tpl\' section=\'form\'}'."\n".
                "\n".
                '{if $pubtype.description neq \'\'}'."\n".
                '    <div class="pm-pubtype-desc">{gt text=$pubtype.description}</div>'."\n".
                '{/if}'."\n".
                "\n".
                '{assign var=\'zformclass\' value="z-form pm-editform pm-editform-`$pubtype.tid` pm-editform-`$pubtype.tid`-`$pubtype.stepname`"}'."\n".
                "\n".
                '{form cssClass=$zformclass enctype=\'multipart/form-data\'}'."\n".
                '    <div>'."\n".
                '        {formvalidationsummary}'."\n".
                '        <fieldset>'."\n".
                '            <legend>'."\n".
                '                {if isset($id)}'."\n".
                '                    {gt text=\''.$title_editpub.'\'}'."\n".
                '                {else}'."\n".
                '                    {gt text=\''.$title_newpub.'\'}'."\n".
                '                {/if}'."\n".
                '            </legend>'."\n";

        $pubfields = PageMaster_Util::getPubFields($tid);

        foreach (array_keys($pubfields) as $k) {
            // get the formplugin name
            $formplugin = PageMaster_Util::processPluginClassname($pubfields[$k]['fieldplugin']);

            if (!empty($pubfields[$k]['fieldmaxlength'])) {
                $maxlength = " maxLength='{$pubfields[$k]['fieldmaxlength']}'";
            } elseif($formplugin == 'PageMaster_Form_Plugin_Text') {
                $maxlength = " maxLength='65535'";
            } else {
                $maxlength = ''; //" maxLength='255'"; //TODO Not a clean solution. MaxLength is not needed for ever plugin
            }

            $toolTip = !empty($pubfields[$k]['description']) ? str_replace("'", "\'", $pubfields[$k]['description']) : '';

            // specific plugins
            $linecol = ($formplugin == 'PageMaster_Form_Plugin_Text') ? " rows='15' cols='70'" : '';

            // scape simple quotes where needed
            $pubfields[$k]['title'] = str_replace("'", "\'", $pubfields[$k]['title']);

            $template_code .= "\n".
                    '            <div class="z-formrow">'."\n".
                    '                {formlabel for=\''.$pubfields[$k]['name'].'\' _'.'_text=\''.$pubfields[$k]['title'].'\''.((bool)$pubfields[$k]['ismandatory'] ? ' mandatorysym=true' : '').'}'."\n".
                    '                {genericformplugin id=\''.$pubfields[$k]['name'].'\''.$linecol.$maxlength.'}'."\n".
        ($toolTip ? '                <span class="z-formnote z-sub">{gt text=\''.$toolTip.'\'}</span>'."\n" : '').
                    '            </div>'."\n";
        }
        $title_lang   = no__('Language');
        $title_pdate  = no__('Publish date');
        $title_edate  = no__('Expire date');
        $title_inlist = no__('Show in list');
        $button_cancel = no__('Cancel');

        $template_code .=
                '        </fieldset>'."\n".
                "\n".
                '        <fieldset>'."\n".
                '            <legend>{gt text=\'Publication options\'}</legend>'."\n".
                "\n".
                '            <div class="z-formrow">'."\n".
                '                {formlabel for=\'core_language\' _'.'_text=\'' . $title_lang . '\'}'."\n".
                '                {formlanguageselector id=\'core_language\' mandatory=false}'."\n".
                '            </div>'."\n".
                "\n".
                '            <div class="z-formrow">'."\n".
                '                {formlabel for=\'core_publishdate\' _'.'_text=\'' . $title_pdate . '\'}'."\n".
                '                {formdateinput id=\'core_publishdate\' includeTime=true}'."\n".
                '            </div>'."\n".
                "\n".
                '            <div class="z-formrow">'."\n".
                '                {formlabel for=\'core_expiredate\' _'.'_text=\'' . $title_edate . '\'}'."\n".
                '                {formdateinput id=\'core_expiredate\' includeTime=true}'."\n".
                '            </div>'."\n".
                "\n".
                '            <div class="z-formrow">'."\n".
                '                {formlabel for=\'core_showinlist\' _'.'_text=\'' . $title_inlist . '\'}'."\n".
                '                {formcheckbox id=\'core_showinlist\' checked=\'checked\'}'."\n".
                '            </div>'."\n".
                '        </fieldset>'."\n".
                "\n".
                '        {if isset($id)}'."\n".
                '            {modcallhooks hookobject=\'item\' hookaction=\'modify\' hookid="`$pubtype.tid`-`$core_pid`" module=\'PageMaster\'}'."\n".
                '        {else}'."\n".
                '            {modcallhooks hookobject=\'item\' hookaction=\'new\' module=\'PageMaster\'}'."\n".
                '        {/if}'."\n".
                "\n".
                '        <div class="z-buttons z-formbuttons">'."\n".
                '            {foreach item=\'action\' from=$actions}'."\n".
                '                {gt text=$action.title assign=\'actiontitle\'}'."\n".
                '                {formbutton commandName=$action.id text=$actiontitle zparameters=$action.parameters.button|default:\'\'}'."\n".
                '            {/foreach}'."\n".
                '            {formbutton commandName=\'cancel\' __text=\'' . $button_cancel . '\' class=\'z-bt-cancel\'}'."\n".
                '        </div>'."\n".
                '    </div>'."\n".
                '{/form}'."\n\n";

        return $template_code;
    }
}
