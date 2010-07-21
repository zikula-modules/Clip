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
 * PageMaster template generator
 */
class PageMaster_Generator
{
    public static function viewpub($tid, $pubdata)
    {
        $dom = ZLanguage::getModuleDomain('PageMaster');

        $template_code = "\n".
                '{hitcount pid=$core_pid tid=$core_tid}'."\n".
                "\n".
                '<h1>{gt text=$pubtype.title}</h1>'."\n".
                "\n".
                '{include file=\'pagemaster_generic_navbar.tpl\' section=\'pubview\'}'."\n".
                "\n".
                '{if $pubtype.description neq \'\'}'."\n".
                '    <div class="pm-pubdesc">{gt text=$pubtype.description}</div>'."\n".
                '{/if}'."\n".
                "\n".
                '<div class="z-form pm-pubdetails">';

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
                switch ($field['fieldplugin'])
                {
                    // text plugin
                    case 'pmformtextinput':
                        $snippet_body = '{$'.$key.'|safehtml|modcallhooks:\'PageMaster\'}';
                        break;

                    // image plugin
                    case 'pmformimageinput':
                        $template_code_add =
                                '    {if $'.$field['name'].'.url neq \'\'}'."\n".
                                '        <div class="z-formrow">'."\n".
                                '            <span class="z-label">'.$template_code_fielddesc.'</span>'."\n".
                                '            <span class="z-formnote">'."\n".
                                '                {$'.$field['name'].'.orig_name}<br />'."\n".
                                '                <img src="{$'.$field['name'].'.thumbnailUrl}" title="'.no__('Thumbnail', $dom).'" alt="'.no__('Thumbnail', $dom).'" /><br />'."\n".
                                '                <img src="{$'.$field['name'].'.url}" title="'.no__('Image', $dom).'" alt="'.no__('Image', $dom).'" />'."\n".
                                '                <pre>{pmarray array=$'.$key.'}</pre>'."\n".
                                '            <span>'."\n".
                                '        </div>'."\n".
                                '    {/if}';
                        break;

                    // list input
                    case 'pmformlistinput':
                        $template_code_add =
                                '    {if !empty($'.$field['name'].')}'."\n".
                                '        <div class="z-formrow">'."\n".
                                '            <span class="z-label">'.$template_code_fielddesc.'</span>'."\n".
                                '            <span class="z-formnote">{$'.$key.'.fullTitle}<span>'."\n".
                                '            <pre>{pmarray array=$'.$key.'}</pre>'."\n".
                                '        </div>'."\n".
                                '    {/if}';
                        break;

                    // multilist input
                    case 'pmformmultilistinput':
                        $template_code_add =
                                '    {if !empty($'.$field['name'].')}'."\n".
                                '        <div class="z-formrow">'."\n".
                                '            <span class="z-label">'.$template_code_fielddesc.'</span>'."\n".
                                '            <span class="z-formnote">'."\n".
                                '                <ul>'."\n".
                                '                    {foreach from=$'.$key.' item=\'item\'}'."\n".
                                '                        <li>{$item.fullTitle}</li>'."\n".
                                '                    {/foreach}'."\n".
                                '                </ul>'."\n".
                                '            <span>'."\n".
                                '        </div>'."\n".
                                '    {/if}';
                        break;

                    // publication input
                    case 'pmformpubinput':
                        $plugin = PageMaster_Util::getPlugin('PageMaster_Form_Plugin_Pub');
                        $plugin->parseConfig($field['typedata']);
                        $template_code_add =
                                '    {if !empty($'.$key.')}'."\n".
                                '        <div class="z-formrow">'."\n".
                                '            <span class="z-label">'.$template_code_fielddesc.'</span>'."\n".
                                '            <span class="z-formnote">'."\n".
                                '                <pre>{pmarray array=$'.$key.'}</pre>'."\n".
                                '                {*modapifunc modname=\'PageMaster\' func=\'getPub\' tid=\''.$plugin->config['tid'].'\' pid=$'.$key.' assign=\''.$key.'_pub\' checkPerm=true handlePluginFields=true getApprovalState=true*}'."\n".
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
                                '                {$'.$key.'|userprofilelink}'."\n".
                                '                <span class="z-sub">[{$'.$key.'|safehtml}]</span>'."\n".
                                '            ';

                        // flags
                    } elseif (in_array($key, array('core_online', 'core_indepot', 'core_showinmenu', 'core_showinlist'))) {
                        $snippet_body = '{$'.$key.'|yesno}';

                        // generic arrays
                    } elseif (is_array($pubfield)) {
                        $snippet_body = '<pre>{pmarray array=$'.$key.'}</pre>';

                        // generic strings
                    } else {
                        $snippet_body = '{$'.$key.'|safehtml}';
                    }
                }

                // build the final snippet
                $template_code_add =
                        '    {if !empty($'.$key.')}'."\n".
                        '        <div class="z-formrow">'."\n".
                        '            <span class="z-label">'.$template_code_fielddesc.'</span>'."\n".
                        '            <span class="z-formnote">'.$snippet_body.'<span>'."\n".
                        '        </div>'."\n".
                        '    {/if}';
            }

            // add the snippet to the final template
            $template_code .= "\n".$template_code_add."\n";
        }

        // Add the Hooks support for viewpub
        $template_code .= '</div>'."\n".
                "\n".
                '{modurl modname=\'PageMaster\' func=\'viewpub\' tid=$core_tid pid=$core_pid assign=\'returnurl\'}'."\n".
                '{modcallhooks hookobject=\'item\' hookaction=\'display\' hookid=$core_uniqueid module=\'PageMaster\' returnurl=$returnurl}'.
                "\n";

        return $template_code;
    }

    public static function editpub($tid)
    {
        $title_newpub  = no__('New publication');
        $title_editpub = no__('Edit publication');

        $template_code = "\n".
                '<h1>{gt text=$pubtype.title}</h1>'."\n".
                "\n".
                '{include file=\'pagemaster_generic_navbar.tpl\' section=\'pubedit\'}'."\n".
                "\n".
                '{if $pubtype.description neq \'\'}'."\n".
                '    <div class="pm-pubdesc">{gt text=$pubtype.description}</div>'."\n".
                '{/if}'."\n".
                "\n".
                '{form cssClass=\'z-form pm-pubedit\' enctype=\'multipart/form-data\'}'."\n".
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
            // get the plugin pnform name of the plugin filename
            $pmformname = $pubfields[$k]['fieldplugin'];

            if (!empty($pubfields[$k]['fieldmaxlength'])) {
                $maxlength = " maxLength='{$pubfields[$k]['fieldmaxlength']}'";
            } elseif($pmformname == 'pmformtextinput') {
                $maxlength = " maxLength='65535'";
            } else {
                $maxlength = ''; //" maxLength='255'"; //TODO Not a clean solution. MaxLength is not needed for ever plugin
            }

            $toolTip = !empty($pubfields[$k]['description']) ? str_replace("'", "\'", $pubfields[$k]['description']) : '';

            // specific plugins
            $linecol = ($pmformname == 'pmformtextinput') ? " rows='15' cols='70'" : '';

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

        $template_code .=
                '        </fieldset>'."\n".
                "\n".
                '        <fieldset>'."\n".
                '            <legend>{gt text=\'Publication options\'}</legend>'."\n".
                "\n".
                '            <div class="z-formrow">'."\n".
                '                {formlabel for=\'core_language\' _'.'_text=\'' . $title_lang . '\'}'."\n".
                '                {formlanguageselector id=\'core_language\' mandatory=\'0\'}'."\n".
                '            </div>'."\n".
                "\n".
                '            <div class="z-formrow">'."\n".
                '                {formlabel for=\'core_publishdate\' _'.'_text=\'' . $title_pdate . '\'}'."\n".
                '                {formdateinput id=\'core_publishdate\' includeTime=\'1\'}'."\n".
                '            </div>'."\n".
                "\n".
                '            <div class="z-formrow">'."\n".
                '                {formlabel for=\'core_expiredate\' _'.'_text=\'' . $title_edate . '\'}'."\n".
                '                {formdateinput id=\'core_expiredate\' includeTime=\'1\'}'."\n".
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
                '        <div class="z-formbuttons">'."\n".
                '            {foreach item=\'action\' from=$actions}'."\n".
                '                {gt text=$action.title assign=\'actiontitle\'}'."\n".
                '                {formbutton commandName=$action.id text=$actiontitle}'."\n".
                '            {/foreach}'."\n".
                '        </div>'."\n".
                '    </div>'."\n".
                '{/form}'."\n\n";

        return $template_code;
    }
}
