<?php
/**
 * PageMaster
 *
 * @copyright   (c) PageMaster Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @version     $ Id $
 * @package     Zikula_3rdParty_Modules
 * @subpackage  pagemaster
 */

/**
 * Generic form Plugin
 * Loads the disired plugin from fieldtype definition
 *
 * @author kundi
 * @param $args['fieldname']
 * @param generic
 */
function smarty_function_genericformplugin($params, &$render)
{
    $dom = ZLanguage::getModuleDomain('PageMaster');

    $id  = $params['id'];

    if (!$id) {
        return LogUtil::registerError(__f('Error! Missing argument [%s].', 'id', $dom));
    }

    if (version_compare(PN_VERSION_NUM, '1.3', '>=')) {
        $tid = $render->eventHandler->pubtype['tid'];
    } else {
        $tid = $render->pnFormEventHandler->tid;
    }

    $pubfields   = PMgetPubFields($tid);
    $pluginclass = $pubfields[$id]['fieldplugin'];

    if (version_compare(PN_VERSION_NUM, '1.3', '>=')) {
        switch ($pluginclass) {
            case 'pmformcheckboxinput':
                $pluginclass = 'Checkbox';
                break;
            case 'pmformcustomdata':
                $pluginclass = 'CustomData';
                break;
            case 'pmformdateinput':
                $pluginclass = 'Date';
                break;
            case 'pmformemailinput':
                $pluginclass = 'Email';
                break;
            case 'pmformfloatinput':
                $pluginclass = 'Float';
                break;
            case 'pmformimageinput':
                $pluginclass = 'Image';
                break;
            case 'pmformintinput':
                $pluginclass = 'Int';
                break;
            case 'pmformlistinput':
                $pluginclass = 'List';
                break;
            case 'pmformmsinput':
                $pluginclass = 'Ms';
                break;
            case 'pmformmulticheckinput':
                $pluginclass = 'MultiCheck';
                break;
            case 'pmformmultilistinput':
                $pluginclass = 'MultiList';
                break;
            case 'pmformpubinput':
                $pluginclass = 'Pub';
                break;
            case 'pmformstringinput':
                $pluginclass = 'String';
                break;
            case 'pmformtextinput':
                $pluginclass = 'Text';
                break;
            case 'pmformuploadinput':
                $pluginclass = 'Upload';
                break;
            case 'pmformurlinput':
                $pluginclass = 'Url';
                break;
        }
        $pluginclass = "PageMaster_Form_Plugin_$pluginclass";
    } else {
        Loader::LoadClass($pluginclass, 'modules/PageMaster/classes/FormPlugins');
    }
    //$plugin = new $pluginclass;

    // read settings in pubfields, if set by template ignore settings in pubfields
    if (!isset($params['mandatory'])){
        $params['mandatory'] = $pubfields[$id]['ismandatory'];
    }

    if (!isset($params['maxLength'])){
        $params['maxLength'] = $pubfields[$id]['fieldmaxlength'];
    }

    return $render->pnFormRegisterPlugin($pluginclass, $params);
}
