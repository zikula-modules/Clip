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

/**
 * List drop down plugin.
 *
 * Renders an HTML <select> element with the supplied items.
 */
class Clip_Filter_Plugin_ListDropdown extends Clip_Filter_Plugin_ListBase
{
    /**
     * Selection mode.
     *
     * Sets selection mode to either single item selection (standard dropdown) or
     * multiple item selection.
     *
     * @var string Possible values are 'single' and 'multiple'
     */
    public $selectionMode = 'single';

    /**
     * Selected value.
     *
     * Selected value is an array of values if you have set selectionMode=multiple.
     *
     * @var mixed
     */
    public $selectedValue;

    /**
     * Selected item index.
     *
     * Select index is not valid when selectionMode=multiple.
     *
     * @var integer Zero based index or null
     */
    public $selectedIndex;

    /**
     * Size of dropdown.
     *
     * This corresponds to the "size" attribute of the HTML <select> element.
     *
     * @var integer
     */
    public $size = null;

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
        parent::create($params, $filter);

        $this->selectedIndex = -1;
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
        parent::load($params, $filter);

        if (is_null($this->getSelectedValue())) {
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
    public function render(Zikula_View $view)
    {
        $idHtml = $this->getIdHtml();

        $nameHtml = " name=\"{$this->inputName}[]\"";

        $readOnlyHtml = ($this->readOnly ? " disabled=\"disabled\"" : '');

        $class = 'z-form-dropdownlist';
        if ($this->mandatorysym) {
            $class .= ' z-form-mandatory';
        }
        if ($this->readOnly) {
            $class .= ' z-form-readonly';
        }
        if ($this->cssClass != null) {
            $class .= ' ' . $this->cssClass;
        }

        $classHtml = ($class == '' ? '' : " class=\"{$class}\"");

        $sizeHtml = ($this->size == null ? '' : " size=\"{$this->size}\"");

        $multipleHtml = '';
        if ($this->selectionMode == 'multiple') {
            $multipleHtml = " multiple=\"multiple\"";
        }

        $attributes = $this->renderAttributes($view);

        $result = "<select{$idHtml}{$nameHtml}{$readOnlyHtml}{$classHtml}{$multipleHtml}{$sizeHtml}{$attributes}>\n";
        $currentOptGroup = null;
        foreach ($this->items as $item) {
            $optgroup = (isset($item['optgroup']) ? $item['optgroup'] : null);
            if ($optgroup != $currentOptGroup) {
                if ($currentOptGroup != null) {
                    $result .= "</optgroup>\n";
                }
                if ($optgroup != null) {
                    $result .= "<optgroup label=\"" . DataUtil::formatForDisplay($optgroup) . "\">\n";
                }
                $currentOptGroup = $optgroup;
            }

            $text = DataUtil::formatForDisplay($item['text']);

            if ($item['value'] === null) {
                $value = '#null#';
            } else {
                $value = DataUtil::formatForDisplay($item['value']);
            }

            if ($this->selectionMode == 'single' && $value == $this->selectedValue) {
                $selected = ' selected="selected"';
            } elseif ($this->selectionMode == 'multiple' && in_array($value, (array)$this->selectedValue)) {
                $selected = ' selected="selected"';
            } else {
                $selected = '';
            }
            $result .= "<option value=\"{$value}\"{$selected}>{$text}</option>\n";
        }
        if ($currentOptGroup != null) {
            $result .= "</optgroup>\n";
        }
        $result .= "</select>\n";
        if ($this->mandatorysym) {
            $result .= '<span class="z-form-mandatory-flag">*</span>';
        }

        return $result;
    }

    /**
     * Set the selected value.
     *
     * @param mixed $value Selected value.
     *
     * @return void
     */
    public function setSelectedValue($value)
    {
        if ($this->selectionMode == 'single') {
            // Check for exiting value in list (avoid tampering with post values)
            for ($i = 0, $count = count($this->items); $i < $count; ++$i) {
                $item = &$this->items[$i];

                if ($item['value'] == $value) {
                    $this->selectedValue = $value;
                    $this->selectedIndex = $i;
                }
            }
        } else {
            if (is_string($value)) {
                $value = explode(':', $value);
            }

            $ok = true;
            for ($j = 0, $jcount = count($value); $j < $jcount; ++$j) {
                $ok2 = false;
                // Check for exiting value in list (avoid tampering with post values)
                for ($i = 0, $icount = count($this->items); $i < $icount; ++$i) {
                    $item = &$this->items[$i];

                    if ($item['value'] == $value[$j]) {
                        $ok2 = true;
                        break;
                    }
                }
                $ok = $ok && $ok2;
            }

            if ($ok) {
                $this->selectedValue = $value;
                $this->selectedIndex = 0;
            }
        }
    }

    /**
     * Get the selected value.
     *
     * @return mixed The selected value.
     */
    public function getSelectedValue()
    {
        return $this->selectedValue;
    }

    /**
     * Set the selected item by index.
     *
     * @param int $index Selected index.
     *
     * @return void
     */
    public function setSelectedIndex($index)
    {
        if ($index >= 0 && $index < count($this->items)) {
            $this->selectedValue = $this->items[$index]['value'];
            $this->selectedIndex = $index;
        }
    }

    /**
     * Get the selected index.
     *
     * @return integer The selected index.
     */
    public function getSelectedIndex()
    {
        return $this->selectedIndex;
    }
}
