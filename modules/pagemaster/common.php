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
 * Code generation functions
 */
function PMgen_viewpub_tplcode($tid, $pubdata)
{
    $dom = ZLanguage::getModuleDomain('pagemaster');

    $template_code = "\n".
                     '<!--[hitcount pid=$core_pid tid=$core_tid]-->'."\n".
                     "\n".
                     '<h1><!--[gt text=$pubtype.title]--></h1>'."\n".
                     "\n".
                     '<!--[include file=\'pagemaster_generic_navbar.htm\' section=\'pubview\']-->'."\n".
                     "\n".
                     '<!--[if $pubtype.description neq \'\']-->'."\n".
                     '    <div class="pm-pubdesc"><!--[gt text=$pubtype.description]--></div>'."\n".
                     '<!--[/if]-->'."\n".
                     "\n".
                     '<div class="z-form pm-pubdetails">';

    $pubfields = PMgetPubFields($tid);

    foreach ($pubdata as $key => $pubfield)
    {
        $template_code_add       = '';
        $template_code_fielddesc = '';
        $snippet_body            = '';

        // check if field is to handle special
        if (isset($pubfields[$key])) {
            $field = $pubfields[$key];

            $template_code_fielddesc = '<!--[gt text=\''.$field['title'].'\']-->:';

            // handle some special plugins
            // FIXME move this to each plugin?
            switch ($field['fieldplugin'])
            {
                // text plugin
                case 'pmformtextinput':
                    $snippet_body = '<!--[$'.$key.'|pnvarprephtmldisplay|pnmodcallhooks:\'pagemaster\']-->';
                    break;

                // image plugin
                case 'pmformimageinput':
                    $template_code_add = 
                     '    <!--[if $'.$field['name'].'.url neq \'\']-->'."\n".
                     '        <div class="z-formrow">'."\n".
                     '            <span class="z-label">'.$template_code_fielddesc.'</span>'."\n".
                     '            <span class="z-formnote">'."\n".
                     '                <!--[$'.$field['name'].'.orig_name]--><br />'."\n".
                     '                <img src="<!--[$'.$field['name'].'.thumbnailUrl]-->" title="'.no__('Thumbnail', $dom).'" alt="'.no__('Thumbnail', $dom).'" /><br />'."\n".
                     '                <img src="<!--[$'.$field['name'].'.url]-->" title="'.no__('Image', $dom).'" alt="'.no__('Image', $dom).'" />'."\n".
                     '            </span>'."\n".
                     '        </div>'."\n".
                     '    <!--[/if]-->';
                    break;

                // list input
                case 'pmformlistinput':
                    $template_code_add = 
                     '    <!--[if $'.$field['name'].'.fullTitle neq \'\']-->'."\n".
                     '        <div class="z-formrow">'."\n".
                     '            <span class="z-label">'.$template_code_fielddesc.'</span>'."\n".
                     '            <span class="z-formnote"><!--[$'.$key.'.fullTitle]--></span>'."\n".
                     '        </div>'."\n".
                     '    <!--[/if]-->';
                    break;

                // multilist input
                case 'pmformmultilistinput':
                    $template_code_add = 
                     '    <!--[if $'.$field['name'].' neq \'\']-->'."\n".
                     '        <div class="z-formrow">'."\n".
                     '            <span class="z-label">'.$template_code_fielddesc.'</span>'."\n".
                     '            <span class="z-formnote">'."\n".
                     '                <ul>'."\n".
                     '                    <!--[foreach from=$'.$key.' item=\'item\']-->'."\n".
                     '                        <li><!--[$item.fullTitle]--></li>'."\n".
                     '                    <!--[/foreach]-->'."\n".
                     '                </ul>'."\n".
                     '            </span>'."\n".
                     '        </div>'."\n".
                     '    <!--[/if]-->';
                    break;

                // publication input
                case 'pmformpubinput':
                    $plugin = PMgetPlugin('pmformpubinput');
                    $plugin->parseConfig($field['typedata']);
                    $template_code_add = 
                    '    <!--[if $'.$key.' neq \'\']-->'."\n".
                    '        <div class="z-formrow">'."\n".
                    '            <span class="z-label">'.$template_code_fielddesc.'</span>'."\n".
                    '            <span class="z-formnote">'."\n".
                    '                <pre><!--[pmarray array=$'.$key.']--></pre>'."\n".
                    '                <!--[*pnmodapifunc modname=\'pagemaster\' func=\'getPub\' tid=\''.$plugin->config['tid'].'\' pid=$'.$key.' assign=\''.$key.'_pub\' checkPerm=true handlePluginFields=true getApprovalState=true*]-->'."\n".
                    '            </span>'."\n".
                    '        </div>'."\n".
                    '    <!--[/if]-->';
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
                        '                <!--[$'.$key.'|userprofilelink]-->'."\n".
                        '                <span class="z-sub">[<!--[$'.$key.'|pnvarprephtmldisplay]-->]</span>'."\n".
                        '            ';
    
                // flags
                } elseif (in_array($key, array('core_online', 'core_indepot', 'core_showinmenu', 'core_showinlist'))) {
                    $snippet_body = '<!--[$'.$key.'|yesno]-->';
    
                // generic arrays
                } elseif (is_array($pubfield)) {
                    $snippet_body = '<pre><!--[pmarray array=$'.$key.']--></pre>';
    
                // generic strings
                } else {
                    $snippet_body = '<!--[$'.$key.'|pnvarprephtmldisplay]-->';
                }
            }

            // build the final snippet
            $template_code_add = 
                    '    <!--[if $'.$key.' neq \'\']-->'."\n".
                    '        <div class="z-formrow">'."\n".
                    '            <span class="z-label">'.$template_code_fielddesc.'</span>'."\n".
                    '            <span class="z-formnote">'.$snippet_body.'</span>'."\n".
                    '        </div>'."\n".
                    '    <!--[/if]-->';
        }

        // add the snippet to the final template 
        $template_code .= "\n".$template_code_add."\n";
    }

    // Add the Hooks support for viewpub
    $template_code .= '</div>'."\n".
                      "\n".
                      '<!--[pnmodurl modname=\'pagemaster\' func=\'viewpub\' tid=$core_tid pid=$core_pid assign=\'returnurl\']-->'."\n".
                      '<!--[pnmodcallhooks hookobject=\'item\' hookaction=\'display\' hookid=$core_uniqueid module=\'pagemaster\' returnurl=$returnurl]-->'.
                      "\n";
    
    return $template_code;
}

function PMgen_editpub_tplcode($tid)
{
    $title_newpub  = no__('New publication');
    $title_editpub = no__('Edit publication');

    $template_code = "\n".
                     '<h1><!--[gt text=$pubtype.title]--></h1>'."\n".
                     "\n".
                     '<!--[include file=\'pagemaster_generic_navbar.htm\' section=\'pubedit\']-->'."\n".
                     "\n".
                     '<!--[if $pubtype.description neq \'\']-->'."\n".
                     '    <div class="pm-pubdesc"><!--[gt text=$pubtype.description]--></div>'."\n".
                     '<!--[/if]-->'."\n".
                     "\n".
                     '<!--[pnform cssClass=\'z-form pm-pubedit\' enctype=\'multipart/form-data\']-->'."\n".
                     '    <div>'."\n".
                     '        <!--[pnformvalidationsummary]-->'."\n".
                     '        <fieldset>'."\n".
                     '            <legend>'."\n".
                     '                <!--[if isset($id)]-->'."\n".
                     '                    <!--[gt text=\''.$title_editpub.'\']-->'."\n".
                     '                <!--[else]-->'."\n".
                     '                    <!--[gt text=\''.$title_newpub.'\']-->'."\n".
                     '                <!--[/if]-->'."\n".
                     '            </legend>'."\n";

    $pubfields = PMgetPubFields($tid);

    foreach (array_keys($pubfields) as $k) {
        // get the plugin pnform name of the plugin filename
        $pmformname = explode('.', $pubfields[$k]['fieldplugin']);
        $pmformname = $pmformname[1];

        if (!empty($pubfields[$k]['fieldmaxlength'])) {
            $maxlength = " maxLength='{$pubfields[$k]['fieldmaxlength']}'";
        } elseif($pmformname == 'pmformtextinput') {
            $maxlength = " maxLength='65535'";
        } else {
            $maxlength = ''; //" maxLength='255'"; //TODO Not a clean solution. MaxLength is not needed for ever plugin
        }

        $toolTip = !empty($pubfields[$k]['description']) ? str_replace("'", "\'", $pubfields[$k]['description']) : '';

        // specific plugins
        $linecol = ($pmformname == 'pmformtextinput') ? " rows='20' cols='70'" : '';

        // scape simple quotes where needed
        $pubfields[$k]['title'] = str_replace("'", "\'", $pubfields[$k]['title']); 

        $template_code .= "\n".
                     '            <div class="z-formrow">'."\n".
                     '                <!--[pnformlabel for=\''.$pubfields[$k]['name'].'\' _'.'_text=\''.$pubfields[$k]['title'].'\''.((bool)$pubfields[$k]['ismandatory'] ? ' mandatorysym=true' : '').']-->'."\n".
                     '                <!--[genericformplugin id=\''.$pubfields[$k]['name'].'\''.$linecol.$maxlength.']-->'."\n".
         ($toolTip ? '                <span class="z-formnote z-sub"><!--[gt text=\''.$toolTip.'\']--></span>'."\n" : '').
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
                     '            <legend><!--[gt text=\'Publication options\']--></legend>'."\n".
                     "\n".
                     '            <div class="z-formrow">'."\n".
                     '                <!--[pnformlabel for=\'core_language\' _'.'_text=\'' . $title_lang . '\']-->'."\n".
                     '                <!--[pnformlanguageselector id=\'core_language\' mandatory=\'0\']-->'."\n".
                     '            </div>'."\n".
                     "\n".
                     '            <div class="z-formrow">'."\n".
                     '                <!--[pnformlabel for=\'core_publishdate\' _'.'_text=\'' . $title_pdate . '\']-->'."\n".
                     '                <!--[pnformdateinput id=\'core_publishdate\' includeTime=\'1\']-->'."\n".
                     '            </div>'."\n".
                     "\n".
                     '            <div class="z-formrow">'."\n".
                     '                <!--[pnformlabel for=\'core_expiredate\' _'.'_text=\'' . $title_edate . '\']-->'."\n".
                     '                <!--[pnformdateinput id=\'core_expiredate\' includeTime=\'1\']-->'."\n".
                     '            </div>'."\n".
                     "\n".
                     '            <div class="z-formrow">'."\n".
                     '                <!--[pnformlabel for=\'core_showinlist\' _'.'_text=\'' . $title_inlist . '\']-->'."\n".
                     '                <!--[pnformcheckbox id=\'core_showinlist\' checked=\'checked\']-->'."\n".
                     '            </div>'."\n".
                     '        </fieldset>'."\n".
                     "\n".
                     '        <!--[if isset($id)]-->'."\n".
                     '            <!--[pnmodcallhooks hookobject=\'item\' hookaction=\'modify\' hookid="`$pubtype.tid`-`$core_pid`" module=\'pagemaster\']-->'."\n".
                     '        <!--[else]-->'."\n".
                     '            <!--[pnmodcallhooks hookobject=\'item\' hookaction=\'new\' module=\'pagemaster\']-->'."\n".
                     '        <!--[/if]-->'."\n".
                     "\n".
                     '        <div class="z-formbuttons">'."\n".
                     '            <!--[foreach item=\'action\' from=$actions]-->'."\n".
                     '                <!--[gt text=$action.title assign=\'actiontitle\']-->'."\n".
                     '                <!--[pnformbutton commandName=$action.id text=$actiontitle]-->'."\n".
                     '            <!--[/foreach]-->'."\n".
                     '        </div>'."\n".
                     '    </div>'."\n".
                     '<!--[/pnform]-->'."\n\n";

    return $template_code;
}

/**
 * Generic getters
 */
function PMgetPluginsOptionList()
{
    $classDirs = array();
    //Loader::LoadClass checks these dirs, strange
    $classDirs[] = 'config/classes/modules/pagemaster/classes/FormPlugins';
    $classDirs[] = 'modules/pagemaster/classes/FormPlugins';

    $plugins = array ();
    foreach ($classDirs as $classDir) {
        $files = FileUtil::getFiles($classDir, false, true, 'php', 'f');
        foreach ($files as $file) {
            if (substr($file, 0, 6) == 'pmform') {
                $pluginclass = substr($file, 0, -10);
                $plugin = PMgetPlugin($pluginclass);
                $plugins[] = array (
                    'plugin' => $plugin,
                    'class' => $pluginclass    
                );
            }
        }
    }

    uasort($plugins, '_PMsortPluginList');

    return $plugins;
}

function _PMsortPluginList($a, $b)
{
    return strcmp($a['plugin']->title, $b['plugin']->title);
}

function PMgetWorkflowsOptionList()
{
    if (!function_exists('PM_parse_dir')) {
        function PM_parse_dir($dir, &$plugins)
        {
            if (!is_dir($dir) || !is_readable($dir)) {
                return;
            }
            $files = FileUtil::getFiles($dir, false, true, 'xml', 'f');
            foreach ($files as $file) {
                $plugins[] = array (
                    'text'  => $file,
                    'value' => $file
                );
            }
        }
    }

    $plugins = array ();

    $dir = 'modules/pagemaster/workflows';
    PM_parse_dir($dir, $plugins);
    $dir = 'config/workflows/pagemaster';
    PM_parse_dir($dir, $plugins);

    return $plugins;
}

/**
 * Generic handlers
 */
function PMhandlePluginFields($publist, $pubfields, $islist=true)
{
    // TODO have to load pnForm, otherwise plugins can not be loaded...
    include_once('includes/pnForm.php');

    foreach ($pubfields as $fieldname => $field) {
        $pluginclass = $field['fieldplugin'];
        $plugin = PMgetPlugin($pluginclass);

        if (method_exists($plugin, 'postRead')) {
            if ($islist) {
                foreach (array_keys($publist) as $key) {
                    $publist[$key][$fieldname] = $plugin->postRead($publist[$key][$fieldname], $field);
                }
            } else {
                $publist[$fieldname] = $plugin->postRead($publist[$fieldname], $field);
            }
        }
    }

    return $publist;
}

function PMhandlePluginOrderBy($orderby, $pubfields, $tbl_alias)
{
    if (!empty($orderby)) {
        $orderby_arr = explode(',', $orderby);
        $orderby_new = '';

        foreach ($orderby_arr as $orderby_field) {
            list($orderby_col, $orderby_dir) = explode(' ', trim($orderby_field));
            $plugin_name = '';
            $field_name  = '';

            foreach ($pubfields as $fieldname => $field) {
                if (strtolower($fieldname) == strtolower($orderby_col)) {
                    $plugin_name = $field['fieldplugin'];
                    $field_name  = $field['name'];
                    break;
                }
            }
            if (!empty($plugin_name)) {
                $plugin = PMgetPlugin($plugin_name);
                if (method_exists($plugin, 'orderBy')) {
                    $orderby_col = $plugin->orderBy($field_name, $tbl_alias);
                } else {
                    $orderby_col = $tbl_alias.$orderby_col;
                }
            } else {
                $orderby_col = $orderby_col;
            }
            $orderby_new .= $orderby_col.' '.$orderby_dir.',';
        }
        $orderby = substr($orderby_new, 0, -1);
    }

    return $orderby;
}

/**
 * Generic utilities
 */
function PMgetTidFromTablename($tablename)
{
    $tid = '';
    while (is_numeric(substr($tablename, -1))) {
        $tid = substr($tablename, -1) . $tid;
        $tablename = substr($tablename, 0, strlen($tablename) - 1);
    }

    return (int)$tid;
}

function PMcreateOrderBy($orderby)
{
    $orderbylist = explode(',', $orderby);
    $orderby     = '';
    foreach ($orderbylist as $key => $value) {
        if ($key > 0) {
            $orderby .= ', ';
        }
        // $value = {col[:ascdesc]}
        $value    = explode(':', $value);
        $orderby .= $value[0].(isset($value[1]) ? ' '.$value[1] : '');
    }

    return trim($orderby);
}

function PMgetNewFileReference()
{
    $chars   = '0123456789abcdefghijklmnopqrstuvwxyz';
    $charLen = strlen($chars);

    $id = '';

    for ($i = 0; $i < 30; ++ $i) {
        $id .= $chars[mt_rand(0, $charLen-1)];
    }

    return $id;
}

function PMgetExtension($filename, $keepDot = false)
{
    if (!$filename) {
        return pn_exit('PMgetExtension pm: filename is empty');
    }

    $p = strrpos($filename, '.');

    if ($p !== false) {
        if ($keepDot) {
            return substr($filename, $p);
        } else {
            return substr($filename, $p +1);
        }
    }

    return '';
}

/**
 * Loop the pubfields array until get the title field
 *
 * @param   array  $pubfields
 * @return         name of the title field
 */
function PMgetTitleField($pubfields)
{
    $core_title = 'id';

    foreach (array_keys($pubfields) as $i) {
        if ($pubfields[$i]['istitle'] == 1) {
            $core_title = $pubfields[$i]['name'];
            break;
        }
    }

    return $core_title;
}

/**
 * Singletones
 */
function PMgetPlugin($pluginclass)
{
    static $plugin_arr;
    
    include_once('includes/pnForm.php');

    if (!isset($plugin_arr[$pluginclass])) {
        Loader::LoadClass($pluginclass, 'modules/pagemaster/classes/FormPlugins');
        $plugin_arr[$pluginclass] = new $pluginclass;
    }

    return $plugin_arr[$pluginclass];
}

function PMgetPubFields($tid, $orderBy = 'lineno')
{
    static $pubfields_arr;

    $tid = (int)$tid;
    if (!isset($pubfields_arr[$tid])) {
        $pubfields_arr[$tid] = DBUtil::selectObjectArray('pagemaster_pubfields', "pm_tid = '$tid'", $orderBy, -1, -1, 'name');
    }

    return $pubfields_arr[$tid];
}

function PMgetPubType($tid)
{
    static $pubtype_arr;

    if (!isset($pubtype_arr)) {
        $pubtype_arr = DBUtil::selectObjectArray('pagemaster_pubtypes', '', 'tid', -1, -1, 'tid');
    }

    return isset($pubtype_arr[(int)$tid]) ? $pubtype_arr[(int)$tid] : false;
}
