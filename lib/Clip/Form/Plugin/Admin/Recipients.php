<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://github.com/zikula-modules/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Form_Plugin
 */

/**
 * Plugin used to manage a relation with an autocompleter.
 */
class Clip_Form_Plugin_Admin_Recipients extends Zikula_Form_Plugin_TextInput
{
    // custom plugin vars
    public $numitems;
    public $maxitems;
    public $minchars;
    public $delimiter = ':';

    // data vars
    public $groups = array();
    public $users  = array();

    /**
     * Get filename for this plugin.
     *
     * @internal
     * @return string
     */
    public function getFilename()
    {
        return __FILE__;
    }

    /**
     * Create event handler.
     *
     * @param Zikula_Form_View $view    Reference to Zikula_Form_View object.
     * @param array            &$params Parameters passed from the Smarty plugin function.
     *
     * @see    Zikula_Form_AbstractPlugin
     * @return void
     */
    public function create($view, &$params)
    {
        $params['textMode'] = 'hidden';

        parent::create($view, $params);

        $this->numitems = (isset($params['numitems']) && is_int($params['numitems'])) ? abs($params['numitems']) : 30;
        $this->maxitems = (isset($params['maxitems']) && is_int($params['maxitems'])) ? abs($params['maxitems']) : 20;
        $this->minchars = (isset($params['minchars']) && is_int($params['minchars'])) ? abs($params['minchars']) : 2;
    }

    /**
     * Load values.
     *
     * Called internally by the plugin itself to load values from the render.
     * Can also by called when some one is calling the render object's Zikula_Form_ViewetValues.
     *
     * @param Zikula_Form_View $view    Reference to Zikula_Form_View object.
     * @param array            &$values Values to load.
     *
     * @return void
     */
    public function loadValue(Zikula_Form_View $view, &$values)
    {
        if ($this->dataBased) {
            $value = array_key_exists($this->id, $values[$this->group]) ? (array)$values[$this->group][$this->id] : array();

            // update the input value
            if (!$this->text) {
                $this->text = implode($this->delimiter, $value);
            }

            // process the plugin data
            foreach ($value as $val) {
                $id = substr($val, 1);

                switch (substr($val, 0, 1))
                {
                    case 'g':
                        $this->groups[] = $id;
                        break;

                    case 'u':
                        $this->users[] = $id;
                        break;
                }
            }

            // fetch the groups and user names
            $tables = DBUtil::getTables();

            if (!empty($this->groups)) {
                $grpColumn = $tables['groups_column'];

                $ids = implode(',', $this->groups);
                $where = "WHERE {$grpColumn['gid']} IN ($ids)";

                $this->groups = DBUtil::selectFieldArray('groups', 'name', $where, $grpColumn['name'], false, 'gid');
            }

            if (!empty($this->users)) {
                $usersColumn = $tables['users_column'];

                $ids = implode(',', $this->users);
                $where = "WHERE {$usersColumn['uid']} IN ($ids)";

                $this->users = DBUtil::selectFieldArray('users', 'uname', $where, $usersColumn['uname'], false, 'uid');
            }
        }
    }

    /**
     * Saves value in data object.
     *
     * Called by the render when doing $view->getValues()
     * Uses the group parameter to decide where to store data.
     *
     * @param Zikula_Form_View $view  Reference to Zikula_Form_View object.
     * @param array            &$data Data object.
     *
     * @return void
     */
    public function saveValue($view, &$data)
    {
        if ($this->dataBased) {
            $data[$this->group][$this->id] = explode($this->delimiter, $this->text);
        }
    }

    /**
     * Render event handler.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     *
     * @return string The rendered output
     */
    public function render($view)
    {
        $result = parent::render($view);

        // build the autocompleter setup
        PageUtil::addVar('javascript', 'prototype');
        PageUtil::addVar('javascript', 'modules/Clip/javascript/Zikula.Autocompleter.js');
        $script =
            "<script type=\"text/javascript\">\n// <![CDATA[\n".'
            function clip_enable_'.$this->id.'() {
                var_auto_'.$this->id.' = new Zikula.Autocompleter(\''.$this->id.'\', \''.$this->id.'_div\',
                                                 {
                                                  fetchFile: Zikula.Config.baseURL+\'ajax.php?lang=\'+Zikula.Config.lang,
                                                  parameters: {
                                                    module: "Clip",
                                                    type: "ajaxdata",
                                                    func: "recipients"
                                                  },
                                                  minchars: '.$this->minchars.',
                                                  maxresults: '.$this->numitems.',
                                                  maxItems: '.$this->maxitems.'
                                                 });
            }
            clip_enable_'.$this->id.'();
        '."\n// ]]>\n</script>";

        // build the autocompleter output
        $typeDataHtml = '
        <div id="'.$this->id.'_div" class="z-auto-container">
            <div class="z-auto-default">'.
            $this->__('Type the user or group names recipients').
            '</div>
            <div class="z-auto-notfound">'.
            $this->__('There are no matches found.').
            '</div>
            <ul class="z-auto-feed">
                ';

        foreach ($this->groups as $value => $title) {
            $typeDataHtml .= '<li value="'.$value.'">'.$this->__f('%s (Group)', DataUtil::formatForDisplay($title)).'</li>';
        }
        foreach ($this->users as $value => $title) {
            $typeDataHtml .= '<li value="'.$value.'">'.DataUtil::formatForDisplay($title).'</li>';
        }
        $typeDataHtml .= '
            </ul>
        </div>';

        return $result . $typeDataHtml . $script;
    }

    /**
     * postRead handler.
     *
     * @param mixed $data Data to process.
     *
     * @return mixed The processed data.
     */
    public static function postRead($data)
    {
        // default
        $v = array(
            'groups' => array(),
            'users'  => array()
        );

        // assign existing data
        foreach ($data as $val) {
            $id = substr($val, 1);

            switch (substr($val, 0, 1))
            {
                case 'g':
                    $v['groups'][] = $id;
                    break;

                case 'u':
                    $v['users'][] = $id;
                    break;
            }
        }

        $tables = DBUtil::getTables();

        // externally we need the email list
        if (!empty($v['groups'])) {
            $membershipColumn = $tables['group_membership_column'];

            // get all the users in the groups
            $ids = implode(',', $v['groups']);
            $where = "WHERE $membershipColumn[gid] IN ($ids)";

            $users = DBUtil::selectFieldArray('group_membership', 'uid', $where, '', true);

            $v['users'] = array_merge($v['users'], $users);
        }

        // fetch the user emails
        $ids = implode(',', array_unique($v['users']));

        if ($ids) {
            $usersColumn = $tables['users_column'];
            $where = "WHERE {$usersColumn['uid']} IN ($ids)";

            return DBUtil::selectFieldArray('users', 'email', $where, '', true);
        }

        return array();
    }
}
