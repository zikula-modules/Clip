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
 * Yes/No selector plugin.
 *
 * This plugin creates a yes/no selector using a drop down list.
 * The selected value of the base drop down list will be set to 1/ respectively
 */
class Clip_Filter_Plugin_YesNo extends Clip_Filter_Plugin_ListDropdown
{
    /**
     * Enable inclusion of an empty null value element.
     *
     * @var boolean (default true)
     */
    public $includeEmptyElement;

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
        $this->includeEmptyElement = (isset($params['includeEmptyElement']) ? $params['includeEmptyElement'] : true);

        if ($this->includeEmptyElement) {
            $this->addItem('', null);
        }
        $this->addItem(__('Yes'), 1);
        $this->addItem(__('No'), 0);

        parent::create($params, $filter);
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

        $code = "if (\$F('{$this->id}') != '#null#') { $('$filterid').value = '{$this->field}:eq:'+\$F('{$this->id}'); }";

        $filter->addFormObserver($code);

        return parent::render($view);
    }
}
