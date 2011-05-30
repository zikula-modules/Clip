<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage View_Plugins
 */

/**
 * Generic form Plugin
 *
 * Loads the desired plugin from fieldtype definition
 */
function smarty_function_clip_form_genericplugin($params, &$render)
{
    $dom = ZLanguage::getModuleDomain('Clip');

    if (!$params['id']) {
        return LogUtil::registerError(__f('Error! Missing argument [%s].', 'id', $dom));
    }

    $tid = $render->eventHandler->getTid();

    $pubfields   = Clip_Util::getPubFields($tid);

    // read settings in pubfields, if set by template ignore settings in pubfields
    if (!isset($params['mandatory'])){
        $params['mandatory'] = $pubfields[$params['id']]['ismandatory'];
    }
    if (!isset($params['maxLength'])){
        $params['maxLength'] = $pubfields[$params['id']]['fieldmaxlength'];
    }

    $plugin = Clip_Util_Plugins::get($pubfields[$params['id']]['fieldplugin']);

    if (method_exists($plugin, 'pluginRegister')) {
        return $plugin->pluginRegister($params, $render);
    } else {
        return $render->registerPlugin(get_class($plugin), $params);
    }
}
