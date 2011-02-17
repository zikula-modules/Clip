<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Form_Plugin
 */

class Clip_Form_Plugin_User extends Zikula_Form_Plugin_TextInput
{
    public $pluginTitle;
    public $columnDef   = 'I4';
    public $filterClass = 'clipuser';

    public $config = array();

    public $numitems;
    public $maxitems;
    public $minchars;
    public $autotip;

    function setup()
    {
        $this->setDomain(ZLanguage::getModuleDomain('Clip'));

        //! field type name
        $this->pluginTitle = $this->__('User');
    }

    function getFilename()
    {
        return __FILE__;
    }

    /**
     * Form Framework methods.
     */
    function create($view, &$params)
    {
        parent::create($view, $params);

        $this->numitems = (isset($params['numitems']) && is_int($params['numitems'])) ? abs($params['numitems']) : 30;
        $this->maxitems = (isset($params['maxitems']) && is_int($params['maxitems'])) ? abs($params['maxitems']) : 20;
        $this->minchars = (isset($params['minchars']) && is_int($params['minchars'])) ? abs($params['minchars']) : 3;
    }

    function readParameters($view, &$params)
    {
        $this->parseConfig($view->eventHandler->getPubfieldData($params['id'], 'typedata'));

        parent::readParameters($view, $params);
    }

    function render($view)
    {
        $this->textMode = 'hidden';

        $result = parent::render($view);

        // build the autocompleter setup
        PageUtil::addVar('javascript', 'prototype');
        PageUtil::addVar('javascript', 'modules/Clip/javascript/Zikula.Autocompleter.js');
        $script =
        "<script type=\"text/javascript\">\n// <![CDATA[\n".'
            function clip_enable_'.$this->id.'() {
                var_auto_'.$this->id.' = new Zikula.Autocompleter(\''.$this->id.'\', \''.$this->id.'_div\',
                                                 {
                                                  fetchFile: Zikula.Config.baseURL+\'ajax.php\',
                                                  parameters: {
                                                    module: "Clip",
                                                    func: "getusers",
                                                    op: "'.$this->config['operator'].'"
                                                  },
                                                  minchars: '.$this->minchars.',
                                                  maxresults: '.$this->numitems.',
                                                  maxItems: '.($this->config['multiple'] ? $this->maxitems : 1).'
                                                 });
            }
            Event.observe(window, \'load\', clip_enable_'.$this->id.', false);
        '."\n// ]]>\n</script>";
        PageUtil::addVar('rawtext', $script);

        // build the autocompleter output
        $typeDataHtml = '
        <div id="'.$this->id.'_div" class="z-auto-container">
            <div class="z-auto-default">'.
                (!empty($this->autotip) ? $this->autotip : $this->_fn('Type the username', 'Type the usernames', $this->config['multiple'] ? 2 : 1, array())).
            '</div>
            <ul class="z-auto-feed">
                ';
        $data = self::postRead($view->_tpl_vars['pubdata'][$this->id], null);

        foreach ($data as $uid => $uname) {
            $typeDataHtml .= '<li value="'.$uid.'">'.$uname.'</li>';
        }
        $typeDataHtml .= '
            </ul>
        </div>';

        return $result . $typeDataHtml;
    }


    function saveValue($view, &$data)
    {
        if ($this->dataBased) {
            $value = $this->parseValue($view, $this->text);

            if ($this->group == null) {
                $data[$this->dataField] = $value;
            } else {
                if (!array_key_exists($this->group, $data)) {
                    $data[$this->group] = array();
                }
                $data[$this->group][$this->dataField] = $value;
            }
        }
    }

    /**
     * Clip processing methods.
     */
    static function postRead($data, $field)
    {
        // this plugin return an array
        $uids = array();

        // if there's a value index the username(s)
        if (!empty($data)) {
            $data = array_filter(explode(':', $data));

            ModUtil::dbInfoLoad('Users');
            $tables = DBUtil::getTables();

            $usersColumn = $tables['users_column'];

            $where = 'WHERE ' . $usersColumn['uid'] . ' IN (\'' . implode('\', \'', $data) . '\')';
            $results = DBUtil::selectFieldArray('users', 'uname', $where, $usersColumn['uname'], false, 'uid');

            if (!$results) {
                return $uids;
            }

            foreach ($results as $uid => $uname) {
                $uids[$uid] = $uname;
            }
        }

        return $uids;
    }

    function getPluginOutput($field)
    {
        $this->parseConfig($field['typedata']);

        $body = "\n".
            '                {foreach from=$pubdata.'.$field['name'].' key=\'pubuid\' item=\'pubuname\'}'."\n".
            '                    {$pubuname|userprofilelink}'."\n".
            '                    <span class="z-sub">[{$pubuid|safehtml}]</span><br />'."\n".
            '                {/foreach}'."\n".
            '            ';

        return array('body' => $body);
    }

    static function getPluginEdit($field)
    {
        return " minchars='3' numitems='30'";
    }

    /**
     * Clip admin methods.
     */
    static function getSaveTypeDataFunc($field)
    {
        $saveTypeDataFunc = 'function saveTypeData()
                             {
                                 if ($(\'clipplugin_multiple\') && $F(\'clipplugin_multiple\') == \'on\') {
                                     $(\'typedata\').value = 1;
                                 } else {
                                     $(\'typedata\').value = 0;
                                 }
                                 $(\'typedata\').value += \'|\';
                                 if ($F(\'clipplugin_operator\') != null) {
                                     $(\'typedata\').value += $F(\'clipplugin_operator\');
                                 } else {
                                     $(\'typedata\').value += \'likefirst\';
                                 }
                                 closeTypeData();
                             }';

        return $saveTypeDataFunc;
    }

    function getTypeHtml($field, $view)
    {
        $this->parseConfig($view->_tpl_vars['field']['typedata']);

        // single or multiple
        $checked = $this->config['multiple'] ? 'checked="checked"' : '';
        $html = '<div class="z-formrow">
                     <label for="clipplugin_multiple">'.$this->__('Multiple Users?').'</label>
                     <input type="checkbox" id="clipplugin_multiple" name="clipplugin_multiple" '.$checked.' />
                 </div>';

        // operator to use
        $operators = array(
            'likefirst' => $this->__('in the beggining'),
            'search'    => $this->__('inside the username'),
        );
        $html .= '<div class="z-formrow">
                      <label for="clipplugin_operator">'.$this->__('Search').'</label>
                      <select id="clipplugin_operator" name="clipplugin_operator">';

        foreach ($operators as $op => $output) {
            $selected = ($this->config['operator'] == $op) ? ' selected="selected"' : '';

            $html .= "    <option{$selected} value=\"{$op}\">{$output}</option>";
        }

        $html .= '    </select>
                  </div>';

        return $html;
    }

    /**
     * Parse configuration
     */
    function parseConfig($typedata='')
    {
        // config: "{(bool)usescribite, (string)editor}"
        $typedata = explode('|', $typedata);

        $this->config = array(
            'multiple' => $typedata[0] !== '' ? (bool)$typedata[0] : false,
            'operator' => isset($typedata[1]) ? $typedata[1] : 'likefirst'
        );
    }
}
