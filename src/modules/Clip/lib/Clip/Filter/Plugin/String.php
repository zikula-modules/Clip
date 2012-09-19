<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Filter_Plugin
 */

/**
 * Clip filter form string.
 *
 * General purpose input plugin that allows the user to enter any kind of character based data, Example:
 * <code>
 * {clip_filter_plugin p='String' id='core_title' maxLength='100' width='30em'}
 * </code>
 */
class Clip_Filter_Plugin_String extends Clip_Filter_Plugin_AbstractPlugin
{
    /**
     * HTML input name for this plugin. Defaults to the ID of the plugin.
     *
     * @var string
     */
    public $inputName;

    /**
     * Displayed text in the text input.
     *
     * This variable contains the text to be displayed in the input.
     * At first page display this variable contains whatever set in the template.
     *
     * @var string
     */
    public $text = '';

    /**
     * Text input mode.
     *
     * The text mode defines what kind of HTML element to render. The possible values are:
     * - <b>Singleline</b>: renders a normal input element (default).
     * - <b>Hidden</b>: renders a input element of type "hidden".
     *
     * @var string
     */
    public $textMode = 'singleline';

    /**
     * Enable or disable read only mode.
     *
     * A text input in read only cannot receive anything.
     *
     * @var boolean
     */
    public $readOnly;

    /**
     * CSS class to use.
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
     * Text to show as tool tip for the input.
     *
     * @var string
     */
    public $toolTip;

    /**
     * Size of HTML input (number of characters).
     *
     * @var integer
     */
    public $size;

    /**
     * Maximum number of characters allowed in the text input.
     *
     * @var integer
     */
    public $maxLength;

    /**
     * Get filename for this plugin.
     *
     * A requirement from the framework - must be implemented like this. Used to restore plugins on postback.
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
     * @param array            $params Parameters passed from the Smarty plugin function.
     * @param Clip_Filter_Form $filter Clip filter form manager instance.
     *
     * @see    Zikula_Form_AbstractPlugin
     * @return void
     */
    public function create($params, $filter)
    {
        // All member variables are fetched automatically before create (as strings)
        // Here we afterwards load all special and non-string parameters
        $this->inputName = (array_key_exists('inputName', $params) ? $params['inputName'] : $filter->getFilterName($this->field));
        $this->textMode  = (array_key_exists('textMode', $params) ? $params['textMode'] : 'singleline');
        $this->op        = (array_key_exists('op', $params) ? $params['op'] : 'search');

        if ($this->maxLength == null && strtolower($this->textMode) == 'singleline') {
            $this->maxLength = 100;
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
        $this->text = '';

        foreach ($filter->getFilter($this->field) as $args) {
            $this->text .= ($this->text ? ':' : '') . $this->formatValue($args['value']);
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
        // adds the form observer
        $filter   = $view->get_registered_object('clip_filter');
        $filterid = $filter->getFilterID($this->field);

        if ($filter->hasPlugin($this->field, $this->id.'_op')) {
            $code = "$('$filterid').value = '{$this->field}:'+\$F('{$this->id}_op')+':'+\$F('{$this->id}');";
        } else {
            $code = "$('$filterid').value = '{$this->field}:{$this->op}:'+\$F('{$this->id}');";
        }
        $code = "if (\$F('{$this->id}')) { $code }";

        $filter->addFormObserver($code);

        // build the text input
        $idHtml = $this->getIdHtml();
        $nameHtml = " name=\"{$this->inputName}\"";
        $titleHtml = ($this->toolTip != null ? ' title="' . $view->translateForDisplay($this->toolTip) . '"' : '');
        $readOnlyHtml = ($this->readOnly ? ' readonly="readonly" tabindex="-1"' : '');
        $sizeHtml = ($this->size > 0 ? " size=\"{$this->size}\"" : '');
        $maxLengthHtml = ($this->maxLength > 0 ? " maxlength=\"{$this->maxLength}\"" : '');
        $text = DataUtil::formatForDisplay($this->text);
        $class = $this->getStyleClass();

        $attributes = $this->renderAttributes($view);

        switch (strtolower($this->textMode)) {
            case 'singleline':
                $result = "<input type=\"text\"{$idHtml}{$nameHtml}{$titleHtml}{$sizeHtml}{$maxLengthHtml}{$readOnlyHtml} class=\"{$class}\" value=\"{$text}\"{$attributes} />";
                if ($this->mandatorysym) {
                    $result .= '<span class="z-form-mandatory-flag">*</span>';
                }
                break;

            case 'hidden':
                $result = "<input type=\"hidden\"{$idHtml}{$nameHtml} class=\"{$class}\" value=\"{$text}\" />";
                break;

            default:
                $result = __f('Unknown value [%1$s] for \'%2$s\'.', array($this->textMode, 'textMode'));
        }

        return $result;
    }

    /**
     * Helper method to determine css class.
     *
     * Can be overridden by subclasses like Zikula_Form_Plugin_IntInput and Zikula_Form_Plugin_FloatInput.
     *
     * @return string the list of css classes to apply
     */
    protected function getStyleClass()
    {
        $class = 'z-form-text';

        if ($this->mandatorysym) {
            $class .= ' z-form-mandatory';
        }
        if ($this->readOnly) {
            $class .= ' z-form-readonly';
        }
        if ($this->cssClass != null) {
            $class .= ' ' . $this->cssClass;
        }

        return $class;
    }

    /**
     * Indicates whether or not the input is empty.
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return $this->text == '';
    }

    /**
     * Parses a value.
     *
     * Override this function in inherited plugins if other format is needed.
     *
     * @param string $text Text.
     *
     * @return string Parsed Text.
     */
    public function parseValue($text)
    {
        return $text;
    }

    /**
     * Format the value to specific format.
     *
     * Override this function in inherited plugins if other format is needed.
     *
     * @param string $value The value to format.
     *
     * @return string Formatted value.
     */
    public function formatValue($value)
    {
        return $value;
    }
}
