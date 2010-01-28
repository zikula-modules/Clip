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

Loader::LoadClass('PmWorkflowUtil', 'modules/pagemaster/classes');

/**
 * Code generation functions
 */
function PMgen_editpub_tplcode($tid, $pubfields, $pubtype, $hookAction='new')
{
    // FIXME Review template path (to pnForm?)
    $template_code = '
                      <h1><!--[gt text=$pubtype.title]--></h1>

                      <!--[include file=\'pagemaster_generic_navbar.htm\' func=\'pubedit\']-->

                      <!--[insert name=\'getstatusmsg\']-->

                      <!--[pnsecauthaction_block component=\'pagemaster::\' instance=\'::\' level=ACCESS_ADMIN]-->
                          <div class="z-warningmsg">
                              <!--[pnmodurl modname=\'pagemaster\' type=\'admin\' func=\'showcode\' mode=\'input\' tid=$pubtype.tid assign=\'urlpecode\']-->
                              <!--[gt text=\'This is a generic template. Your can <a href="%1$s">get the code</a> and create individuals template (<b>pubedit_%2$s_{STEPNAME}.htm</b> or <b>pubedit_%2$s_all.htm</b>), then store it in the the config directory: <b>/config/templates/pagemaster/input/pubedit_%2$s_{STEPNAME}.htm</b> or within your theme: <b>/templates/modules/pagemaster/input/pubedit_%2$s_{STEPNAME}.htm</b>.\' tag1=$urlpecode tag2=$pubtype.formname]-->
                          </div>
                      <!--[/pnsecauthaction_block]-->

                      <!--[if $pubtype.description neq \'\']-->
                      <div class="pm-pubdesc"><!--[gt text=$pubtype.description]--></div>
                      <!--[/if]-->

                      <!--[pnform cssClass=\'z-form\' enctype=\'multipart/form-data\']-->
                          <div>
                              <!--[pnformvalidationsummary]-->
                              <fieldset>
                              <legend><!--[if isset($id)]--><!--[gt text=\'Edit publication\']--><!--[else]--><!--[gt text=\'New publication\']--><!--[/if]--></legend>
                      ';

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

        if (!empty($pubfields[$k]['description'])) {
            //$toolTip = " toolTip='{$pubfields[$k]['description']}'";
            $toolTip = str_replace("'", "\'", $pubfields[$k]['description']);
        } else {
            $toolTip = '';
        }

        // specific plugins
        if ($pmformname == 'pmformtextinput') {
            $linecol = " rows='20' cols='70'";
        } else {
            $linecol = '';
        }

        // scape sensible data
        $pubfields[$k]['title'] = str_replace("'", "\'", $pubfields[$k]['title']); 

        $template_code .= '
                            <div class="z-formrow">
                                <!--[pnformlabel for=\''.$pubfields[$k]['name'].'\' __text=\''.$pubfields[$k]['title'].'\''.((bool)$pubfields[$k]['ismandatory'] ? ' mandatorysym=true' : '').']-->
                                <!--[genericformplugin id=\''.$pubfields[$k]['name'].'\''.$linecol.$maxlength.']-->'.
                                ($toolTip ? "\n".'<span class="z-formnote z-sub"><!--[gt text=\''.$toolTip.'\']--></span>' : '').'
                            </div>
                            ';
    }
    $template_code .= '     </fieldset>

                            <fieldset>
                            <legend><!--[gt text=\'Publication options\']--></legend>
                            <div class="z-formrow">
                                <!--[pnformlabel for=\'core_language\' __text=\'' . no__('Language') . '\']-->
                                <!--[pnformlanguageselector id=\'core_language\' mandatory=\'0\']-->
                            </div>

                            <div class="z-formrow">
                                <!--[pnformlabel for=\'core_publishdate\' __text=\'' . no__('Publish date') . '\']-->
                                <!--[pnformdateinput id=\'core_publishdate\' includeTime=\'1\']-->
                            </div>

                            <div class="z-formrow">
                                <!--[pnformlabel for=\'core_expiredate\' __text=\'' . no__('Expire date') . '\']-->
                                <!--[pnformdateinput id=\'core_expiredate\' includeTime=\'1\']-->
                            </div>

                            <div class="z-formrow">
                                <!--[pnformlabel for=\'core_showinlist\' __text=\'' . no__('Show in list') . '\']-->
                                <!--[pnformcheckbox id=\'core_showinlist\' checked=\'checked\']-->
                            </div>
                            </fieldset>

                            <!--[pnmodcallhooks hookobject=\'item\' hookaction=\''.$hookAction.'\' hookid="`$core_tid`-`$core_pid`" module=\'pagemaster\']-->

                            <div class="z-formbuttons">
                                <!--[foreach item=\'action\' from=$actions]-->
                                    <!--[gt text=$action.title assign=\'actiontitle\']-->    
                                    <!--[pnformbutton commandName=$action.id text=$actiontitle]-->
                                <!--[/foreach]-->
                            </div>
                        </div>
                        <!--[/pnform]-->
                        ';

    return $template_code;
}

function PMgen_viewpub_tplcode($tid, $pubdata, $pubtype, $pubfields)
{
    $template_code = '<!--[pndebug]-->
                <!--[hitcount pid=$core_pid tid=$core_tid]-->

                <h1><!--[gt text=$pubtype.title]--></h1>

                <!--[include file=\'pagemaster_generic_navbar.htm\' func=\'pubview\']-->

                <!--[insert name=\'getstatusmsg\']-->

                <!--[pnsecauthaction_block component=\'pagemaster::\' instance=\'::\' level=ACCESS_ADMIN]-->
                    <div class="z-warningmsg">
                        <!--[pnmodurl modname=\'pagemaster\' type=\'admin\' func=\'showcode\' mode=\'outputfull\' tid=$pubtype.tid assign=\'urlpvcode\']-->
                        <!--[gt text=\'This is a generic template. Your can <a href="%1$s">get the pubview code</a> and create a customized template (<b>viewpub_%2$s.htm</b>), then store it in the the config directory: <b>/config/templates/pagemaster/output/viewpub_%2$s.htm</b> or within your theme: <b>/templates/modules/pagemaster/output/viewpub_%2$s.htm</b>.\' tag1=$urlpvcode tag2=$pubtype.filename]-->
                    </div>
                <!--[/pnsecauthaction_block]-->

                <!--[if $pubtype.description neq \'\']-->
                <div class="pm-pubdesc"><!--[gt text=$pubtype.description]--></div>
                <!--[/if]-->

                <div class="z-form">
                ';

    foreach ($pubdata as $key => $pubfield)
    {
        $template_code_add = '';
        $template_code_fielddesc = '';

        // check if field is to handle special
        if (isset($pubfields[$key])) {
            $field = $pubfields[$key];

            $template_code_fielddesc = '<!--[gt text=\''.$field['title'].'\']-->:';

            // handle some special plugins
            // FIXME move this to each plugin?
            switch ($field['fieldplugin'])
            { 
                // image plugin
                case 'pmformimageinput':
                    $template_code_add = '<!--[if $'.$field['name'].'.url neq \'\']-->
                                              <div class="z-formrow">
                                                  <span class="z-label">'.$template_code_fielddesc.'</span>
                                                  <span class="z-formnote">
                                                      <!--[$'.$field['name'].'.orig_name]--><br />
                                                      <img src="<!--[$'.$field['name'].'.thumbnailUrl]-->" /><br />
                                                      <img src="<!--[$'.$field['name'].'.url]-->" />
                                                  <span>
                                              </div>
                                          <!--[/if]-->'."\n\n";
                    break;

                // list input
                case 'pmformlistinput':
                    $template_code_add = '<!--[if $'.$field['name'].'.fullTitle neq \'\']-->
                                              <div class="z-formrow">
                                                  <span class="z-label">'.$template_code_fielddesc.'</span>
                                                  <span class="z-formnote"><!--[$'.$key.'.fullTitle]--><span>
                                              </div>
                                          <!--[/if]-->'."\n\n";
                    break;

                // multilist input
                case 'pmformmultilistinput':
                    $template_code_add = '<!--[if $'.$field['name'].' neq \'\']-->
                                              <div class="z-formrow">
                                                  <span class="z-label">'.$template_code_fielddesc.'</span>
                                                  <span class="z-formnote">
                                                      <ul>
                                                          <!--[foreach from=$'.$key.' item=\'item\']-->
                                                              <li><!--[$item.fullTitle]--></li>
                                                          <!--[/foreach]-->
                                                      </ul>
                                                  <span>
                                              </div>
                                          <!--[/if]-->'."\n\n";
                    break;

                // publication input
                case 'pmformpubinput'://z_prayer($field);
                    $template_code_add = '<!--[if $'.$key.' neq \'\']-->
                                              <div class="z-formrow">
                                                  <span class="z-label">'.$template_code_fielddesc.'</span>
                                                  <span class="z-formnote"><pre><!--[pmarray array=$'.$key.']--></pre><!--[*pnmodapifunc modname=\'pagemaster\' func=\'getPub\' tid=\''.$field['typedata'].'\' pid=$'.$key.' assign=\''.$key.'_pub\' checkPerm=true handlePluginFields=true getApprovalState=true*]--><span>
                                              </div>
                                          <!--[/if]-->'."\n\n";
                    break;
            }
        }

        // if it was no special field handle it normal
        if (empty($template_code_add)) {
            if (empty($template_code_fielddesc)) {
                $template_code_fielddesc = $key.':';
            }
            // filter some core fields (uids)
            if (in_array($key, array('core_author', 'cr_uid', 'lu_uid'))) {
                $snippet_body = '<!--[$'.$key.'|userprofilelink]--> (<!--[$'.$key.'|pnvarprephtmldisplay]-->)';
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
            // build the final snippet
            $template_code_add = '<!--[if $'.$key.' neq \'\']-->
                                      <div class="z-formrow">
                                          <span class="z-label">'.$template_code_fielddesc.'</span>
                                          <span class="z-formnote">'.$snippet_body.'<span>
                                      </div>
                                  <!--[/if]-->'."\n\n";
        }

        // add the snippet to the final template 
        $template_code .= $template_code_add;
    }

    // Add the Hooks support for viewpub
    $template_code .= '</div>

                       <!--[pnmodurl modname=\'pagemaster\' func=\'viewpub\' tid=$core_tid pid=$core_pid assign=\'returnurl\']-->
                       <!--[pnmodcallhooks hookobject=\'item\' hookaction=\'display\' hookid=$core_uniqueid module=\'pagemaster\' returnurl=$returnurl]-->';
    
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

    if (!isset($plugin_arr[$pluginclass])) {
        Loader::LoadClass($pluginclass, 'modules/pagemaster/classes/FormPlugins');
        $plugin_arr[$pluginclass] = new $pluginclass;
    }

    return $plugin_arr[$pluginclass];
}

function PMgetPubFields($tid, $orderBy = '')
{
    static $pubfields_arr;

    $tid = (int)$tid;
    if (!isset($pubfields_arr[$tid])) {
        $pubfields_arr[$tid] = DBUtil::selectObjectArray('pagemaster_pubfields', 'pm_tid = '.$tid, $orderBy, -1, -1, 'name');
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
