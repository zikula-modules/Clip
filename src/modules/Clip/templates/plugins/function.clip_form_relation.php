<?php
/**
 * PageMaster
 *
 * @copyright   (c) PageMaster Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package     Zikula_3rdParty_Modules
 * @subpackage  pagemaster
 */

/**
 * Relation selector form plugin.
 *
 * @param $params['fieldname']
 * @param generic
 */
function smarty_function_clip_form_relation($params, &$view) {
    return $view->registerPlugin('ClipFormRelation', $params);
}

class ClipFormRelation extends Form_Plugin_TextInput
{
    public $relinfo;

    /**
     * Get filename for this plugin.
     *
     * @internal
     * @return string
     */
    function getFilename()
    {
        return __FILE__;
    }

    /**
     * Constructor.
     *
     * @param Form_View $view    Reference to Form_View object.
     * @param array     &$params Parameters passed from the Smarty plugin function.
     */
    function __construct($view, &$params)
    {
        if (isset($params['relation'])) {
            $this->relinfo = $params['relation'];
        }

        $params['textMode'] = 'hidden';

        parent::__construct($view, $params);
    }

    /**
     * Render event handler.
     *
     * @param Form_View $view Reference to Form_View object.
     *
     * @return string The rendered output
     */
    function render($view)
    {
        $result = parent::render($view);

        // config values
        $numitems = 30;
        $maxitems = 20;
        $minchars = 2;

        $count = $this->relinfo['own'] ? ($this->relinfo['type']%2 == 0 ? 1 : 2) : ($this->relinfo['type'] <= 1 ? 1 : 2);

        // build the autocompleter setup
        PageUtil::addVar('javascript', 'prototype');
        PageUtil::addVar('javascript', 'modules/PageMaster/javascript/facebooklist.js');
        $script =
        "<script type=\"text/javascript\">\n//<![CDATA[\n".'
            function clip_enable_'.$this->id.'() {
                var_auto_'.$this->id.' = new Zikula.Autocompleter(\''.$this->id.'\', \''.$this->id.'_div\',
                                                 {
                                                  fetchFile: Zikula.Config.baseURL+\'ajax.php\',
                                                  parameters: {
                                                    module: "PageMaster",
                                                    func: "autocomplete",
                                                    tid: '.$this->relinfo['tid'].',
                                                    itemsperpage: '.$numitems.'
                                                  },
                                                  minchars: '.$minchars.',
                                                  maxresults: '.$numitems.',
                                                  maxItems: '.($count == 1 ? 1 : $maxitems).'
                                                 });
            }
            Event.observe(window, \'load\', clip_enable_'.$this->id.', false);
        '."\n// ]]>\n</script>";
        PageUtil::setVar('rawtext', $script);

        // build the autocompleter output
        $typeDataHtml = '
        <div id="'.$this->id.'_div" class="clip-autocompleter-div z-formnote">
            <div class="autocompleter-default">'.$this->_fn('Type the title of the related publication', 'Type the titles of the related publications', $count, array()).'</div>
            <ul class="autocompleter-feed">
                './* foreach($this->items) <li value="id">Pub title</li> .*/'
            </ul>
        </div>';

        return $result . $typeDataHtml;
    }

    /**
     * Saves value in data object.
     *
     * Called by the render when doing $view->getValues()
     * Uses the group parameter to decide where to store data.
     *
     * @param Form_View $view  Reference to Form_View object.
     * @param array     &$data Data object.
     *
     * @return void
     */
    function saveValue($view, &$data)
    {
        if ($this->dataBased) {
            $value = $this->parseValue($view, $this->text);

            $classname = 'PageMaster_Model_Pubdata'.$this->relinfo['tid'];
            $pub = Doctrine_Core::getTable($classname)
                   ->find($value);

            if ($this->group == null) {
                $data[$this->dataField] = $pub;
            } else {
                if (!array_key_exists($this->group, $data)) {
                    $data[$this->group] = array();
                }
                $data[$this->group][$this->dataField] = $pub;
            }
        }
    }
}
