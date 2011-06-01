<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Lib
 */

/**
 * Clip Template Generator.
 */
class Clip_Generator
{
    protected static $tablesloaded = false;

    public static function pubdisplay($tid, $public=true, $forblock=false)
    {
        // build and process a dummy pubdata object
        $className = "Clip_Model_Pubdata{$tid}";
        $pubdata   = new $className();
        $pubdata->clipProcess();
        $pubdata->clipWorkflow();
        // get the record fields
        $recfields = $pubdata->pubFields();
        // and the relation fields information
        $relfields = $pubdata->getRelations(false);

        // build the display code
        $code = "\n";
        if (!$forblock) {
            $code .=
                '{if !$homepage}{pagesetvar name="title" value="`$pubdata.core_title` - `$pubtype.title` - `$modvars.ZConfig.sitename`"}{/if}'."\n".
                '{clip_hitcount pid=$pubdata.core_pid tid=$pubdata.core_tid}'."\n".
                "\n".
                '{include file=\'clip_generic_navbar.tpl\' section=\'display\'}'."\n".
                "\n".
                '<h2>{$pubdata.core_title|safetext}</h2>'."\n".
                "\n".
                '<div class="z-form clip-pub-details">';
        } else {
            $code .=
                '<div class="z-form clip-pub-block">'."\n".
                '    <div class="z-linear">'."\n";
        }

        $pubfields = Clip_Util::getPubFields($tid);

        foreach ($recfields as $key => $recfield)
        {
            $rowcode = array(
                'full'  => '',
                'label' => '',
                'body'  => ''
            );

            // check for relation fields
            if ($recfield == 'relation') {
                $rowcode['full'] =
                    '    <div class="z-formrow">'."\n".
                    '        <span class="z-label">{gt text=\''.$relfields[$key]['title'].'\'}:</span>'."\n".
                    '        {if $pubdata.'.$key.' AND count($pubdata.'.$key.')}'."\n".
                    '            <pre class="z-formnote">{clip_array array=$pubdata.'.$key.'->toArray()}</pre>'."\n".
                    '        {else}'."\n".
                    '            <span class="z-formnote z-sub">{gt text=\''.no__('(empty)').'\'}</span>'."\n".
                    '        {/if}'."\n".
                    '    </div>';
            }

            // check if field is to handle special
            if (isset($pubfields[$key])) {
                // $key is $field.name
                $field = $pubfields[$key];

                $rowcode['label'] = '{gt text=\''.$field['title'].'\'}:';

                // process the postRead and getPluginOutput
                $plugin = Clip_Util_Plugins::get($field['fieldplugin']);

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
                            $rowcode['full'] = !$forblock ? false : '<h5'.($public ? ' class="z-center"' : '').'>$pubdata.'.$key.'</h5>';
                            break;

                        // reads
                        case 'core_hitcount':
                            $rowcode['body'] = "\n".
                                '        <span class="z-formnote">{gt text=\'%s read\' plural=\'%s reads\' count=$pubdata.'.$key.' tag1=$pubdata.'.$key.'}</span>';
                            break;

                        // language
                        case 'core_language':
                            $rowcode['full'] =
                                '    <div class="z-formrow">'."\n".
                                '        <span class="z-label">'.$rowcode['label'].'</span>'."\n".
                                '            {if !empty($pubdata.'.$key.')}'."\n".
                                '                <span class="z-formnote">{$pubdata.'.$key.'|getlanguagename}</span>'."\n".
                                '            {else}'."\n".
                                '                <span class="z-formnote">{gt text=\''.no__('Available for all languages').'\'}</span>'."\n".
                                '            {/if}'."\n".
                                '        </span>'."\n".
                                '    </div>';
                            break;

                        // flags
                        case 'core_creator':
                        case 'core_online':
                        case 'core_indepot':
                        case 'core_showinmenu':
                        case 'core_showinlist':
                            $rowcode['body'] = "\n".
                                '        <span class="z-formnote">{$pubdata.'.$key.'|yesno}</span>';
                            break;

                        // user ids
                        case 'core_author':
                        case 'cr_uid':
                        case 'lu_uid':
                            $rowcode['body'] = "\n".
                                '        <span class="z-formnote">'."\n".
                                '            {$pubdata.'.$key.'|profilelinkbyuid}'."\n".
                                '            <span class="z-sub">[{$pubdata.'.$key.'|safehtml}]</span>'."\n".
                                '        </span>';
                            break;

                        // dates
                        case 'core_publishdate':
                        case 'core_expiredate':
                        case 'cr_date':
                        case 'lu_date':
                            $rowcode['body'] = "\n".
                                '        <span class="z-formnote">{$pubdata.'.$key.'|dateformat:\'datetimelong\'}</span>';
                            break;

                        default:
                            if (is_array($pubdata[$key])) {
                                // generic arrays
                                $rowcode['body'] = "\n".
                                    '        <pre class="z-formnote">{clip_array array=$pubdata.'.$key.'}</pre>';

                            } elseif (is_bool($pubdata[$key])) {
                                // generic booleans
                                $rowcode['body'] = "\n".
                                    '        <span class="z-formnote">{$pubdata.'.$key.'|yesno}</span>';

                            } else {
                                // generic strings
                                $rowcode['body'] = "\n".
                                    '        <span class="z-formnote">{$pubdata.'.$key.'|safetext}</span>';
                            }
                    }
                }

                // build the final row if not filled
                if ($rowcode['full'] !== false && empty($rowcode['full'])) {
                    $rowcode['full'] =
                        '    <div class="z-formrow">'."\n".
                        '        <span class="z-label">'.$rowcode['label'].'</span>'.$rowcode['body']."\n".
                        '    </div>';
                }
            }

            if ($rowcode['full'] !== false) {
                // add the snippet to the final template
                $code .= "\n".$rowcode['full']."\n";
            }
        }

        if (!$forblock) {
            // add the Hooks support for display
            $code .= '</div>'."\n".
                "\n".
                '<div class="clip-display-hooks">'."\n".
                '    {notifydisplayhooks eventname="clip.ui_hooks.pubtype`$pubtype.tid`.display_view" id=$pubdata.core_uniqueid}'."\n".
                '</div>';
        } else {
            $code .= '    </div>'."\n".
                     '</div>';
        }
        $code .= "\n\n";

        // if the template is a public output
        if ($public) {
            // add the row cycles
            $code = str_replace('z-formrow', 'z-formrow {cycle values=\'z-odd,z-even\'}', $code);
        }

        return $code;
    }

    public static function pubedit($tid)
    {
        $heading_newpub = no__('Submit a publication');
        $legend_pubcontent = no__('Publication content');

        $code = "\n".
                '{* process the title according the publication status *}'."\n".
                '{if $pubdata.id}'."\n".
                '    {gt text="Edit \'%s\'" tag1=$pubdata.core_title|truncate:50 assign=\'pagetitle\'}'."\n".
                '{else}'."\n".
                '    {gt text=\''.$heading_newpub.'\' assign=\'pagetitle\'}'."\n".
                '{/if}'."\n".
                '{if !$homepage}{pagesetvar name="title" value="`$pagetitle` - `$pubtype.title` - `$modvars.ZConfig.sitename`"}{/if}'."\n".
                "\n".
                '{include file=\'clip_generic_navbar.tpl\' section=\'form\'}'."\n".
                "\n".
                '<h2>{$pagetitle}</h2>'."\n".
                "\n".
                '{assign var=\'zformclass\' value="z-form clip-editform clip-editform-`$pubtype.tid` clip-editform-`$pubtype.tid`-`$clipargs.edit.state`"}'."\n".
                "\n".
                '{form cssClass=$zformclass enctype=\'multipart/form-data\'}'."\n".
                '    <div>'."\n".
                '        {formvalidationsummary}'."\n".
                '        <fieldset class="z-linear">'."\n".
                '            <legend>{gt text=\''.$legend_pubcontent.'\'}</legend>'."\n";

        // publication fields
        $pubfields = Clip_Util::getPubFields($tid)->toArray();

        foreach (array_keys($pubfields) as $k) {
            // get the formplugin name
            $formplugin = $pubfields[$k]['fieldplugin'];

            // FIXME lenghts
            if (!empty($pubfields[$k]['fieldmaxlength'])) {
                $maxlength = " maxLength='{$pubfields[$k]['fieldmaxlength']}'";
            } elseif ($formplugin == 'Text') {
                $maxlength = " maxLength='65535'";
            } else {
                $maxlength = '';
            }

            $toolTip = !empty($pubfields[$k]['description']) ? str_replace("'", "\'", $pubfields[$k]['description']) : '';

            // specific edit parameters
            // process the getPluginEdit of the plugin
            $plugin = Clip_Util_Plugins::get($formplugin);

            if (method_exists($plugin, 'getPluginEdit')) {
                $plugadd = $plugin->getPluginEdit($pubfields[$k]);
            } elseif ($formplugin == 'String' && $pubfields[$k]['istitle']) {
                $plugadd = ' cssClass="z-form-text-big"';
            } else {
                $plugadd = '';
            }

            // scape simple quotes where needed
            $pubfields[$k]['title'] = str_replace("'", "\'", $pubfields[$k]['title']);

            $code .= "\n".
                    '            <div class="z-formrow">'."\n".
                    '                {formlabel for=\''.$pubfields[$k]['name'].'\' _'.'_text=\''.$pubfields[$k]['title'].'\''.((bool)$pubfields[$k]['ismandatory'] ? ' mandatorysym=true' : '').'}'."\n".
                    '                {clip_form_genericplugin id=\''.$pubfields[$k]['name'].'\''.$maxlength.$plugadd.' group=\'pubdata\'}'."\n".
        ($toolTip ? '                <span class="z-formnote z-sub">{gt text=\''.$toolTip.'\'}</span>'."\n" : '').
                    '            </div>'."\n";
        }
        $code .=
                '        </fieldset>'."\n".
                "\n";

        // publication relations
        no__('Related publications');

        $code .=
                '        {if $relations}'."\n".
                '        <fieldset>'."\n".
                '            <legend>{gt text=\'Related publications\'}</legend>'."\n".
                "\n".
                '            {foreach from=$relations key=\'alias\' item=\'item\' name=\'relations\'}'."\n".
                '            <div class="z-formrow">'."\n".
                '                {formlabel for=$alias text=$item.title}'."\n".
                '                {clip_form_relation id=$alias relation=$item minchars=2 op=\'search\' group=\'pubdata\'}'."\n".
                '            </div>'."\n".
                '            {/foreach}'."\n".
                "\n".
                '        </fieldset>'."\n".
                '        {/if}'."\n".
                "\n".
        '';

        // publication options
        no__('Publication options');
        no__('Language');
        no__('Publish date');
        no__('Expire date');
        no__('Show in list');
        no__('Cancel');

        $code .=
                '        <fieldset>'."\n".
                '            <legend>{gt text=\'Publication options\'}</legend>'."\n".
                "\n".
                '            <div class="z-formrow">'."\n".
                '                {formlabel for=\'core_language\' _'.'_text=\'Language\'}'."\n".
                '                {formlanguageselector id=\'core_language\' group=\'pubdata\' mandatory=false}'."\n".
                '            </div>'."\n".
                "\n".
                '            <div class="z-formrow">'."\n".
                '                {formlabel for=\'core_publishdate\' _'.'_text=\'Publish date\'}'."\n".
                '                {formdateinput id=\'core_publishdate\' group=\'pubdata\' includeTime=true}'."\n".
                '                <em class="z-formnote z-sub">{gt text=\'leave blank if you do not want to schedule the publication\'}</em>'."\n".
                '            </div>'."\n".
                "\n".
                '            <div class="z-formrow">'."\n".
                '                {formlabel for=\'core_expiredate\' _'.'_text=\'Expire date\'}'."\n".
                '                {formdateinput id=\'core_expiredate\' group=\'pubdata\' includeTime=true}'."\n".
                '                <em class="z-formnote z-sub">{gt text=\'leave blank if you do not want the plublication expires\'}</em>'."\n".
                '            </div>'."\n".
                "\n".
                '            <div class="z-formrow">'."\n".
                '                {formlabel for=\'core_showinlist\' _'.'_text=\'Show in list\'}'."\n".
                '                {formcheckbox id=\'core_showinlist\' group=\'pubdata\' checked=\'checked\'}'."\n".
                '            </div>'."\n".
                '        </fieldset>'."\n".
                "\n".
                '        {notifydisplayhooks eventname="clip.ui_hooks.pubtype`$pubtype.tid`.form_edit" id=$pubobj.core_uniqueid}'."\n".
                "\n".
                '        <div class="z-buttons z-formbuttons">'."\n".
                '            {foreach item=\'action\' from=$actions}'."\n".
                '                {formbutton commandName=$action.id text=$action.title zparameters=$action.parameters.button|default:\'\'}'."\n".
                '            {/foreach}'."\n".
                '            {formbutton commandName=\'cancel\' __text=\'Cancel\' class=\'z-bt-cancel\'}'."\n".
                '        </div>'."\n".
                '    </div>'."\n".
                '{/form}'."\n\n";

        return $code;
    }

    /**
     * Build the Doctrine Model code dynamically.
     *
     * @param integer $tid Publication type ID.
     *
     * @return string The model class code.
     */
    public static function pubmodel($tid)
    {
        $table = "clip_pubdata{$tid}";
        $tables = DBUtil::getTables();

        if (isset($tables["{$table}_column"])) {
            $columns = $tables["{$table}_column"];
            $def = DBUtil::getTableDefinition($table);
            $opt = DBUtil::getTableOptions($table);
        } else {
            $columns = $def = $opt = array();
        }
        $columns = array_flip($columns);

        // relations
        $hasRelations = '';
        // owning side
        $relations = Clip_Util::getRelations($tid);
        foreach ($relations as $relation) {
            // set the method to use
            switch ($relation['type']) {
                case 0:
                case 2:
                    $method = 'hasOne';
                    break;
                case 1:
                case 3:
                    $method = 'hasMany';
                    break;
            }
            if ($method) {
                // build the relation code
                $relDefinition = "Clip_Model_Pubdata{$relation['tid2']} as {$relation['alias1']}";
                // set the relation arguments
                switch ($relation['type']) {
                    case 0: // o2o
                    case 1: // o2m
                        $relArgs = array(
                            'local'   => 'id',
                            'foreign' => "rel_{$relation['id']}"
                        );
                        break;
                    case 2: // m2o
                        $relArgs = array(
                            'local'   => "rel_{$relation['id']}",
                            'foreign' => 'id'
                        );
                        // add the relation column definition
                        $columns["pm_rel_{$relation['id']}"] = "rel_{$relation['id']}";
                        $def["pm_rel_{$relation['id']}"] = array(
                            'type'     => 'integer',
                            'length'   => 4,
                            'unsigned' => false
                        );
                        break;
                    case 3: // m2m
                        $relArgs = array(
                            'local'    => "rel_{$relation['id']}_1",
                            'foreign'  => "rel_{$relation['id']}_2",
                            'refClass' => "Clip_Model_Relation{$relation['id']}"
                        );
                }
                $relArgs = var_export($relArgs, true);
                $relArgs = str_replace('array (', 'array(', $relArgs);
                $relArgs = str_replace(",\n)", "\n)", $relArgs);
                $relArgs = str_replace("\n", "\n            ", $relArgs);
                $relArgs = str_replace("\n            )", "\n        )", $relArgs);
                // add the code line
                $hasRelations .= "
        \$this->$method('$relDefinition', $relArgs);
        ";
            }
        }
        // owned side
        $relations = Clip_Util::getRelations($tid, false);
        foreach ($relations as $relation) {
            // set the method to use
            switch ($relation['type']) {
                case 0:
                case 1:
                    $method = 'hasOne';
                    break;
                case 2:
                case 3:
                    $method = 'hasMany';
                    break;
            }
            if ($method) {
                // build the relation code
                $relDefinition = "Clip_Model_Pubdata{$relation['tid1']} as {$relation['alias2']}";
                // set the relation arguments
                switch ($relation['type']) {
                    case 0: //o2o
                    case 1: //o2m
                        $relArgs = array(
                            'local'   => "rel_{$relation['id']}",
                            'foreign' => 'id'
                        );
                        // add the relation column definition
                        $columns["pm_rel_{$relation['id']}"] = "rel_{$relation['id']}";
                        $def["pm_rel_{$relation['id']}"] = array(
                            'type' => 'integer',
                            'length' => 4,
                            'unsigned' => false
                        );
                        break;
                    case 2: //m2o
                        $relArgs = array(
                            'local'   => 'id',
                            'foreign' => "rel_{$relation['id']}"
                        );
                        break;
                    case 3: // m2m
                        $relArgs = array(
                            'local'    => "rel_{$relation['id']}_2",
                            'foreign'  => "rel_{$relation['id']}_1",
                            'refClass' => "Clip_Model_Relation{$relation['id']}"
                        );
                }
                $relArgs = var_export($relArgs, true);
                $relArgs = str_replace('array (', 'array(', $relArgs);
                $relArgs = str_replace(",\n)", "\n)", $relArgs);
                $relArgs = str_replace("\n", "\n            ", $relArgs);
                $relArgs = str_replace("\n            )", "\n        )", $relArgs);
                // add the code line
                $hasRelations .= "
        \$this->$method('$relDefinition', $relArgs);
        ";
            }
        }

        // columns
        $hasColumns = '';
        foreach ($def as $columnName => $array) {
            $columnAlias = $columns[$columnName];
            // removes the basic type and lenght
            // and clean the parameters
            $type   = $array['type'];
            $length = (is_null($array['length']) ? 'null' : $array['length']);
            unset($array['type']);
            unset($array['length']);
            if (isset($array['default'])) {
                $default = $array['default'];
            }
            $array = array_filter($array);
            if (isset($default)) {
                $array['default'] = $default;
                unset($default);
            }
            // process the modifiers
            $array = !empty($array) ? ', '.var_export($array, true) : null;
            if (!empty($array)) {
                $array = str_replace('array (', 'array(', $array);
                $array = str_replace(",\n)", "\n)", $array);
                $array = str_replace("\n", "\n            ", $array);
                $array = str_replace("\n            )", "\n        )", $array);
            }
            // verify the lenght
            $length = (!empty($array) || $length != 'null') ? ", $length" : '';
            // add the column
            $hasColumns .= !empty($hasColumns) ? "\n\n        " : '';
            $hasColumns .= "\$this->hasColumn('$columnName as $columnAlias', '$type'{$length}{$array});";
        }

        // options
        $options = '';
        foreach ($opt as $k => $v) {
            if (in_array($k, array('type', 'charset', 'collate'))) {
                continue;
            }
            $options .= (!empty($options) ? "\n        " : '')."\$this->option('$k', '$v');";
        }

        // generate the model code
        $code = "
/**
 * Clip
 * Generated Model Class
 *
 * @link http://code.zikula.org/clip/
 */

/**
 * This is the model class that define the entity structure and behaviours.
 */
class Clip_Model_Pubdata{$tid} extends Clip_Base_Pubdata
{
    /**
     * Set table definition.
     *
     * @return void
     */
    public function setTableDefinition()
    {
        \$this->setTableName('$table');

        $hasColumns

        $options
    }

    /**
     * Record setup.
     *
     * @return void
     */
    public function setUp()
    {
        \$this->actAs('Zikula_Doctrine_Template_StandardFields', array('oldColumnPrefix' => 'pm_'));
        $hasRelations
    }
}
";

        return $code;
    }

    /**
     * Build the Doctrine Table code dynamically.
     *
     * @param integer $tid Publication type ID.
     *
     * @return string The table class code.
     */
    public static function pubtable($tid)
    {
        // generate the model code
        $code = "
/**
 * Clip
 * Generated Model Class
 *
 * @link http://code.zikula.org/clip/
 */

/**
 * Doctrine_Table class used to implement own special entity methods.
 */
class Clip_Model_Pubdata{$tid}Table extends Clip_Doctrine_Table
{

}
";
        return $code;
    }

    /**
     * Build the Doctrine m2m Relation classes code dynamically.
     *
     * @return string The relation classes code.
     */
    public static function evalrelations()
    {
        $ownedrelations = Clip_Util::getRelations(-1, false, true);

        $code = '';
        $hasColumns = '';
        foreach ($ownedrelations as $tid => $relations) {
            foreach ($relations as $relation) {
                $classname = 'Clip_Model_Relation'.$relation['id'];
                if ($relation['type'] != 3 || class_exists($classname, false)) {
                    continue;
                }
                for ($i = 1; $i <= 2; $i++) {
                    $columnName = "rel_{$relation['id']}_$i";
                    $array = var_export(array('primary' => true), true);
                    $array = str_replace('array (', 'array(', $array);
                    $array = str_replace(",\n)", "\n)", $array);
                    $array = str_replace("\n", "\n            ", $array);
                    $array = str_replace("\n            )", "\n        )", $array);
                    $hasColumns .= !empty($hasColumns) ? "\n\n        " : '';
                    $hasColumns .= "\$this->hasColumn('$columnName', 'integer', 4, {$array});";
                }

                // add the refClass
                $code .= "
class Clip_Model_Relation{$relation['id']} extends Doctrine_Record
{
    public function setTableDefinition()
    {
        \$this->setTableName('clip_relation{$relation['id']}');

        $hasColumns
    }
}
class Clip_Model_Relation{$relation['id']}Table extends Clip_Doctrine_Table
{

}
";
            }
        }

        if (!empty($code)) {
            eval($code);
        }
    }

    public static function loadModelClasses($force = false)
    {
        static $loaded = array();

        // refresh the pubtypes definitions
        self::addtables($force);

        $pubtypes = Doctrine_Core::getTable('Clip_Model_Pubtype')->selectFieldArray('tid');

        foreach ($pubtypes as $tid) {
            if (!isset($loaded[$tid])) {
                $code = Clip_Generator::pubmodel($tid);
                eval($code);
                $code = Clip_Generator::pubtable($tid);
                eval($code);
                $loaded[$tid] = true;
            }
        }

        self::evalrelations();
    }

    // dynamic pubdata tables
    private static function _addtable(&$tables, $tid, $tableColumn, $tableDef)
    {
        $tablename = "clip_pubdata{$tid}";

        $tables[$tablename] = DBUtil::getLimitedTablename($tablename);
        $tables[$tablename.'_column']     = $tableColumn;
        $tables[$tablename.'_column_def'] = $tableDef;

        //ObjectUtil::addStandardFieldsToTableDefinition($tables[$tablename.'_column'], 'pm_');
        //ObjectUtil::addStandardFieldsToTableDataDefinition($tables[$tablename.'_column_def']);

        // TODO indexes
        /*
        $tables[$tablename.'_column_idx'] = array (
            'core_online' => 'core_online' //core_showinlist
        );
        */
    }

    public static function addtables($force = false)
    {
        $modinfo = ModUtil::getInfoFromName('Clip');

        if ($modinfo['state'] == ModUtil::STATE_UNINITIALISED && !$force) {
            return;
        }
        if (self::$tablesloaded && !$force) {
            return;
        }
        self::$tablesloaded = true;

        $tables  = array();
        $pubfields = Doctrine_Core::getTable('Clip_Model_Pubfield')
                     ->selectCollection('', 'tid ASC, id ASC');

        if ($pubfields === false) {
            return LogUtil::registerError('Error! Failed to load the pubfields.');
        }

        $old_tid = 0;

        $tableOrder = array(
            'core_pid'         => 'pm_pid',
            'id'               => 'pm_id'
        );
        $tableColumnCore = array(
            'id'               => 'pm_id',
            'core_pid'         => 'pm_pid',
            'core_author'      => 'pm_author',
            'core_hitcount'    => 'pm_hitcount',
            'core_language'    => 'pm_language',
            'core_revision'    => 'pm_revision',
            'core_online'      => 'pm_online',
            'core_indepot'     => 'pm_indepot',
            'core_showinmenu'  => 'pm_showinmenu',
            'core_showinlist'  => 'pm_showinlist',
            'core_publishdate' => 'pm_publishdate',
            'core_expiredate'  => 'pm_expiredate'
        );
        $tableDefCore = array(
            'id'               => 'I4 PRIMARY AUTO',
            'core_pid'         => 'I4 NOTNULL',
            'core_author'      => 'I4 NOTNULL',
            'core_hitcount'    => 'I8 DEFAULT 0',
            'core_language'    => "C(10) NOTNULL", //FIXME how many chars are needed for a gettext code?
            'core_revision'    => 'I4 NOTNULL DEFAULT 1',
            'core_online'      => 'L DEFAULT 0',
            'core_indepot'     => 'L DEFAULT 0',
            'core_showinmenu'  => 'L DEFAULT 0',
            'core_showinlist'  => 'L DEFAULT 1',
            'core_publishdate' => 'T',
            'core_expiredate'  => 'T'
        );

        // loop the pubfields adding their definitions
        // to their pubdata tables
        $tableColumn = array();
        $tableDef    = array();

        foreach ($pubfields as $pubfield) {
            // if we change of publication type
            if ($pubfield['tid'] != $old_tid && $old_tid != 0) {
                // add the table definition to the $tables array
                self::_addtable($tables, $old_tid, array_merge($tableOrder, $tableColumn, $tableColumnCore), array_merge($tableDefCore, $tableDef));
                // and reset the columns and definitions for the next pubtype
                $tableColumn = array();
                $tableDef    = array();
            }

            // add the column and definition for this field
            $tableColumn[$pubfield['name']] = "pm_{$pubfield['id']}";
            $tableDef[$pubfield['name']]    = "{$pubfield['fieldtype']} NULL";

            // set the actual tid to check a pubtype change in the next cycle
            $old_tid = $pubfield['tid'];
        }

        // the final one doesn't trigger a tid change
        if (!empty($tableColumn)) {
            self::_addtable($tables, $old_tid, array_merge($tableOrder, $tableColumn, $tableColumnCore), array_merge($tableDefCore, $tableDef));
        }

        // validates the existence of all the pubdata tables
        // to ensure the creation of all the dynamic classes
        $pubtypes = Doctrine_Core::getTable('Clip_Model_Pubtype')->selectFieldArray('tid');
        foreach ($pubtypes as $tid) {
            if (!isset($tables["clip_pubdata{$tid}"])) {
                self::_addtable($tables, $tid, $tableColumnCore, $tableDefCore);
            }
        }

        $serviceManager = ServiceUtil::getManager();
        $dbtables = $serviceManager['dbtables'];
        $serviceManager['dbtables'] = array_merge($dbtables, (array)$tables);
    }
}
