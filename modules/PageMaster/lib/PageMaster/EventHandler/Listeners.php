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

class PageMaster_EventHandler_Listeners
{
    public static function getFormPlugins(Zikula_Event $event)
    {
        $classNames = array();
        $classNames['Date']       = 'PageMaster_Form_Plugin_Date';
        $classNames['Email']      = 'PageMaster_Form_Plugin_Email';
        $classNames['Float']      = 'PageMaster_Form_Plugin_Float';
        $classNames['Image']      = 'PageMaster_Form_Plugin_Image';
        $classNames['Int']        = 'PageMaster_Form_Plugin_Int';
        $classNames['List']       = 'PageMaster_Form_Plugin_List';
        $classNames['Ms']         = 'PageMaster_Form_Plugin_Ms';
        $classNames['MultiCheck'] = 'PageMaster_Form_Plugin_MultiCheck';
        $classNames['MultiList']  = 'PageMaster_Form_Plugin_MultiList';
        $classNames['Pub']        = 'PageMaster_Form_Plugin_Pub';
        $classNames['String']     = 'PageMaster_Form_Plugin_String';
        $classNames['Text']       = 'PageMaster_Form_Plugin_Text';
        $classNames['Upload']     = 'PageMaster_Form_Plugin_Upload';
        $classNames['Url']        = 'PageMaster_Form_Plugin_Url';

        $plugins = array();
        foreach ($classNames as $name => $className) {
            $plugin = PageMaster_Util::getPlugin($className);
            $plugins[$name] = array(
                'plugin' => $plugin,
                'class'  => $className,
            );
        }

        $event->data = array_merge((array)$event->data, $plugins);
    }
}
