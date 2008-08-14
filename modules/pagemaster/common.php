<?php
/**
 * PageMaster
 *
 * @copyright (c) 2008, PageMaster Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package     Zikula_3rd_party_Modules
 * @subpackage  pagemaster
 */

function createOrderBy($orderby)
{
    $orderbylist = explode(',', $orderby);
    $orderby = '';
    foreach ($orderbylist as $key => $orderbysingle) {
        list ($col, $ascdesc) = explode(':', $orderbysingle);
        if ($key > 0)
            $orderby .= ', ';
        $orderby .= $col.' '.$ascdesc;
    }
    return trim($orderby);
}

function getNewFileReference()
{
    $chars    = "0123456789abcdefghijklmnopqrstuvwxyz";
    $charLen = strlen($chars);

    $id = '';

    for ($i = 0; $i < 30; ++ $i)
        $id .= $chars[mt_rand(0, $charLen -1)];

    return $id;
}

function getExtension($filename, $keepDot = false)
{
    if (!$filename) {
        return pn_exit('getExtension: filename is empty');
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

function generate_editpub_template_code($tid, $pubfields, $pubtype)
{
    $template_code = '
        <h1><!--[pnml name="' . $pubtype[title] . '"]--></h1>

        <!--[pnsecauthaction_block component="pagemaster::" instance="::" level=ACCESS_ADMIN]-->
            <!--[pnml name=_PAGEMASTER_GENERIC_EDITPUB"]--><br />
        <!--[/pnsecauthaction_block]-->

        <!--[pngetstatusmsg]-->

        <!--[pnml name="' . $pubtype[description] . '"]--><br />
        
        <!--[pnform enctype="multipart/form-data"]-->
        <!--[pnformvalidationsummary]-->
        <table>
        ';

    foreach ($pubfields as $pubfield) {
        $fieldplugin = explode('.', $pubfield['fieldplugin']);
        if ($pubfield['fieldmaxlength'] <> '') {
            $maxlength = ' maxLength="' . $pubfield['fieldmaxlength'] . '" ';
        } else {
            $maxlength = ' maxLength="255" '; //TODO Not a clean solution. MaxLength is not needed for ever plugin
        }

        if ($pubfield['description'] <> '') {
            $toolTip = ' toolTip="' . $pubfield['description'] . '" ';
        } else {
            $toolTip = '';
        }
        if ($fieldplugin[1] == 'pmformtextinput') {
            $linecol = ' rows="20" cols="70" ';
        } else {
            $linecol = '';
        }
        $template_code .= "\n".'<tr>
                                    <td><!--[pnformlabel for="' . $pubfield[name] . '" text="' . $pubfield[title] . '" ]-->:</td>
                                    <td><!--[' . $fieldplugin[1] . ' id="' . $pubfield[name] . '" ' . $maxlength . $linecol . $toolTip . ' mandatory="' . $pubfield[ismandatory] . '"]--></td>
                                </tr>'."\n";
    }

    $template_code .= "\n".'<tr>
                                <td><!--[pnformlabel for="core_publishdate" text="_PAGEMASTER_PUBLISHDATE" ]-->:</td>
                                <td><!--[pmformdateinput id="core_publishdate" includeTime="1" ]--></td>
                            </tr>'."\n";
    $template_code .= "\n".'<tr>
                                <td><!--[pnformlabel for="core_expiredate" text="_PAGEMASTER_EXPIREDATE" ]-->:</td>
                                <td><!--[pmformdateinput id="core_expiredate" includeTime="1"  ]--></td>
                            </tr>'."\n";
    $template_code .= "\n".'<tr>
                                <td><!--[pnformlabel for="core_language" text="_LANGUAGE" ]-->:</td>
                                <td><!--[pnformlanguageselector id="core_language" mandatory="1" ]--></td>
                            </tr>'."\n";

    $template_code .= "\n".'<tr>
                                <td><!--[pnformlabel for="core_showinlist" text="_PAGEMASTER_SHOWINLIST" ]-->:</td>
                                <td><!--[pmformcheckboxinput id="core_showinlist" ]--></td>
                            </tr>'."\n";

    $template_code .= '
                </table>
                <br/>

                <!--[foreach item=action from=$actions]-->
                    <!--[pnformbutton commandName=$action text=$action]-->
                <!--[/foreach]-->
                <!--[/pnform]-->
                ';

    return $template_code;
}

function generate_viewpub_template_code($tid, $pubdata, $pubtype, $pubfields)
{
    $template_code = '<!--[pndebug]-->
                
                <!--[hitcount pid=$core_pid tid=$core_tid]-->
                <h1><!--[pnml name="' . $pubtype['title'] . '"]--></h1>
                
                <!--[pngetstatusmsg]-->
                <!--[pnsecauthaction_block component="pagemaster::" instance="::" level=ACCESS_ADMIN]-->
                    <!--[pnml name=_PAGEMASTER_GENERIC_VIEWPUB"]--><br />
                <!--[/pnsecauthaction_block]-->

                <!--[pnml name="' . $pubtype['description'] . '"]--><br />

                ';

    // build an index of the plugin names in $pubfields
    $index = array();
    foreach($pubfields as $key => $field) {
        $index[$field['name']] = $key ;
    }

    foreach ($pubdata as $key => $pubfield) {
        $template_code_add = '';
        $template_code_fielddesc = '';

        // check if field is to handle special
        if (isset($index[$key])) {
            $field = $pubfields[$index[$key]];

            $template_code_fielddesc = '<!--[pnml name='.$field['name'].']-->: ';
            if ($field['fieldplugin'] == 'function.pmformimageinput.php') {
                $template_code_add = '<!--[if $'.$field['name'].'.url neq "" ]-->'."\n".$template_code_fielddesc.'<!--[$' . $field['name'] . '.orig_name ]--><br/>'."\n";
                $template_code_add .= '<img src="<!--[$' . $field['name'] . '.thumbnailUrl ]-->" /><br/>'."\n";
                $template_code_add .= '<img src="<!--[$' . $field['name'] . '.url ]-->" /><br/>'."\n".'<!--[/if]-->'."\n\n";
            } elseif ($field['fieldplugin'] == 'function.pmformlistinput.php') {
                $template_code_add = '<!--[if $'.$field['name'].'.fullTitle neq "" ]-->'."\n".$template_code_fielddesc.'<!--[$' . $field['name'] . '.fullTitle ]--><br/>'."\n".'<!--[/if]-->'."\n\n";
            } elseif ($field['fieldplugin'] == 'function.pmformpubinput.php') {
                $template_code_add = '<!--[if $'.$key.' neq "" ]-->'."\n".$template_code_fielddesc.'<!--[pnmodapifunc modname="pagemaster" checkPerm="true" handlePluginFields="true" getApprovalState="true" func="getPub" tid='.$field['typedata'].' pid=$'.$key.' assign="'.$key.'_publication"]-->'."\n".'<!--[/if]-->'."\n\n";
            }
        }

        // if it was no special field handle it normal
        if ($template_code_add == '') {
            if ($template_code_fielddesc == '') {
                $template_code_fielddesc = $key.': ';
            }
            if (is_array($pubfield)) {
                foreach ($pubfield as $a => $b) {
                    $template_code_add = '<!--[$' . $key . '.' . $a . ']--><br/>'."\n\n";
                }
            } else {
                $template_code_add = '<!--[if $'.$key.' neq "" ]-->'."\n".$template_code_fielddesc.'<!--[$' . $key . '|pnvarprephtmldisplay]--><br/>'."\n".'<!--[/if]-->'."\n\n";
            }
        }
        $template_code = $template_code . $template_code_add;
    }
    return $template_code;
}

function pagemasterGetPluginsOptionList()
{
    $dir = 'modules/pagemaster/pntemplates/plugins';
    $plugins = array ();
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
            if (substr($file, 0, 15) == "function.pmform") {
                $plugin = pagemasterGetPlugin($file);
                $plugins[] = array (
                    'plugin' => $plugin,
                    'file' => $file
                );

            }
        }
        closedir($dh);
    }
    return $plugins;
}

function pagemasterGetWorkflowsOptionList()
{
    $dir = 'modules/pagemaster/workflows';
    $plugins = array ();
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
            if (substr($file, -4, 4) == ".xml") {
                $plugins[] = array (
                    'text' => $file,
                    'value' => $file
                );
            }
        }
        closedir($dh);
    }
    return $plugins;
}

function pagemasterGetPlugin($file)
{
    static $plugins = array ();
    if (empty ($plugins[$file])) {
        $pluginType = pagemasterGetPluginTypeFromFilename($file);
        pagemasterloadPluginType($pluginType);
        $plugins[$file] = new $pluginType;
    }
    return $plugins[$file];
}

function pagemasterGetPluginTypeFromFilename($filename)
{
    $i = strpos($filename, '.', 9);
    if ($i === false)
    return false;
    return substr($filename, 9, $i -9);
}

function pagemasterloadPluginType($pluginType)
{
    static $loadedPlugins = array ();
    if (empty ($loadedPlugins[$pluginType])) {
        $filename = "modules/pagemaster/pntemplates/plugins/function.$pluginType.php";
        require_once $filename;
        $loadedPlugins[$pluginType] = 1;
    }
}

function handlePluginFields($publist, $pubfields)
{
    foreach ($pubfields as $field) {
        $plugin = pagemasterGetPlugin($field['fieldplugin']);
        
        if (method_exists($plugin, 'postRead')) {
            foreach ($publist as $key => $pub) {
                if ($pub[$field['name']] <> '' and isset($pub[$field['name']]))
                    $publist[$key][$field['name']] = $plugin->postRead($pub[$field['name']], $field);
            }
        }
    }
    return $publist;
}

function getTidFromTablename($tablename)
{
    while (is_numeric(substr($tablename, -1))) {
        $tid = substr($tablename, -1) . $tid;
        $tablename = substr($tablename, 0, strlen($tablename) - 1);
    }
    return $tid;
}

function handlePluginOrderBy($orderby, $pubfields, $tbl_alias)
{
    if ($orderby <> '') {
        $orderby_arr = explode(',',$orderby);

        foreach ($orderby_arr as $orderby_field) {
            list($orderby_col, $orderby_dir) = explode (' ', trim($orderby_field));
            $plugin_name = '';
            $field_name  = '';
            foreach ($pubfields as $key => $field) {
                if (strtolower($field['name']) == strtolower($orderby_col)) {
                    $plugin_name = $field['fieldplugin'];
                    $field_name  = $field['name'];
                    break;
                }
            }

            if ($plugin_name <> '') {
                $plugin =  pagemasterGetPlugin($plugin_name);
                if (method_exists($plugin, 'orderBy')) {
                    $orderby_col = $plugin->orderBy($field_name);
                } else {
                    $orderby_col = $tbl_alias.$orderby_col;
                }
            } else {
                $orderby_col = $tbl_alias.$orderby_col;
            }
            $orderby_new .= $orderby_col .' '.$orderby_dir.',';
        }
        $orderby = substr($orderby_new,0,-1);
    }
    return $orderby;
}

function getTitleField($pubfields)
{
    foreach ($pubfields as $field) {
        if ($field['istitle'] == 1)
            $core_title = $field['name'];
    }
    return $core_title;
}
