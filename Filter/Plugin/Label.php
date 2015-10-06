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
use ZLanguage;

/**
 * Clip filter form label.
 *
 * Use this to create labels for the input fields in a filter form. Example:
 * <code>
 * {clip_filter_plugin p='Label' id='title' __text='Title'}:
 * {clip_filter_plugin p='String' id='title'}
 * </code>
 * The rendered output is an HTML label element with the "for" value
 * set to the supplied id.
 */
class Label extends AbstractPlugin
{
    /**
     * Text to show as label.
     *
     * @var string
     */
    public $text;
    /**
     * Labelled plugin's ID.
     *
     * @var string
     */
    public $for;
    /**
     * Enable or disable the mandatory asterisk.
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
        $this->setDomain(ZLanguage::getModuleDomain('Clip'));
        $this->for = $filter->getFieldID($this->for);
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
        $attrs = $this->renderAttributes();
        $output = "<label for=\"{$this->for}\"{$attrs}>{$this->text}";
        if ($this->mandatorysym) {
            $output .= '<span class="z-form-mandatory-flag">*</span>';
        }
        $output .= '</label>';
        return $output;
    }

}
