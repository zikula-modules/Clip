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

/**
 * Generic form Plugin
 * Loads the disired plugin from fieldtype definition
 *
 * @param $args['fieldname']
 * @param generic
 */
function smarty_function_clip_form_genericplugin($params, &$render)
{
    $dom = ZLanguage::getModuleDomain('Clip');

    $id  = $params['id'];

    if (!$id) {
        return LogUtil::registerError(__f('Error! Missing argument [%s].', 'id', $dom));
    }

    $tid = $render->eventHandler->getTid();

    $pubfields   = Clip_Util::getPubFields($tid);
    $pluginclass = $pubfields[$id]['fieldplugin'];

    $pluginclass = Clip_Util::processPluginClassname($pluginclass);

    // read settings in pubfields, if set by template ignore settings in pubfields
    if (!isset($params['mandatory'])){
        $params['mandatory'] = $pubfields[$id]['ismandatory'];
    }

    if (!isset($params['maxLength'])){
        $params['maxLength'] = $pubfields[$id]['fieldmaxlength'];
    }

    return $render->registerPlugin($pluginclass, $params);
}
