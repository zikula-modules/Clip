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

class Clip_Form_Plugin_Int extends Zikula_Form_Plugin_IntInput
{
    public $pluginTitle;
    public $columnDef = 'I4';

    public $config = array();

    public function setup()
    {
        $this->setDomain(ZLanguage::getModuleDomain('Clip'));
        
        //! field type name
        $this->pluginTitle = $this->__('Integer');
    }

    public function getFilename()
    {
        return __FILE__;
    }

    /**
     * Form Framework methods.
     */
    public function readParameters($view, &$params)
    {
        $this->parseConfig($view->eventHandler->getPubfieldData($params['id'], 'typedata'));

        $params['minValue'] = isset($params['minValue']) ? $params['minValue'] : $this->config['min'];
        $params['maxValue'] = isset($params['maxValue']) ? $params['maxValue'] : $this->config['max'];

        parent::readParameters($view, $params);
    }

    /**
     * Clip processing methods.
     */
    public static function processQuery(&$query, $field, $args)
    {
        if (!$field['isuid']) {
            return;
        }

        // restrict the query for normal users
        if (!Clip_Access::toPubtype($args['tid'], 'editor')) {
            $uid = UserUtil::getVar('uid');
            $query->andWhere("$fieldname = ?", $uid);
        }
    }

    /**
     * Clip admin methods.
     */
    public static function getSaveTypeDataFunc($field)
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

    public function getTypeHtml($field, $view)
    {
        $this->parseConfig($view->_tpl_vars['field']['typedata']);

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
    public function parseConfig($typedata='', $args=array())
    {
        $typedata = explode('|', $typedata);

        $this->config = array(
            'min' => is_numeric($typedata[0]) ? (float)$typedata[0] : null,
            'max' => isset($typedata[1]) && is_numeric($typedata[1]) ? (float)$typedata[1] : null
        );
    }
}
