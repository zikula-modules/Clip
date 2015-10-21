<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://github.com/zikula-modules/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Filter_Plugin
 */

namespace Matheo\Clip\Filter\Plugin;

use Matheo\Clip\Filter\FormFilter;
use PageUtil;
use DataUtil;
use ModUtil;
use DBUtil;

/**
 * Clip filter form author.
 *
 * General purpose input plugin that allows the user to search an user, Example:
 * <code>
 * {clip_filter_plugin p='Author' id='core_author'}
 * </code>
 */
class Author extends String
{
    // plugin custom vars
    public $operator;
    public $multiple;
    public $numitems;
    public $maxitems;
    public $minchars;
    public $autotip;

    public function getFilename()
    {
        return __FILE__;
    }
    
    /**
     * Read Smarty plugin parameters.
     *
     * @param array $params Parameters passed from the Smarty plugin function.
     *
     * @return void
     */
    public function readParameters($params)
    {
        $params['width'] = isset($params['width']) ? $params['width'] : (!isset($params['multiple']) || $params['multiple'] ? '30em' : '20em');
        parent::readParameters($params);
    }
    
    /**
     * Create event handler.
     *
     * @param array            $params Parameters passed from the Smarty plugin function.
     * @param FormFilter       $filter Clip filter form manager instance.
     *
     * @see    Zikula_Form_AbstractPlugin
     * @return void
     */
    public function create($params, $filter)
    {
        parent::create($params, $filter);
        $this->operator = isset($params['operator']) ? $params['operator'] : 'search';
        $this->multiple = isset($params['multiple']) ? (bool) $params['multiple'] : true;
        $this->numitems = isset($params['numitems']) && is_int($params['numitems']) ? abs($params['numitems']) : 10;
        $this->maxitems = isset($params['maxitems']) && is_int($params['maxitems']) ? abs($params['maxitems']) : 3;
        $this->minchars = isset($params['minchars']) && is_int($params['minchars']) ? abs($params['minchars']) : 3;
        // TODO FIXME for multiple users inside a : separated string
        $this->op = isset($params['op']) ? $params['op'] : 'eq';
    }
    
    /**
     * Load event handler.
     *
     * @param array            $params Parameters passed from the Smarty plugin function.
     * @param FormFilter       $filter Clip filter form manager instance.
     *
     * @return void
     */
    public function load($params, $filter)
    {
        $this->text = '';
        foreach ($filter->getFilter($this->field) as $args) {
            $this->text .= ($this->text ? ':' : '') . $this->formatValue($args['value']);
        }
    }
    
    /**
     * Render event handler.
     *
     * @param \Zikula_View $view Reference to Zikula_View object.
     *
     * @return string The rendered output
     */
    public function render(\Zikula_View $view)
    {
        // build the autocompleter setup
        PageUtil::addVar('javascript', 'prototype');
        PageUtil::addVar('javascript', 'modules/Clip/javascript/Zikula.Autocompleter.js');
        $script = '<script type="text/javascript">
// <![CDATA[
' . '
            function clip_enable_' . $this->id . '() {
                var_auto_' . $this->id . ' = new Zikula.Autocompleter(\'' . $this->id . '\', \'' . $this->id . '_div\',
                                                 {
                                                  fetchFile: Zikula.Config.baseURL+\'ajax.php\',
                                                  parameters: {
                                                    module: "Clip",
                                                    type: "ajaxdata",
                                                    func: "getusers",
                                                    op: "' . $this->operator . '"
                                                  },
                                                  minchars: ' . $this->minchars . ',
                                                  maxresults: ' . $this->numitems . ',
                                                  maxItems: ' . ($this->multiple ? $this->maxitems : 1) . '
                                                 });
            }
            document.observe(\'dom:loaded\', clip_enable_' . $this->id . ', false);
        ' . '
// ]]>
</script>';
        PageUtil::addVar('header', $script);
        // adds the form observer
        $filter = $view->get_registered_object('clip_filter');
        $filterid = $filter->getFilterID($this->field);
        $code = "\$('{$filterid}').value = '{$this->field}:{$this->op}:'+\$F('{$this->id}').split(':').join('*{$this->field}:{$this->op}:');";
        $code = "if (\$F('{$this->id}')) { {$code} }";
        $filter->addFormObserver($code);
        // build the autocompleter output
        $output = '
        <div class="z-auto-filterwrap">
            <input type="hidden"' . $this->getIdHtml() . ' name="' . $this->inputName . '" class="' . $this->getStyleClass() . '" value="' . DataUtil::formatForDisplay($this->text) . '" />
            <div id="' . $this->id . '_div" class="z-auto-container" style="display: none">
                <div class="z-auto-default">' . (!empty($this->autotip) ? $this->autotip : $this->_fn(
            'Type the username',
            'Type the usernames',
            $this->multiple ? 2 : 1,
            array()
        )) . '</div>
                <ul class="z-auto-feed">
                    ';
        foreach ($this->getUsernames() as $uid => $uname) {
            $output .= '<li value="' . $uid . '">' . $uname . '</li>';
        }
        $output .= '
                </ul>
            </div>
        </div>';
        return $output;
    }
    
    public function getUsernames()
    {
        $unames = array();
        if (!empty($this->text)) {
            $data = explode(':', $this->text);
            // check if there's the annonymous ID (0)
            if ($pos = array_search(0, $data)) {
                $unames[0] = $this->__('Annonymous');
                unset($data[$pos]);
            }
            if (!empty($data)) {
                ModUtil::dbInfoLoad('Users');
                $tables = DBUtil::getTables();
                $usersColumn = $tables['users_column'];
                $where = 'WHERE ' . $usersColumn['uid'] . ' IN (\'' . implode('\', \'', $data) . '\')';
                $results = DBUtil::selectFieldArray('users', 'uname', $where, $usersColumn['uname'], false, 'uid');
                if ($results) {
                    foreach ($results as $uid => $uname) {
                        $unames[$uid] = $uname;
                    }
                }
            }
        }
        return $unames;
    }

}
