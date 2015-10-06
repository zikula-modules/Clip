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

/**
 * Base implementation for dropdown list.
 */
class ListBase extends AbstractPlugin
{
    /**
     * HTML input name for this plugin. Defaults to the ID of the plugin.
     *
     * @var string
     */
    public $inputName;
    /**
     * The list of selectable items.
     *
     * This is an array of arrays like this:
     * array( array('text' => 'A', 'value' => '1'),
     * array('text' => 'B', 'value' => '2'),
     * array('text' => 'C', 'value' => '3') )
     *
     * @var array
     */
    public $items = array();
    /**
     * Enable or disable read only mode.
     *
     * @var boolean
     */
    public $readOnly;
    /**
     * CSS class for styling.
     *
     * @var string
     */
    public $cssClass;
    /**
     * Enable or disable mandatory asterisk.
     *
     * @var boolean
     */
    public $mandatorysym;
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
     * @param FormFilter       $filter Clip filter form manager instance.
     *
     * @return void
     */
    public function create($params, $filter)
    {
        $this->inputName = $this->id;
        $this->readOnly = array_key_exists('readOnly', $params) ? $params['readOnly'] : false;
        $this->mandatorysym = array_key_exists('mandatorysym', $params) ? $params['mandatorysym'] : false;
        $this->itemsDataField = isset($params['itemsDataField']) ? $params['itemsDataField'] : $this->id . 'Items';
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
        $this->setSelectedValue(null);
        foreach ($filter->getFilter($this->field) as $args) {
            $this->setSelectedValue($args['value']);
        }
    }
    
    /**
     * Set the selected value.
     *
     * To be implemented by extending class.
     *
     * @param mixed $value Selected value.
     *
     * @return boolean
     */
    public function setSelectedValue($value)
    {
        return true;
    }
    
    /**
     * Get the selected value.
     *
     * To be implemented by extending class.
     *
     * @return mixed The selected value.
     */
    public function getSelectedValue()
    {
        return null;
    }
    
    /**
     * Add item to list.
     *
     * @param string $text  The text of the item.
     * @param string $value The value of the item.
     *
     * @return void
     */
    public function addItem($text, $value)
    {
        $item = array('text' => $text, 'value' => $value);
        $this->items[] = $item;
    }
    
    /**
     * Add several items to list.
     *
     * Quicker than copying the items one by one.
     * If addItem() does som special logic in the future then call that for each element in $items.
     *
     * @param array $items List of items.
     *
     * @return void
     */
    public function setItems($items)
    {
        $this->items = $items;
    }

}
