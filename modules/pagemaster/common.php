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
Loader::LoadClass("PmWorkflowUtil",'modules/pagemaster/classes');


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
            $maxlength = 'maxLength=\''.$pubfields[$k]['fieldmaxlength'].'\' ';
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
                                <td><!--[genericformplugin id=\''.$pubfields[$k]['name'].'\' '.$linecol.$toolTip.'\']--></td>
                            </tr>
                            ';
    }
    $template_code .= '
                            <tr>
                                <td><!--[pnformlabel for=\'core_publishdate\' text=\'' . __('Publish Date') . '\']-->:</td>
                                <td><!--[pnformdateinput id=\'core_publishdate\' includeTime=\'1\']--></td>
                            </tr>

                            <tr>
                                <td><!--[pnformlabel for=\'core_expiredate\' text=\'' . __('Expire Date') . '\']-->:</td>
                                <td><!--[pnformdateinput id=\'core_expiredate\' includeTime=\'1\']--></td>
                            </tr>

                            <tr>
                                <td><!--[pnformlabel for=\'core_language\' text=\'' . __('Language') . '\']-->:</td>
                                <td><!--[pnformlanguageselector id=\'core_language\' mandatory=\'0\']--></td>
                            </tr>

                            <tr>
                                <td><!--[pnformlabel for=\'core_showinlist\' text=\'' . __('Show in List') . '\']-->:</td>
                                <td><!--[pnformcheckbox id=\'core_showinlist\' checked=\'checked\']--></td>
                            </tr>
                        </table>

                        <!--[pnmodcallhooks hookobject=\'item\' hookaction=\''.$hookAction.'\' hookid="`$core_tid`-`$core_pid`" module=\'pagemaster\']-->

                        <!--[foreach item=\'action\' from=$actions]-->
                            <!--[pnformbutton commandName=$action.id text=$action.title|pnml]-->
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
                    <div class="pn-warningmsg"><!--[gt text=\"This is a generic template. Your can create a customized template (<b>viewpub_{$pubtype_name}.htm</b>) and store it in the the directory <b>/config/templates/pagemaster/input/</b> or within your theme in the <b>/templates/modules/pagemaster/input/</b> subfolder.\"]--></div>
                <!--[/pnsecauthaction_block]-->

                <h1><!--[pnml name=\'' . $pubtype['title'] . '\']--></h1>

                <p><!--[pnml name=\'' . $pubtype['description'] . '\']--></p>

                ';

    foreach ($pubdata as $key => $pubfield)
    {
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
    $classDirs = array();
    //Loader::LoadClass checks these dirs, strange
    $classDirs[] = 'config/classes/modules/pagemaster/classes/FormPlugins';
    $classDirs[] = 'modules/pagemaster/classes/FormPlugins';
    $plugins = array ();
    foreach ($classDirs as $classDir) {
        if ($dh = opendir($classDir)) {
            while (($file = readdir($dh)) !== false) {
                if (substr($file, 0, 6) == 'pmform') {
                    $pluginclass = substr($file, 0, -10);
                    $plugin = getPlugin($pluginclass);
                    $plugins[] = array (
                        'plugin' => $plugin,
                        'class' => $pluginclass    
                    );
                }
            }
            closedir($dh);
        }
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

function handlePluginFields($publist, $pubfields)
{
    foreach ($pubfields as $fieldname => $field) {
        $pluginclass = $field['fieldplugin'];
        $plugin = getPlugin($pluginclass);
        if (method_exists($plugin, 'postRead')) {
            foreach ($publist as $key => $pub) {
                if ($pub[$fieldname] <> '' and isset($pub[$fieldname]))
                    $publist[$key][$fieldname] = $plugin->postRead($pub[$fieldname], $field);
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
                $plugin = getPlugin($plugin_name);
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

/**
 * Singletone
 *
 * @param plugin class
 * @return plugin object
 */
function getPlugin($pluginclass)
{
    static $plugin_arr;
    if (!isset($plugin_arr[$pluginclass]))
    {
        Loader::LoadClass($pluginclass,'modules/pagemaster/classes/FormPlugins');
        $plugin_arr[$pluginclass] = new $pluginclass;
    }
    return $plugin_arr[$pluginclass];
}

function getPubFields($tid, $orderBy = '')
{
    static $pubfields_arr;
    if (empty($pubfields_arr[$tid]))
        $pubfields_arr[$tid] = DBUtil::selectObjectArray('pagemaster_pubfields', 'pm_tid = '.$tid, $orderBy, -1, -1, 'name');
    return $pubfields_arr[$tid];
}
function getPubType($tid)
{
    static $pubtype_arr;

    if (empty($pubtype_arr[$tid])) {
        $pubtype_arr[$tid] = DBUtil::selectObjectByID('pagemaster_pubtypes', $tid, 'tid');
    }

    return $pubtype_arr[$tid];
}
