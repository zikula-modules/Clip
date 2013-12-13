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

namespace Clip\Filter\Plugin;


/**
 * List category selector plugin.
 *
 * This plugin creates a category selector using a drop down list.
 * The selected value of the base drop down list will be set to ID of the selected category.
 */
class OpString extends \Clip_Filter_Plugin_ListDropdown
{
    /**
     * Enabled operators.
     *
     * Comma separated list of enabled operators.
     *
     * @var string
     */
    public $enabled;
    /**
     * Supported operators.
     *
     * @var array
     */
    public $ops;
    /**
     * Get filename of this file.
     *
     * @return string
     */
    public function getFilename()
    {
        return __FILE__;
    }
    
    /**
     * Create event handler.
     *
     * @param array            $params Parameters passed from the Smarty plugin function.
     * @param Clip_Filter_Form $filter Clip filter form manager instance.
     *
     * @return void
     */
    public function create($params, $filter)
    {
        $this->ops = array('eq' => $this->__('is equal to'), 'ne' => $this->__('is different to'), 'search' => $this->__('contains'), 'like' => $this->__('is like'), 'likefirst' => $this->__('begins with'), 'null' => $this->__('is empty'), 'notnull' => $this->__('is not empty'));
        if (isset($params['enabled'])) {
            $this->enabled = explode(',', $params['enabled']);
            $this->enabled = array_intersect($this->enabled, array_keys($this->ops));
            if ($this->enabled) {
                // valid values, if not use the full list of operators
                foreach (array_keys($this->ops) as $k) {
                    if (!in_array($k, $this->enabled)) {
                        unset($this->ops[$k]);
                    }
                }
                // sort the ops in the order passed in the enabled array
                $this->ops = array_merge(array_combine($this->enabled, $this->enabled), $this->ops);
            }
        }
        parent::create($params, $filter);
        $this->inputName = $filter->getFilterName($this->field);
        foreach ($this->ops as $value => $text) {
            $this->addItem($text, $value);
        }
    }
    
    /**
     * Load event handler.
     *
     * @param array            $params Parameters passed from the Smarty plugin function.
     * @param Clip_Filter_Form $filter Clip filter form manager instance.
     *
     * @return void
     */
    public function load($params, $filter)
    {
        $this->setSelectedValue(null);
        if ($args = $filter->getFilter($this->field, false)) {
            $this->setSelectedValue($args['op']);
        } else {
            // if someone decided to set selected value from the template then try to "set it for real"
            // (meaning: set also selected Index) - after the items, potentially, have been loaded.
            if (array_key_exists('selectedValue', $params)) {
                $this->setSelectedValue($params['selectedValue']);
            }
            if (array_key_exists('selectedIndex', $params)) {
                $this->setSelectedIndex($params['selectedIndex']);
            }
        }
    }
    
    /**
     * Render event handler.
     *
     * @param Zikula_View $view Reference to Zikula_View object.
     *
     * @return string The rendered output
     */
    public function render(Zikula_View &$view)
    {
        if (in_array('null', array_keys($this->ops)) || in_array('notnull', array_keys($this->ops))) {
            $filter = $view->get_registered_object('clip_filter');
            $field = $filter->getFieldID($this->field);
            // add an observer for null and notnull options
            $function = "if (\$F('{$this->id}') == 'null' || \$F('{$this->id}') == 'notnull')\r\n                         { \$('{$field}').hide(); } else { \$('{$field}').show(); }";
            $filter->addFieldObserver($this->id, $function);
        }
        return parent::render($view);
    }

}