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
    private $relinfo;

    function getFilename()
    {
        return __FILE__;
    }

    function __construct($view, &$params)
    {
        $this->relinfo = $params['relation'];

        $params['textMode'] = 'hidden';

        parent::__construct($view, $params);
    }

    function render($view)
    {
        $result = parent::render($view);

        $script =
        "<script type=\"text/javascript\">\n//<![CDATA[\n".'
            function clip_enable_'.$this->id.'() {
                var_auto_'.$this->id.' = new FacebookList(\''.$this->id.'\', \''.$this->id.'_div\');
            }
            Event.observe(window, \'load\', clip_enable_'.$this->id.', false);
        '."\n// ]]>\n</script>";
        PageUtil::setVar('rawtext', $script);

        $count = $this->relinfo['own'] ? ($this->relinfo['type']%2 ? 1 : 2) : ($this->relinfo['type'] <= 1 ? 1 : 2);
        $typeDataHtml  = '
        <div id="'.$this->id.'_div" class="clip-autocompleter-div">
            <div class="autolist-default">'.$this->_fn('Type the title of the related publication', 'Type the titles of the related publications', $count, array()).'</div>
                <ul class="autolist-feed">
                    './* foreach($this->items) <li value="id">Pub title</li> .*/'
                </ul>
            </div>
        </div>';

        return $result . $typeDataHtml;
    }
}
