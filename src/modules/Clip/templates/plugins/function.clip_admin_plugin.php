<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://github.com/zikula-modules/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage View_Plugins
 */

/**
 * Admin Form Plugin.
 * Clip's interface to load an admin form plugins.
 *
 * Available parameters:
 *  - id     (string)  ID of the field.
 *  - plugin (string)  Admin plugin to use.
 *
 * Example:
 *
 *  <samp>{clip_admin_plugin group='wfvars' id='notify.admins' plugin='recipients'}</samp>
 *
 * @param array            $params All parameters passed to this plugin from the template.
 * @param Zikula_Form_View $view   Reference to the {@link Zikula_Form_View} object.
 *
 * @return mixed Plugin output.
 */
function smarty_function_clip_admin_plugin($params, Zikula_Form_View &$render)
{
    if (!isset($params['id']) || !$params['plugin']) {
        $render->trigger_error($render->__f('Error! Missing argument [%s].', 'id | plugin'));
    }

    // validate and setup the plugin to use
    $plugin = $params['plugin'];
    unset($params['plugin']);

    // get the admin plugin classname
    $plugin = Clip_Util_Plugins::getAdminClassname($plugin);

    // validate that the class exists
    if (!$plugin) {
        $render->trigger_error($render->__f('Error! The specified plugin class [%s] does not exists.', $plugin));
    }

    // check if it's needed to remove some parameters
    $vars = array_keys(get_class_vars($plugin));

    if (!in_array('maxLength', $vars)) {
        unset($params['maxLength']);
    }
    if (!in_array('mandatory', $vars)) {
        unset($params['mandatory']);
    }

    // register plugin
    return $render->registerPlugin($plugin, $params);
}
