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
    public static function pubdisplay($tid, $public=true)
    {
        $dom = ZLanguage::getModuleDomain('PageMaster');

        $tables = DBUtil::getTables();
        // initial pubdata is the table definition
        $pubdata = $tables["pagemaster_pubdata{$tid}_column"];

        // add the display fields
        $displayfields = array(
            'core_title' => '',
            'core_uniqueid' => '',
            'core_tid' => $tid,
            'core_pid' => '',
            'core_author' => '',
            'core_creator' => false,
            'core_approvalstate' => ''
        );

        $pubdata = array_merge($displayfields, $pubdata);

        $pubdata['__WORKFLOW__'] = array();

        // build the display code
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
            $rowcode = array(
                'full'  => '',
                'label' => '',
                'body'  => ''
            );

            // check if field is to handle special
            if (isset($pubfields[$key])) {
                // $key is $field.name
                $field = $pubfields[$key];

                $rowcode['label'] = '{gt text=\''.$field['title'].'\'}:';

                // process the postRead and getPluginOutput
                $plugin = PageMaster_Util::getPlugin($field['fieldplugin']);

                if (method_exists($plugin, 'postRead')) {
                    $pubdata[$key] = $plugin->postRead('', $key);
                } else {
                    $pubdata[$key] = '';
                }

                if (method_exists($plugin, 'getPluginOutput')) {
                    $plugincode = $plugin->getPluginOutput($field);
                    $rowcode = array_merge($rowcode, (array)$plugincode);
                }
            }

            // if the row is not defined yet
            if (empty($rowcode['full'])) {
                // fill the label if empty
                if (empty($rowcode['label'])) {
                    $rowcode['label'] = $key.':';
                }

                // fill the body if empty
                if (empty($rowcode['body'])) {
                    // filter some core fields
                    switch ($key) {
                        // title
                        case 'core_title':
                            $rowcode['full'] = '    <h3'.($public ? ' class="z-center"' : '').'>{gt text=$pubdata.'.$key.'}</h3>';
                            break;

                        // reads
                        case 'core_hitcount':
                            $rowcode['body'] = '{gt text=\'%s read\' plural=\'%s reads\' count=$pubdata.'.$key.' tag1=$pubdata.'.$key.'}';
                            break;

                        // language
                        case 'core_language':
                            $rowcode['full'] = "\n".
                                '    <div class="z-formrow">'."\n".
                                '        <span class="z-label">'.$rowcode['label'].'</span>'."\n".
                                '            {if !empty($pubdata.'.$key.')}'."\n".
                                '                <span class="z-formnote">{$pubdata.'.$key.'|getlanguagename}<span>'."\n".
                                '            {else}'."\n".
                                '                <span class="z-formnote">{gt text=\''.no__('Available for all languages').'\'}<span>'."\n".
                                '            {/if}'."\n".
                                '        </span>'."\n".
                                '    </div>'."\n";
                            break;

                        // flags
                        case 'core_creator':
                        case 'core_online':
                        case 'core_indepot':
                        case 'core_showinmenu':
                        case 'core_showinlist':
                            $rowcode['body'] = '{$pubdata.'.$key.'|yesno}';
                            break;

                        // user ids
                        case 'core_author':
                        case 'cr_uid':
                        case 'lu_uid':
                            $rowcode['body'] = "\n".
                                '                {$pubdata.'.$key.'|userprofilelink}'."\n".
                                '                <span class="z-sub">[{$pubdata.'.$key.'|safehtml}]</span>'."\n".
                                '            ';
                            break;

                        // dates
                        case 'core_publishdate':
                        case 'core_expiredate':
                        case 'cr_date':
                        case 'lu_date':
                            $rowcode['body'] = '{$pubdata.'.$key.'|dateformat:\'datetimelong\'}';
                            break;

                        default:
                            if (is_array($pubfield)) {
                                // generic arrays
                                $rowcode['body'] = '<pre>{pmarray array=$pubdata.'.$key.'}</pre>';

                            } elseif (is_bool($pubfield)) {
                                // generic booleans
                                $rowcode['body'] = '{$pubdata.'.$key.'|yesno}';

                            } else {
                                // generic strings
                                $rowcode['body'] = '{$pubdata.'.$key.'|safetext}';
                            }
                    }
                }

                // build the final row if not filled
                if (empty($rowcode['full'])) {
                    $rowcode['full'] =
                        '    {if $pubdata.'.$key.' neq \'\'}'."\n".
                        '        <div class="z-formrow">'."\n".
                        '            <span class="z-label">'.$rowcode['label'].'</span>'."\n".
                        '            <span class="z-formnote">'.$rowcode['body'].'<span>'."\n".
                        '        </div>'."\n".
                        '    {/if}';
                }
            }

            // add the snippet to the final template
            $template_code .= "\n".$rowcode['full']."\n";
        }

        // Add the Hooks support for display
        $template_code .= '</div>'."\n".
                "\n".
                '{modurl modname=\'PageMaster\' func=\'display\' tid=$pubdata.core_tid pid=$pubdata.core_pid assign=\'returnurl\'}'."\n".
                '{modcallhooks hookobject=\'item\' hookaction=\'display\' hookid=$pubdata.core_uniqueid module=\'PageMaster\' returnurl=$returnurl}'.
                "\n";

        // if the template is a public output
        if ($public) {
            // add the row cycles
            $template_code = str_replace('z-formrow', 'z-formrow {cycle values=\'z-odd,z-even\'}', $template_code);
        }

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
