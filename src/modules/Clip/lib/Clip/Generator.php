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
    public static function pubdisplay($tid, $public=true, $forblock=false)
    {
        // build and process a dummy pubdata object
        $className = "ClipModels_Pubdata{$tid}";
        $pubdata   = new $className();
        $pubdata->clipValues();
        $pubdata->clipWorkflow();
        // get the record fields
        $recfields = $pubdata->pubFields();

        $pubfields = Clip_Util::getPubFields($tid);

        $code = '';
        foreach ($recfields as $name => $recfield)
        {
            $rowcode = array(
                'full'  => '',
                'label' => '',
                'body'  => ''
            );

            // check for relation fields
            if ($recfield == 'relation') {
                $rowcode['full'] =
                    '        <div class="z-formrow">'."\n".
                    '            <span class="z-label">{$relations.'.$name.'|clip_translate}:</span>'."\n".
                    '            {if $pubdata.'.$name.'|clip_exists}'."\n".
                    '                <pre class="z-formnote">{clip_dump var=$pubdata.'.$name.'->toArray()}</pre>'."\n".
                    '            {else}'."\n".
                    '                <span class="z-formnote z-sub">{gt text=\''.no__('(empty)').'\'}</span>'."\n".
                    '            {/if}'."\n".
                    '        </div>';
            }

            // check if field is to handle special
            if (isset($pubfields[$name])) {
                $field = $pubfields[$name];

                $rowcode['label'] = '{$pubfields.'.$name.'|clip_translate}:';

                // process the postRead and getPluginOutput
                $plugin = Clip_Util_Plugins::get($field['fieldplugin']);

                if (method_exists($plugin, 'postRead')) {
                    $plugin->postRead($pubdata, $field);
                } else {
                    $pubdata[$name] = '';
                }

                if (method_exists($plugin, 'getOutputDisplay')) {
                    $plugincode = $plugin->getOutputDisplay($field);
                    $rowcode = array_merge($rowcode, (array)$plugincode);
                }
            }

            // if the row is not defined yet
            if (empty($rowcode['full'])) {
                // fill the label if empty
                if (empty($rowcode['label'])) {
                    $rowcode['label'] = "$name:";
                }

                // fill the body if empty
                if (empty($rowcode['body'])) {
                    // filter some core fields
                    switch ($name) {
                        // title
                        case 'core_title':
                            $rowcode['full'] = !$forblock ? false : '        <h5>{$pubdata.'.$name.'}</h5>';
                            break;

                        // reads
                        case 'core_hitcount':
                            $rowcode['body'] = "\n".
                                '            <span class="z-formnote">{gt text=\'%s read\' plural=\'%s reads\' count=$pubdata.'.$name.' tag1=$pubdata.'.$name.'}</span>';
                            break;

                        // language
                        case 'core_language':
                            $rowcode['full'] =
                                '        <div class="z-formrow">'."\n".
                                '            <span class="z-label">'.$rowcode['label'].'</span>'."\n".
                                '            {if !empty($pubdata.'.$name.')}'."\n".
                                '                <span class="z-formnote">{$pubdata.'.$name.'|getlanguagename}</span>'."\n".
                                '            {else}'."\n".
                                '                <span class="z-formnote">{gt text=\''.no__('Available for all languages.').'\'}</span>'."\n".
                                '            {/if}'."\n".
                                '        </div>';
                            break;

                        // flags
                        case 'core_creator':
                        case 'core_online':
                        case 'core_intrash':
                        case 'core_visible':
                        case 'core_locked':
                            $rowcode['body'] = "\n".
                                '            <span class="z-formnote">{$pubdata.'.$name.'|yesno}</span>';
                            break;

                        // user ids
                        case 'core_author':
                        case 'cr_uid':
                        case 'lu_uid':
                            $rowcode['body'] = "\n".
                                '            <span class="z-formnote">'."\n".
                                '                {$pubdata.'.$name.'|profilelinkbyuid}'."\n".
                                '                <span class="z-sub">[{$pubdata.'.$name.'|safehtml}]</span>'."\n".
                                '            </span>';
                            break;

                        // dates
                        case 'core_publishdate':
                        case 'cr_date':
                        case 'lu_date':
                            $rowcode['body'] = "\n".
                                '            <span class="z-formnote">{$pubdata.'.$name.'|dateformat:\'datetimelong\'}</span>';
                            break;

                        case 'core_expiredate':
                            $rowcode['body'] = "\n".
                                '            {gt text=\''.no__('No expire date specified.').'\' assign=\'defexpire\'}'."\n".
                                '            <span class="z-formnote">{$pubdata.'.$name.'|dateformat:\'datetimelong\'|default:$defexpire}</span>';
                            break;

                        default:
                            if (is_array($pubdata[$name])) {
                                // generic arrays
                                $rowcode['body'] = "\n".
                                    '            <pre class="z-formnote">{clip_dump var=$pubdata.'.$name.'}</pre>';

                            } elseif (is_bool($pubdata[$name])) {
                                // generic booleans
                                $rowcode['body'] = "\n".
                                    '            <span class="z-formnote">{$pubdata.'.$name.'|yesno}</span>';

                            } else {
                                // generic strings
                                $rowcode['body'] = "\n".
                                    '            <span class="z-formnote">{$pubdata.'.$name.'|safetext}</span>';
                            }
                    }
                }

                // build the final row if not filled
                if ($rowcode['full'] !== false && empty($rowcode['full'])) {
                    $rowcode['full'] =
                        '        <div class="z-formrow">'."\n".
                        '            <span class="z-label">'.$rowcode['label'].'</span>'.$rowcode['body']."\n".
                        '        </div>';
                }
            }

            if ($rowcode['full'] !== false) {
                if ($forblock && strpos($name, 'core_') !== 0) {
                    // add non core rows commented out
                    $code .= "{*\n".$rowcode['full']."\n*}";
                } else {
                    // add the snippet to the final template
                    $code .= "\n".$rowcode['full']."\n";
                }
            }
        }

        // if the template is a public output
        if ($public && !$forblock) {
            // add the row cycles
            $code = str_replace('z-formrow', 'z-formrow {cycle values=\'z-even,z-odd\'}', $code);
        }

        // build the output
        $view = Zikula_View::getInstance('Clip');

        $template = 'generic_'.($forblock ? 'blockpub' : 'display').'.tpl';
        $tplpath  = $view->get_template_path($template);
        $output   = file_get_contents($tplpath.'/'.$template);

        return str_replace('{$code}', $code, $output);
    }

    public static function pubedit($tid)
    {
        // publication fields
        $pubfields = Clip_Util::getPubFields($tid);

        $code = '';
        foreach ($pubfields as $name => $pubfield) {
            // get the formplugin name
            $formplugin = $pubfield['fieldplugin'];

            // FIXME lenghts
            if (!empty($pubfield['fieldmaxlength'])) {
                $maxlength = " maxLength='{$pubfield['fieldmaxlength']}'";
            } elseif ($formplugin == 'Text') {
                $maxlength = " maxLength='65535'";
            } else {
                $maxlength = '';
            }

            // specific edit parameters
            // process the getPluginEdit of the plugin
            $plugin = Clip_Util_Plugins::get($formplugin);

            $plugadd = '';
            $plugres = null;
            if (method_exists($plugin, 'getOutputEdit')) {
                $plugres = $plugin->getOutputEdit($pubfield);
                if (is_array($plugres)) {
                    if (isset($plugres['full'])) {
                        $code .= $plugres['full'];
                    } elseif (isset($plugres['args'])) {
                        $plugadd = $plugres['args'];
                    }
                }
            }

            // build the field's row output
            if (!isset($plugres['full'])) {
                $code .= "\n".
                        '                <div class="z-formrow">'."\n".
                        '                    {clip_form_label for=\''.$name.'\' text=$pubfields.'.$name.'.title|clip_translate'.((bool)$pubfield['ismandatory'] ? ' mandatorysym=true' : '').'}'."\n".
                        '                    {clip_form_plugin field=\''.$name.'\''.$maxlength.$plugadd.'}'."\n".
                        '                    {if $pubfields.'.$name.'.description|clip_translate}'."\n".
                        '                        <span class="z-formnote z-sub">{$pubfields.'.$name.'.description|clip_translate}</span>'."\n".
                        '                    {/if}'."\n".
                        '                </div>'."\n";
            }
        }

        // build the output
        $view = Zikula_View::getInstance('Clip');

        $tplpath  = $view->get_template_path('generic_edit.tpl');
        $output   = file_get_contents($tplpath.'/generic_edit.tpl');
        $output   = str_replace('{$code}', $code, $output);

        return $output;
    }

    /**
     * Build the Doctrine Model code dynamically.
     *
     * @param integer $tid   Publication type ID.
     * @param boolean $force Force the reload of relations.
     *
     * @return string The model class code.
     */
    public static function pubmodel($tid, $force = false)
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
        $relations = Clip_Util::getRelations($tid, true, $force);
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
                $relDefinition = "ClipModels_Pubdata{$relation['tid2']} as {$relation['alias1']}";
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
                        $columns["rel_{$relation['id']}"] = "rel_{$relation['id']}";
                        $def["rel_{$relation['id']}"] = array(
                            'type'     => 'integer',
                            'length'   => 4,
                            'unsigned' => false
                        );
                        break;
                    case 3: // m2m
                        $relArgs = array(
                            'local'    => "rel_{$relation['id']}_1",
                            'foreign'  => "rel_{$relation['id']}_2",
                            'refClass' => "ClipModels_Relation{$relation['id']}"
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
                $relDefinition = "ClipModels_Pubdata{$relation['tid1']} as {$relation['alias2']}";
                // set the relation arguments
                switch ($relation['type']) {
                    case 0: //o2o
                    case 1: //o2m
                        $relArgs = array(
                            'local'   => "rel_{$relation['id']}",
                            'foreign' => 'id'
                        );
                        // add the relation column definition
                        $columns["rel_{$relation['id']}"] = "rel_{$relation['id']}";
                        $def["rel_{$relation['id']}"] = array(
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
                            'refClass' => "ClipModels_Relation{$relation['id']}"
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

        $options .= (!empty($options) ? "\n        " : '')."\$this->index('urltitle_index', array(
                'fields' => array('urltitle')
            )
        );";

        // title field
        $titlefield = Clip_Util::getTitleField($tid);

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
class ClipModels_Pubdata{$tid} extends Clip_Doctrine_Pubdata
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
        \$this->actAs('Zikula_Doctrine_Template_StandardFields');
        $hasRelations
    }

    /**
     * Returns the relations as an indexed array.
     *
     * @param boolean \$onlyown Retrieves owning relations only (default: false).
     * @param strung  \$field   Retrieve a KeyValue array as alias => \$field (default: null).
     *
     * @return array List of available relations.
     */
    public function getRelations(\$onlyown = true, \$field = null)
    {
        return call_user_func_array(array('ClipModels_Pubdata{$tid}Table', 'clipRelations'), array(\$onlyown, \$field));
    }

    /**
     * Utility methods to assign Clip Values
     */
    public function assignDefaultValues(\$overwrite = false)
    {
        \$this->assignClipValues(\$this);

        parent::assignDefaultValues(\$overwrite);
    }

    public function assignClipValues(&\$obj)
    {
        if (is_object(\$obj)) {
            \$obj->clip_state = false;
            \$obj->mapValue('core_tid',        $tid);
            \$obj->mapValue('core_titlefield', '$titlefield');
            \$obj->mapValue('core_title',      \$obj->_get(\$obj->core_titlefield, false) ? \$obj[\$obj->core_titlefield] : '');
            \$obj->mapValue('core_uniqueid',   \$obj->_get('core_pid', false) ? \$obj->core_tid.'-'.\$obj->core_pid : null);
            \$obj->mapValue('core_creator',    \$obj->_get('core_author', false) ? (\$obj->core_author == UserUtil::getVar('uid') ? true : false) : null);
        } else {
            \$obj['core_tid']        = $tid;
            \$obj['core_titlefield'] = '$titlefield';
            \$obj['core_title']      = isset(\$obj[\$obj['core_titlefield']]) ? \$obj[\$obj['core_titlefield']] : '';
            \$obj['core_uniqueid']   = isset(\$obj['core_pid']) && \$obj['core_pid'] ? \$obj['core_tid'].'-'.\$obj['core_pid'] : null;
            \$obj['core_creator']    = isset(\$obj['core_author']) ? (\$obj['core_author'] == UserUtil::getVar('uid') ? true : false) : null;
        }

        return \$obj;
    }

    /**
     * Hydration hook.
     *
     * @return void
     */
    public function postHydrate(\$event)
    {
        \$event->data = \$this->assignClipValues(\$event->data);
    }
}
";

        return $code;
    }

    /**
     * Build the Doctrine Table code dynamically.
     *
     * @param integer $tid   Publication type ID.
     * @param boolean $force Force the load of relations.
     *
     * @return string The table class code.
     */
    public static function pubtable($tid, $force = false)
    {
        $ownRelations = '';
        $allRelations = '';

        // owning side
        $relations = Clip_Util::getRelations($tid, true, $force);
        foreach ($relations as $relation) {
            // add the relation array field
            $ownRelations .= "
        \$relations['".str_replace("'", "\'", $relation['alias1'])."'] = array(
            'id'       => {$relation['id']},
            'tid'      => {$relation['tid2']},
            'type'     => {$relation['type']},
            'alias'    => '".str_replace("'", "\'", $relation['alias1'])."',
            'title'    => '".str_replace("'", "\'", $relation['title1'])."',
            'descr'    => '".str_replace("'", "\'", $relation['descr1'])."',
            'opposite' => '".str_replace("'", "\'", $relation['alias2'])."',
            'single'   => ".($relation['type']%2 == 0 ? 'true' : 'false').",
            'own'      => true
        );
";
        }

        // owned side
        $relations = Clip_Util::getRelations($tid, false);
        foreach ($relations as $relation) {
            // add the relation array field
            $allRelations .= "
            \$relations['".str_replace("'", "\'", $relation['alias2'])."'] = array(
                'id'       => {$relation['id']},
                'tid'      => {$relation['tid1']},
                'type'     => {$relation['type']},
                'alias'    => '".str_replace("'", "\'", $relation['alias2'])."',
                'title'    => '".str_replace("'", "\'", $relation['title2'])."',
                'descr'    => '".str_replace("'", "\'", $relation['descr2'])."',
                'opposite' => '".str_replace("'", "\'", $relation['alias1'])."',
                'single'   => ".($relation['type'] <= 1 ? 'true' : 'false').",
                'own'      => false
            );
";
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
 * Doctrine_Table class used to implement own special entity methods.
 */
class ClipModels_Pubdata{$tid}Table extends Clip_Doctrine_Table
{
    /**
     * Returns the relations as an indexed array.
     *
     * @param boolean \$onlyown Retrieves owning relations only (default: false).
     * @param strung  \$field   Retrieve a KeyValue array as alias => \$field (default: null).
     *
     * @return array List of available relations.
     */
    static public function clipRelations(\$onlyown = true, \$field = null)
    {
        \$relations = array();

        // own relations
        $ownRelations

        if (!\$onlyown) {
            // foreign relations
            $allRelations
        }

        // return here if no relations or no specific field requested
        if (!\$relations || !\$field) {
            return \$relations;
        }

        \$v = reset(\$relations);
        if (!isset(\$v[\$field])) {
            throw new Exception(\"Invalid field [\$field] requested for the property [\$key] on \".get_class().\"::getRelations\");
        }

        \$result = array();
        foreach (\$relations as \$k => \$v) {
            \$result[\$k] = \$v[\$field];
        }

        return \$result;
    }
}
";
        return $code;
    }

    /**
     * Build the Doctrine m2m Relation classes code dynamically.
     *
     * @return string The relation classes code.
     */
    public static function createRelationsModels()
    {
        $path = ModUtil::getVar('Clip', 'modelspath');

        // delete all the existing relation models first
        $files = FileUtil::getFiles($path, false, true, 'php');

        foreach ($files as $file) {
            if (strpos($file, 'Relation') === 0) {
                unlink("$path/$file");
            }
        }
        unset($files);

        $allrelations = Clip_Util::getRelations(-1, false, true);

        $code = '';
        foreach ($allrelations as $tid => $relations) {
            foreach ($relations as $relation) {
                $classname = 'ClipModels_Relation'.$relation['id'];
                if ($relation['type'] != 3 || class_exists($classname, false)) {
                    continue;
                }
                $hasColumns = '';
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

                // save the relation class
                $code = "
class ClipModels_Relation{$relation['id']} extends Doctrine_Record
{
    public function setTableDefinition()
    {
        \$this->setTableName('clip_relation{$relation['id']}');

        $hasColumns
    }
}
";
                $file = "$path/Relation{$relation['id']}.php";
                file_put_contents($file, '<?php'.$code);

                // save the relation table class
                $code = "
class ClipModels_Relation{$relation['id']}Table extends Clip_Doctrine_Table
{

}
";
                $file = "$path/Relation{$relation['id']}Table.php";
                file_put_contents($file, '<?php'.$code);
            }
        }
    }

    // dynamic pubdata tables
    private static function _addtable(&$tables, $tid, $tableColumn, $tableDef)
    {
        $tablename = "clip_pubdata{$tid}";

        $tables[$tablename] = DBUtil::getLimitedTablename($tablename);
        $tables[$tablename.'_column']     = $tableColumn;
        $tables[$tablename.'_column_def'] = $tableDef;
    }

    public static function addtables($tid = 0, $force = false)
    {
        static $added = array();

        if ((isset($added[$tid]) || $tid && isset($added[0])) && !$force) {
            return;
        }

        $added[$tid] = true;

        $where  = $tid ? array(array('tid = ?', (int)$tid)) : '' ;

        $pubfields = Doctrine_Core::getTable('Clip_Model_Pubfield')
                     ->selectCollection($where, 'tid ASC, lineno ASC');

        if ($pubfields === false) {
            return LogUtil::registerError('Error! Failed to load the pubfields.');
        }

        $tables = array();
        $tableOrder = array(
            'core_pid'         => 'pid',
            'id'               => 'id'
        );
        $tableColumnCore = array(
            'id'               => 'id',
            'core_pid'         => 'pid',
            'core_urltitle'    => 'urltitle',
            'core_author'      => 'author',
            'core_hitcount'    => 'hits',
            'core_language'    => 'language',
            'core_revision'    => 'revision',
            'core_online'      => 'online',
            'core_intrash'     => 'intrash',
            'core_visible'     => 'visible',
            'core_locked'      => 'locked',
            'core_publishdate' => 'publishdate',
            'core_expiredate'  => 'expiredate'
        );
        $tableDefCore = array(
            'id'               => 'I4 PRIMARY AUTO',
            'core_pid'         => 'I4 NOTNULL',
            'core_urltitle'    => "C(255) NOTNULL",
            'core_author'      => 'I4 NOTNULL',
            'core_hitcount'    => 'I8 DEFAULT 0',
            'core_language'    => "C(10) NOTNULL", //FIXME how many chars are needed for a gettext code?
            'core_revision'    => 'I4 NOTNULL DEFAULT 1',
            'core_online'      => 'L DEFAULT 0',
            'core_intrash'     => 'L DEFAULT 0',
            'core_visible'     => 'L DEFAULT 1',
            'core_locked'      => 'L DEFAULT 0',
            'core_publishdate' => 'T',
            'core_expiredate'  => 'T'
        );

        // loop the pubfields adding their definitions
        // to their pubdata tables
        $tableColumn = array();
        $tableDef    = array();

        $old_tid = 0;

        foreach ($pubfields as $pubfield) {
            // if we change of publication type
            if ($pubfield['tid'] != $old_tid && $old_tid != 0) {
                // add the table definition to the $tables array
                self::_addtable($tables, $old_tid, array_merge($tableOrder, $tableColumn, $tableColumnCore), array_merge($tableDefCore, $tableDef));
                // and reset the columns and definitions for the next pubtype
                $tableColumn = array();
                $tableDef    = array();
            }

            $default = $pubfield['iscounter'] ? 'DEFAULT 0' : 'NULL';

            // add the column and definition for this field
            $tableColumn[$pubfield['name']] = "field{$pubfield['id']}";
            $tableDef[$pubfield['name']]    = "{$pubfield['fieldtype']} {$default}";

            // set the actual tid to check a pubtype change in the next cycle
            $old_tid = $pubfield['tid'];
        }

        // the final one doesn't trigger a tid change
        if (!empty($tableColumn)) {
            self::_addtable($tables, $old_tid, array_merge($tableOrder, $tableColumn, $tableColumnCore), array_merge($tableDefCore, $tableDef));
        }

        if ($tid && !count($pubfields)) {
            self::_addtable($tables, $tid, array_merge($tableOrder, $tableColumn, $tableColumnCore), array_merge($tableDefCore, $tableDef));
        }

        if (!$tid) {
            // validates the existence of all the pubdata tables
            // to ensure the creation of all the pubdata model classes
            $pubtypes = Doctrine_Core::getTable('Clip_Model_Pubtype')->selectFieldArray('tid');
            foreach ($pubtypes as $tid) {
                if (!isset($tables["clip_pubdata{$tid}"])) {
                    self::_addtable($tables, $tid, $tableColumnCore, $tableDefCore);
                }
            }
        }

        $serviceManager = ServiceUtil::getManager();
        $dbtables = $serviceManager['dbtables'];
        $serviceManager['dbtables'] = array_merge($dbtables, (array)$tables);
    }

    public static function createTempModel($tid)
    {
        static $tmpref = 0;
        $tmpref++;

        $tmpclass = "ClipModels_Pubdata{$tid}Tmp{$tmpref}";

        $path = ModUtil::getVar('Clip', 'modelspath');

        $files = array(
            "$path/Pubdata{$tid}.php",
            "$path/Pubdata{$tid}Table.php"
        );

        foreach ($files as $file) {
            // get file contents, rename the class and eval
            $code = file_get_contents($file);
            $code = str_replace('<?php', '', $code);
            $code = str_replace("class ClipModels_Pubdata{$tid}", "class $tmpclass", $code);
            eval($code);
        }

        return $tmpclass;
    }

    public static function deleteModel($tid, $type = 'Pubdata')
    {
        $path = ModUtil::getVar('Clip', 'modelspath');

        $files = array(
            "$path/$type{$tid}.php",
            "$path/$type{$tid}Table.php"
        );

        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    public static function updateModel($tid, $loadtables = false, $forcerels = false)
    {
        self::addtables($tid, $loadtables);

        $path = ModUtil::getVar('Clip', 'modelspath');

        $file = "$path/Pubdata{$tid}.php";
        $code = Clip_Generator::pubmodel($tid, $forcerels);
        file_put_contents($file, '<?php'.$code);

        $file = "$path/Pubdata{$tid}Table.php";
        $code = Clip_Generator::pubtable($tid);
        file_put_contents($file, '<?php'.$code);
    }

    public static function createModels()
    {
        // refresh the pubtypes definitions
        self::addtables();

        $pubtypes = Doctrine_Core::getTable('Clip_Model_Pubtype')->selectFieldArray('tid');

        foreach ($pubtypes as $tid) {
            self::updateModel($tid, false);
        }

        self::createRelationsModels();
    }

    public static function checkModels($force = false)
    {
        static $checked;

        if (!isset($checked) || $force) {
            $checked = true;

            $tid  = Clip_Util::getPubType()->getFirst()->tid;
            $path = ModUtil::getVar('Clip', 'modelspath');
            $file = $path."/Pubdata$tid.php";

            if (!is_dir($path)) {
                mkdir($path, System::getVar('system.chmod_dir', 0777), true);
            }

            if (!file_exists($file)) {
                self::createModels();
            }
        }
    }

    public static function resetModels()
    {
        $path   = ModUtil::getVar('Clip', 'modelspath');
        $models = FileUtil::getFiles($path, false, false);

        foreach ($models as $model) {
            if (is_file($model)) {
                if (!unlink($model)) {
                    return false;
                }
            }
        }

        return true;
    }
}
