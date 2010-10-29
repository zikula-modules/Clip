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

class Clip_Form_Plugin_Float extends Form_Plugin_FloatInput
{
    public $pluginTitle;
    public $columnDef = 'F';

    public $config = array();

    function setup()
    {
        //! field type name
        $this->pluginTitle = $this->__('Float Value');
    }

    function getFilename()
    {
        return __FILE__;
    }

    /**
     * Form Framework methods.
     */
    function readParameters($view, &$params)
    {
        $this->parseConfig($view->eventHandler->getPubfieldData($params['id'], 'typedata'));

        $params['minValue'] = isset($params['minValue']) ? $params['minValue'] : $this->config['min'];
        $params['maxValue'] = isset($params['maxValue']) ? $params['maxValue'] : $this->config['max'];

        parent::readParameters($view, $params);
    }

    /**
     * Clip admin methods.
     */
    static function getSaveTypeDataFunc($field)
    {
        $saveTypeDataFunc = 'function saveTypeData()
                             {
                                 if ($F(\'clipplugin_minvalue\') != null) {
                                     $(\'typedata\').value = $F(\'clipplugin_minvalue\');
                                 }
                                 $(\'typedata\').value += \'|\';
                                 if ($F(\'clipplugin_maxvalue\') != null) {
                                     $(\'typedata\').value += $F(\'clipplugin_maxvalue\');
                                 }
                                 closeTypeData();
                             }';

        return $saveTypeDataFunc;
    }

    function getTypeHtml($field, $view)
    {
        $typedata = isset($view->_tpl_vars['field']['typedata']) ? $view->_tpl_vars['field']['typedata'] : '|';
        $this->parseConfig($typedata);

        $html = ' <div class="z-formrow">
                      <label for="clipplugin_minvalue">'.$this->__('Min value').':</label>
                      <input type="text" id="clipplugin_minvalue" name="clipplugin_minvalue" value="'.$this->config['min'].'" />
                  </div>
                  <div class="z-formrow">
                      <label for="clipplugin_maxvalue">'.$this->__('Max value').':</label>
                      <input type="text" id="clipplugin_maxvalue" name="clipplugin_maxvalue" value="'.$this->config['max'].'" />
                  </div>';

        return $html;
    }

    /**
     * Parse configuration
     */
    function parseConfig($typedata='', $args=array())
    {
        $typedata = explode('|', $typedata);

        $this->config = array(
            'min' => $typedata[0] !== '' ? (float)$typedata[0] : null,
            'max' => isset($typedata[1]) ? (float)$typedata[1] : null
        );
    }
}
