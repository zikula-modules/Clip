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

function createOrderBy($orderby)
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

function getNewFileReference()
{
    $chars   = '0123456789abcdefghijklmnopqrstuvwxyz';
    $charLen = strlen($chars);

    $id = '';

    for ($i = 0; $i < 30; ++ $i) {
        $id .= $chars[mt_rand(0, $charLen-1)];
    }

    return $id;
}

function getExtension($filename, $keepDot = false)
{
    if (!$filename) {
        return pn_exit('getExtension pm: filename is empty');
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

function generate_editpub_template_code($tid, $pubfields, $pubtype, $hookAction='new')
{
    $template_code = '
                      <!--[insert name=\'getstatusmsg\']-->

                      <!--[pnsecauthaction_block component=\'pagemaster::\' instance=\'::\' level=ACCESS_ADMIN]-->
                          <div class="pn-warningmsg"><!--[pnml name=\'_PAGEMASTER_GENERIC_EDITPUB\' html=1]--></div>
                      <!--[/pnsecauthaction_block]-->

                      <h1><!--[pnml name=\''. $pubtype['title'] .'\']--></h1>

                      <p><!--[pnml name=\''. $pubtype['description'] .'\']--></p>

                      <!--[pnform enctype=\'multipart/form-data\']-->
                      <!--[pnformvalidationsummary]-->
                      <table>
                      ';

    $ak = array_keys($pubfields);
    foreach ($ak as $k) {
        // get the plugin pnform name of the plugin filename
        $pmformname = explode('.', $pubfields[$k]['fieldplugin']);
        $pmformname = $pmformname[1];

        if (!empty($pubfields[$k]['fieldmaxlength'])) {
            $maxlength = 'maxLength=\''.$pubfields[$k]['fieldmaxlength'].'\'';
        } elseif($pmformname == 'pmformtextinput') {
            $maxlength = 'maxLength=\'65535\' ';
        } else {
            $maxlength = 'maxLength=\'255\' '; //TODO Not a clean solution. MaxLength is not needed for ever plugin
        }

        if (!empty($pubfields[$k]['description'])) {
            $toolTip = 'toolTip=\''.$pubfields[$k]['description'].'\' ';
        } else {
            $toolTip = '';
        }

        // specific plugins
        if ($pmformname == 'pmformtextinput') {
            $linecol = 'rows=\'20\' cols=\'70\' ';
        } else {
            $linecol = '';
        }
        $template_code .= '
                            <tr>
                                <td><!--[pnformlabel for=\''.$pubfields[$k]['name'].'\' text=\''.$pubfields[$k]['title'].'\']-->:</td>
                                <td><!--['.$pmformname.' id=\''.$pubfields[$k]['name'].'\' '.$maxlength.$linecol.$toolTip.'mandatory=\''.$pubfields[$k]['ismandatory'].'\']--></td>
                            </tr>
                            ';
    }
    $template_code .= '
                            <tr>
                                <td><!--[pnformlabel for=\'core_publishdate\' text=\'_PAGEMASTER_PUBLISHDATE\']-->:</td>
                                <td><!--[pmformdateinput id=\'core_publishdate\' includeTime=\'1\']--></td>
                            </tr>

                            <tr>
                                <td><!--[pnformlabel for=\'core_expiredate\' text=\'_PAGEMASTER_EXPIREDATE\']-->:</td>
                                <td><!--[pmformdateinput id=\'core_expiredate\' includeTime=\'1\']--></td>
                            </tr>

                            <tr>
                                <td><!--[pnformlabel for=\'core_language\' text=\'_LANGUAGE\']-->:</td>
                                <td><!--[pnformlanguageselector id=\'core_language\' mandatory=\'0\']--></td>
                            </tr>

                            <tr>
                                <td><!--[pnformlabel for=\'core_showinlist\' text=\'_PAGEMASTER_SHOWINLIST\']-->:</td>
                                <td><!--[pmformcheckboxinput id=\'core_showinlist\' checked=\'checked\']--></td>
                            </tr>
                        </table>

                        <!--[pnmodcallhooks hookobject=\'item\' hookaction=\''.$hookAction.'\' hookid="`$core_tid`-`$core_pid`" module=\'pagemaster\']-->

                        <!--[foreach item=\'action\' from=$actions]-->
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
                <!--[insert name=\'getstatusmsg\']-->

                <!--[pnsecauthaction_block component=\'pagemaster::\' instance=\'::\' level=ACCESS_ADMIN]-->
                    <div class="pn-warningmsg"><!--[pnml name=\'_PAGEMASTER_GENERIC_VIEWPUB\' html=1]--></div>
                <!--[/pnsecauthaction_block]-->

                <h1><!--[pnml name=\'' . $pubtype['title'] . '\']--></h1>

                <p><!--[pnml name=\'' . $pubtype['description'] . '\']--></p>

                ';

    foreach ($pubdata as $key => $pubfield) {
        $template_code_add = '';
        $template_code_fielddesc = '';

        // check if field is to handle special
        if (isset($pubfields[$key])) {
            $field = $pubfields[$key];

            $template_code_fielddesc = '<!--[pnml name=\''.$field['title'].'\']-->: ';
            // image plugin
            if ($field['fieldplugin'] == 'function.pmformimageinput.php') {
                $template_code_add = '<!--[if $'.$field['name'].'.url neq \'\']-->'."\n".$template_code_fielddesc.'<!--[$'. $field['name'] .'.orig_name]--><br />
                                      <img src="<!--[$' . $field['name'] . '.thumbnailUrl]-->" /><br />
                                      <img src="<!--[$' . $field['name'] . '.url]-->" /><br />
                                      <!--[/if]-->'."\n\n";
            // list input
            } elseif ($field['fieldplugin'] == 'function.pmformlistinput.php') {
                $template_code_add = '<!--[if $'.$field['name'].'.fullTitle neq \'\']-->'."\n".$template_code_fielddesc.'<!--[$'. $field['name'] .'.fullTitle]--><br/>'."\n".'<!--[/if]-->'."\n\n";

            // multilist input
            } elseif ($field['fieldplugin'] == 'function.pmformmultilistinput.php') {
                $template_code_add = '<!--[if $'.$field['name'].' neq null]-->'."\n".$template_code_fielddesc.'<br />
                <ul>
                    <!--[foreach from=$items item=\'item\']-->
                    <!--[array_field_isset assign=\'itemname\' array=$item.display_name field=$core_language returnValue=1]-->
                    <!--[if $itemname eq \'\']--><!--[assign var=\'itemname\' value=$item.name]--><!--[/if]-->
                    <li><!--[$itemname]--></li>
                    <!--[/foreach]-->
                </ul>
                <!--[/if]-->'."\n\n";

            // publication input
            } elseif ($field['fieldplugin'] == 'function.pmformpubinput.php') {
                $template_code_add = '<!--[if $'.$key.' neq \'\']-->'."\n".$template_code_fielddesc.'<!--[pnmodapifunc modname=\'pagemaster\' checkPerm=true handlePluginFields=true getApprovalState=true func=\'getPub\' tid=\''.$field['typedata'].'\' pid=$'.$key.' assign=\''.$key.'_publication\']-->'."\n".'<!--[/if]-->'."\n\n";
            }
        }

        // if it was no special field handle it normal
        if ($template_code_add == '') {
            if ($template_code_fielddesc == '') {
                $template_code_fielddesc = $key.': ';
            }
            if (is_array($pubfield)) {
                foreach ($pubfield as $a => $b) {
                    $template_code_add = '<!--[$'. $key .'.'. $a .']--><br/>'."\n\n";
                }
            } else {
                $template_code_add = '<!--[if $'.$key.' neq \'\']-->'."\n".$template_code_fielddesc.'<!--[$'. $key .'|pnvarprephtmldisplay]--><br/>'."\n".'<!--[/if]-->'."\n\n";
            }
        }
        $template_code .= $template_code_add;
    }

    // Add the Hooks support for viewpub
    $template_code .= "\n".'<!--[pnmodurl modname=\'pagemaster\' func=\'viewpub\' tid=$core_tid pid=$core_pid assign=\'returnurl\']-->
                            <!--[pnmodcallhooks hookobject=\'item\' hookaction=\'display\' hookid=$core_uniqueid module=\'pagemaster\' returnurl=$returnurl]-->';
    
    return $template_code;
}

function pagemasterGetPluginsOptionList()
{
    $dir = 'modules/pagemaster/pntemplates/plugins';
    $plugins = array ();
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
            if (substr($file, 0, 15) == 'function.pmform') {
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
    function parse_dir($dir, &$plugins) {
        if (!is_dir($dir) || !is_readable($dir)) {
            return;
        }
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if (substr($file, -4, 4) == '.xml') {
                    $plugins[] = array (
                        'text'  => $file,
                        'value' => $file
                    );
                }
            }
            closedir($dh);
        }
    }
    $plugins = array ();

    $dir = 'modules/pagemaster/workflows';
    parse_dir($dir, $plugins);
    $dir = 'config/workflows/pagemaster';
    parse_dir($dir, $plugins);
    return $plugins;
}

function pagemasterGetPlugin($file)
{
    static $plugins = array();

    if (empty($plugins[$file])) {
        $pluginType = pagemasterGetPluginTypeFromFilename($file);
        pagemasterloadPluginType($pluginType);
        $plugins[$file] = new $pluginType;
    }

    return $plugins[$file];
}

function pagemasterGetPluginTypeFromFilename($filename)
{
    $i = strpos($filename, '.', 9);
    if ($i === false) {
        return false;
    }
    return substr($filename, 9, $i -9);
}

function pagemasterloadPluginType($pluginType)
{
    static $loadedPlugins = array();

    if (empty($loadedPlugins[$pluginType])) {
        require_once("modules/pagemaster/pntemplates/plugins/function.$pluginType.php");
        $loadedPlugins[$pluginType] = 1;
    }
}

function handlePluginFields($publist, $pubfields)
{
    // Loop the plugins and process their data in the publist if postRead exists
    // Save memory using array keys instead $key => $values
    $akl = array_keys($publist);
    $akf = array_keys($pubfields);
    // Loop the plugins
    foreach ($akf as $fieldname) {
        // $pubfields[$fieldname] is a $field
        $plugin = pagemasterGetPlugin($pubfields[$fieldname]['fieldplugin']);

        if (method_exists($plugin, 'postRead')) {
            foreach ($akl as $l) {
                // $publist[$l] is a $pub
                if (isset($publist[$l][$fieldname]) && !empty($publist[$l][$fieldname])) {
                    $publist[$l][$fieldname] = $plugin->postRead($publist[$l][$fieldname], $pubfields[$fieldname]);
                }
            }
        }
    }

    return $publist;
}

function getTidFromTablename($tablename)
{
    $tid = '';
    while (is_numeric(substr($tablename, -1))) {
        $tid = substr($tablename, -1) . $tid;
        $tablename = substr($tablename, 0, strlen($tablename) - 1);
    }

    return (int)$tid;
}

function handlePluginOrderBy($orderby, $pubfields, $tbl_alias)
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
                $plugin =  pagemasterGetPlugin($plugin_name);
                if (method_exists($plugin, 'orderBy')) {
                    $orderby_col = $plugin->orderBy($field_name);
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
 * Loop the pubfields array until get the title field
 *
 * @param array $pubfields
 * @return name of the title field
 */
function getTitleField($pubfields)
{
    $ak = array_keys($pubfields);
    foreach ($ak as $i) {
        if ($pubfields[$i]['istitle'] == 1) {
            $core_title = $pubfields[$i]['name'];
            break;
        }
    }

    return $core_title;
}
