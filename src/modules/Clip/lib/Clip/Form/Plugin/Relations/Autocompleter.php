<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Form_Plugin
 */

/**
 * Plugin used to manage a relation with an autocompleter.
 */
class Clip_Form_Plugin_Relations_Autocompleter extends Clip_Form_Plugin_Relations_Text
{
    // custom plugin vars
    public $numitems;
    public $maxitems;
    public $minchars;
    public $op;

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

        $this->op = (isset($params['op']) && in_array($params['op'], array('search', 'likefirst', 'like'))) ? $params['op'] : 'search';
        $this->delimiter = ':';
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
                                                    func: "autocomplete",
                                                    tid: '.$this->relation['tid'].',
                                                    itemsperpage: '.$this->numitems.',
                                                    op: "'.$this->op.'"
                                                  },
                                                  minchars: '.$this->minchars.',
                                                  maxresults: '.$this->numitems.',
                                                  maxItems: '.($this->relation['single'] ? 1 : $this->maxitems).'
                                                 });
            }
            document.observe(\'dom:loaded\', clip_enable_'.$this->id.', false);
        '."\n// ]]>\n</script>";
        PageUtil::addVar('header', $script);

        // build the autocompleter output
        $typeDataHtml = '
        <div id="'.$this->id.'_div" class="z-auto-container">
            <div class="z-auto-default">'.
                (!empty($this->relation['descr']) ? $this->relation['descr'] : $this->_fn('Type the title of the related publication', 'Type the titles of the related publications', $this->relation['single'] ? 1 : 2, array())).
            '</div>
            <div class="z-auto-notfound">'.
                (!empty($this->relation['notfound']) ? $this->relation['notfound'] : $this->__('There are no matches found.')).
            '</div>
            <ul class="z-auto-feed">
                ';

        foreach ($this->reldata as $value => $title) {
            $typeDataHtml .= '<li value="'.$value.'">'.$title.'</li>';
        }
        $typeDataHtml .= '
            </ul>
        </div>';

        return $result . $typeDataHtml;
    }
}
