<?php
/**
 * Clip
 *
 * @copyright   (c) Clip Team
 * @link        http://code.zikula.org/clip/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package     Zikula_3rdParty_Modules
 * @subpackage  clip
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
        // input relation data
        if (isset($params['relation'])) {
            $this->relinfo = $params['relation'];
        }

        if (!is_null($this->relinfo)) {
            // assign existing data if available
            if (!($view->_tpl_vars['pubdata'][$this->relinfo['alias']] instanceof Doctrine_Null)) {
                $this->relinfo['data'] = $view->_tpl_vars['pubdata'][$this->relinfo['alias']];
            } else {
                $this->relinfo['data'] = null;
            }

            // detects single or multiple relation
            $this->relinfo['single'] = $this->relinfo['own'] ? ($this->relinfo['type']%2 == 0 ? true : false) : ($this->relinfo['type'] <= 1 ? true : false);
        }

        $params['textMode'] = 'hidden';

        parent::__construct($view, $params);
    }

    /**
     * Post-initialise hook.
     *
     * @return void
     */
    public function postInitialize()
    {
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

        // build the autocompleter setup
        PageUtil::addVar('javascript', 'prototype');
        PageUtil::addVar('javascript', 'modules/Clip/javascript/facebooklist.js');
        $script =
        "<script type=\"text/javascript\">\n// <![CDATA[\n".'
            function clip_enable_'.$this->id.'() {
                var_auto_'.$this->id.' = new Zikula.Autocompleter(\''.$this->id.'\', \''.$this->id.'_div\',
                                                 {
                                                  fetchFile: Zikula.Config.baseURL+\'ajax.php\',
                                                  parameters: {
                                                    module: "Clip",
                                                    func: "autocomplete",
                                                    tid: '.$this->relinfo['tid'].',
                                                    itemsperpage: '.$numitems.'
                                                  },
                                                  minchars: '.$minchars.',
                                                  maxresults: '.$numitems.',
                                                  maxItems: '.($this->relinfo['single'] ? 1 : $maxitems).'
                                                 });
            }
            Event.observe(window, \'load\', clip_enable_'.$this->id.', false);
        '."\n// ]]>\n</script>";
        PageUtil::addVar('rawtext', $script);

        // build the autocompleter output
        $typeDataHtml = '
        <div id="'.$this->id.'_div" class="z-auto-container">
            <div class="z-auto-default">'.$this->_fn('Type the title of the related publication', 'Type the titles of the related publications', $this->relinfo['single'] ? 1 : 2, array()).'</div>
            <ul class="z-auto-feed">
                ';
        if ($this->relinfo['single']) {
            if (isset($this->relinfo['data']['id'])) {
                $relpub = $this->relinfo['data'];
                $relpub->pubPostProcess();
                $typeDataHtml .= '<li value="'.$relpub['id'].'">'.$relpub['core_title'].'</li>';
            }
        } elseif (isset($this->relinfo['data']) && $this->relinfo['data']->count() > 0) {
            foreach ($this->relinfo['data'] as $relpub) {
                $relpub->pubPostProcess();
                $typeDataHtml .= '<li value="'.$relpub['id'].'">'.$relpub['core_title'].'</li>';
            }
        }
        $typeDataHtml .= '
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

            $classname = 'Clip_Model_Pubdata'.$this->relinfo['tid'];
            $tableObj  = Doctrine_Core::getTable($classname);

            $ref = $this->relinfo['single'] ? array($value) : explode(':', $value);

            if ($this->group == null) {
                $data[$this->dataField] = $ref;
            } else {
                if (!array_key_exists($this->group, $data)) {
                    $data[$this->group] = array();
                }
                $data[$this->group][$this->dataField] = $ref;
            }
        }
    }
}
