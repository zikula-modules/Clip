<?php
/**
 * Clip
 *
 * @copyright   (c) Clip Team
 * @link        http://code.zikula.org/clip/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @version     $ Id $
 * @package     Zikula_3rdParty_Modules
 * @subpackage  clip
 */

class Clip_Form_Plugin_Pub extends Form_Plugin_DropdownList
{
    public $pluginTitle;
    public $columnDef = 'I4';

    public $config;

    function setup()
    {
        $dom = ZLanguage::getModuleDomain('Clip');
        $this->setDomain($dom);

        //! field type name
        $this->pluginTitle = $this->__('Publication');
    }

    function getFilename()
    {
        return __FILE__;
    }

    function getPluginOutput($field)
    {
        $this->parseConfig($field['typedata']);

        $full = '    {if !empty($pubdata.'.$field['name'].')}'."\n".
                '        <div class="z-formrow">'."\n".
                '            <span class="z-label">{gt text=\''.$field['title'].'\'}:</span>'."\n".
                '            <span class="z-formnote">'."\n".
                '                <pre>{clip_array array=$pubdata.'.$field['name'].'}</pre>'."\n".
                '                {*modapifunc modname=\'Clip\' func=\'get\' tid=\''.$this->config['tid'].'\' pid=$pubdata.'.$field['name'].' assign=\''.$field['name'].'_pub\' checkPerm=true handlePluginFields=true getApprovalState=true*}'."\n".
                '            <span>'."\n".
                '        </div>'."\n".
                '    {/if}';

        return array('full' => $full);
    }

    function postRead($data, $field)
    {
        $this->parseConfig($field['typedata']);

        $pub = array();

        if (!empty($this->config['tid']) && !empty($data)) {
            $pub = ModUtil::apiFunc('Clip', 'user', 'get',
                                array('tid'                => $this->config['tid'],
                                      'pid'                => (int)$data,
                                      'checkPerm'          => true,
                                      'getApprovalState'   => true,
                                      'handlePluginFields' => true));

            if (!$pub) {
                $pub = array('core_error' => $this->__('No such publication found.'));
            }
        }

        return $pub;    
    }

    function load($view, &$params)
    {
        $this->parseConfig($view->eventHandler->getPubfieldData($this->id, 'typedata'));

        if (!empty($this->config['tid'])) {
            $pubarr = ModUtil::apiFunc('Clip', 'user', 'getall',
                                   array('tid'                => $this->config['tid'],
                                         'countmode'          => 'no',
                                         'filter'             => $this->config['filter'],
                                         'orderby'            => $this->config['orderby'],
                                         'checkPerm'          => true,
                                         'handlePluginFields' => false));

            $titleField = Clip_Util::getTitleField($this->config['tid']);

            $items = array();
            $items[] = array('text'  => '- - -',
                             'value' => '');

            foreach ($pubarr['publist'] as $pub ) {
                $items[] = array('text'  => $pub[$titleField],
                                 'value' => $pub['core_pid']);
            }
            $this->items = $items;
        } else {
            $this->items = array(
                               array('text'  => $this->__('Plugin not configured.'),
                                     'value' => '')
                           );
        }

        parent::load($view);
    }

    static function getSaveTypeDataFunc($field)
    {
        // TODO Implement effects for the checkbox enabled
        // TODO Implement postBack to check if the fields are correct?
        $saveTypeDataFunc = 'function saveTypeData()
                             {
                                 $(\'typedata\').value = $F(\'clipplugin_pubtid\')+\';\'+$F(\'clipplugin_pubfilter\')+\';\'+$F(\'clipplugin_pubjoin\')+\';\'+$F(\'clipplugin_pubjoinfields\')+\';\'+$F(\'clipplugin_puborderbyfield\');
                                 closeTypeData();
                             }';

        return $saveTypeDataFunc;
    }

    function getTypeHtml($field, $view)
    {
        $typedata = isset($view->_tpl_vars['typedata']) ? $view->_tpl_vars['typedata'] : '';
        $this->parseConfig($typedata);

        $pubtypes = Doctrine_Core::getTable('Clip_Model_Pubtype')
                    ->selectFieldArray('title', '', '', false, 'tid');

        foreach ($pubtypes as $tid => $title) {
            $pubtypes[$tid] = $this->__($title);
        }
        asort($pubtypes);

        $html = ' <div class="z-formrow">
                      <label for="clipplugin_pubtid">'.$this->__('Publication').':</label>
                      <select id="clipplugin_pubtid" name="clipplugin_pubtid">';

        foreach ($pubtypes as $tid => $title) {
            $selectedText = ($tid == $this->config['tid']) ? 'selected="selected"' : '';

            $html .= "<option{$selectedText} value=\"{$tid}\">{$title}</option>\n";
        }

        $html .= '    </select>
                  </div>
                  <div class="z-formrow">
                      <label for="clipplugin_pubfilter">'.$this->__('Filter').':</label>
                      <input type="text" id="clipplugin_pubfilter" name="clipplugin_pubfilter" value="'.$this->config['filter'].'" />
                  </div>
                  <div class="z-formrow">
                      <label for="clipplugin_pubjoin">'.$this->__('Join').':</label>
                      <input type="checkbox" id="clipplugin_pubjoin" name="clipplugin_pubjoin" '.($this->config['join'] == 'on' ? 'checked="checked"' : '').' />
                  </div>
                  <div class="z-formrow">
                      <label for="clipplugin_pubjoinfields">'.$this->__('Join fields').':</label>
                      <input type="text" id="clipplugin_pubjoinfields" name="clipplugin_pubjoinfields" value="'.$this->config['alias'].'" >
                      <span class="z-formnote z-sub">'.$this->__('format: fieldname:alias,fieldname:alias').'</span>
                  </div>
                  <div class="z-formrow">
                      <label for="clipplugin_puborderbyfield">'.$this->__('Orderby field').':</label>
                      <input type="text" id="clipplugin_puborderbyfield" name="clipplugin_puborderbyfield" value="'.$this->config['orderby'].'" >
                  </div>';

        return $html;
    }

    /**
     * Parse configuration
     */
    function parseConfig($typedata='', $args=array())
    {
        $this->config = explode(';', $typedata);

        $this->config = array(
            'tid'     => (int)$this->config[0],
            'filter'  => isset($this->config[1]) ? $this->config[1] : '',
            'join'    => isset($this->config[2]) ? $this->config[2] : '',
            'alias'   => isset($this->config[3]) ? $this->config[3] : '',
            'orderby' => isset($this->config[4]) ? $this->config[4] : ''
        );
    }
}
