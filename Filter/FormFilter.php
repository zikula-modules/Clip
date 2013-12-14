<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://github.com/zikula-modules/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Lib
 */

namespace Clip\Filter;

/**
 * Clip manager for Form Filters.
 */
class FormFilter
{
    const INPUTVAR = 'filter';

    public $id;
    // form id
    public $tid;
    // tid to filter
    public $filters = array();
    // list arguments
    public $plugins = array();
    // registered plugins
    public $obsform = array();
    // registered form observers
    public $obsflds = array();
    // registered fields observers
    /**
     * Constructor.
     *
     * @param array       $params Parameters passed from the Smarty plugin function.
     * @param Zikula_View $view   View instance.
     */
    public function __construct(&$params, Zikula_View $view)
    {
        $this->id = uniqid('cf');
        $this->tid = $view->getTplVar('pubtype')->tid;
        // extract the clip filters from the view
        $clipargs = $view->getTplVar('clipargs');
        $this->filters = $clipargs['getallapi']['filterform'];
    }
    
    /**
     * ID getter.
     *
     * @return string Form ID.
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * TID getter.
     *
     * @return string Form pubtype TID.
     */
    public function getTid()
    {
        return $this->tid;
    }
    
    /**
     * Filters getter.
     *
     * @return array Filter parameters.
     */
    public function getFilters()
    {
        return $this->filters;
    }
    
    /**
     * Filter conditional getter.
     *
     * @param string $field Field name to check on the top filter.
     * @param bool   $del   Whether to delete the top filter or not.
     *
     * @return array Filter parameters.
     */
    public function getFilter($field, $del = true)
    {
        $filter = array();
        do {
            $f = reset($this->filters);
            if ($field == $f['field'] && $del) {
                $filter[] = $f;
                array_shift($this->filters);
            }
        } while ($field == $f['field']);
        return $filter;
    }
    
    /**
     * Attach a plugin to the form.
     *
     * @param Clip_Filter_AbstractPlugin $plugin Filter name.
     *
     * @return void
     */
    public function addPlugin($plugin)
    {
        $this->plugins[$plugin->getField()][$plugin->getId()] = $plugin;
    }
    
    /**
     * Attach a form observer.
     *
     * @param string $code Function code.
     *
     * @return void
     */
    public function addFormObserver($code)
    {
        $this->obsform[] = $code;
    }
    
    /**
     * Attach a field observer to the form.
     *
     * @param string $field Field id.
     * @param string $code  Function code.
     *
     * @return void
     */
    public function addFieldObserver($field, $code)
    {
        $this->obsflds[] = array($field, $code);
    }
    
    /**
     * Form field ID getter.
     *
     * @param string $id Field ID.
     *
     * @return string ID for the field in the form.
     */
    public function getFieldID($id)
    {
        return $this->id . '_' . $id;
    }
    
    /**
     * Filter ID getter.
     *
     * @param string $field Field name.
     *
     * @return string ID for the filter.
     */
    public function getFilterId($field)
    {
        return $this->id . $this->getFilterName($field);
    }
    
    /**
     * Filter name getter.
     *
     * @param string $field Field name.
     *
     * @return string Filter name for the field.
     */
    public function getFilterName($field)
    {
        if (!isset($this->plugins[$field])) {
            $this->plugins[$field] = array();
        }
        return self::INPUTVAR . (array_search($field, array_keys($this->plugins)) + 1);
    }
    
    /**
     * Filters name getter.
     *
     * @return array Filter names of the form.
     */
    public function getFilterNames()
    {
        $filters = array();
        foreach (array_keys($this->plugins) as $k => $field) {
            $fname = self::INPUTVAR . ($k + 1);
            $filters[$this->id . $fname] = $fname;
        }
        return $filters;
    }
    
    /**
     * Plugin validator.
     *
     * @param string $field  Field name.
     * @param string $plugin Plugin name.
     *
     * @return boolean True if exists, false otherwise.
     */
    public function hasPlugin($field, $plugin)
    {
        return isset($this->plugins[$field][$plugin]);
    }
    
    /**
     * Form javascript generator.
     *
     * @return string Form script.
     */
    public function getFormScript()
    {
        $js = '<script type="text/javascript">' . '
';
        // show filter form for javascript enabled browsers
        $js .= "  \$('{$this->getId()}wrapper').show();\n";
        // add fields observers
        foreach ($this->obsflds as $k => $obs) {
            $fname = "{$this->id}_obs{$k}_{$obs[0]}";
            // build field function and observer
            $js .= "  function {$fname}() {\n{$obs[1]}\n}\n";
            $js .= "  \$('{$obs[0]}').observe('change', {$fname});\n";
            $js .= "  {$fname}();\n";
        }
        // add form observers
        foreach ($this->obsform as $k => $code) {
            $fname = "{$this->id}_obsform{$k}";
            // build field function and observer
            $js .= "  function {$fname}() {\n{$code}\n}\n";
            $js .= "  \$('{$this->id}form').observe('submit', {$fname});\n";
        }
        $js .= '</script>';
        return $js;
    }

}