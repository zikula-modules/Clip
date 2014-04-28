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
 * Clip filter form string with extended options.
 *
 * Allows to search text in more than one pubfield with custom operators, Example:
 * <code>
 * {clip_filter_plugin p='StringExtended' id='core_title' addfields='summary,description' op='or'}
 * </code>
 */
class Clip_Filter_Plugin_StringExtended extends Clip_Filter_Plugin_String
{
    /**
     * Additional fields.
     *
     * @var string
     */
    public $addfields;

    /**
     * Operator for fields.
     *
     * @var string
     */
    public $opfields;

    /**
     * Operator.
     *
     * @var string
     */
    public $op;

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
        parent::create($params, $filter);

        $this->addfields = array_filter(array_merge(array($this->field), explode(',', (string)$this->addfields)));
        $this->opfields = (array_key_exists('opfields', $params) && in_array($params['opfields'], array('or', 'and')) ? $params['opfields'] : 'or');
        $this->op = (array_key_exists('op', $params) && in_array($params['op'], array('or', 'and')) ? $params['op'] : 'and');

        if ($this->maxLength == null && strtolower($this->textMode) == 'singleline') {
            $this->maxLength = 200;
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

        $fields = implode(',', $this->addfields);
        $opfields = ($this->opfields == 'and' ? ',' : '*');
        $op = ($this->op == 'and' ? ',' : '*');
        $code = "if (\$F('{$this->id}')) {
            var filters = [], filter = [], fields = '{$fields}';
            fields.split(',').each(function (f) {
                \$F('{$this->id}').split(' ').each(function (i) {
                    filter.push(f+':search:'+i);
                })
                filters.push(filter.join('{$op}'));
                filter = [];
            })
            $('$filterid').value = '('+filters.join(')$opfields(')+')';
        }";

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
}
