<?php

class PageMaster_EventHandler_Listeners
{
    public static function getFormPlugins(Zikula_Event $event)
    {
        $classNames = array();
        $classNames[] = 'PageMaster_Form_Plugin_Date';
        $classNames[] = 'PageMaster_Form_Plugin_Email';
        $classNames[] = 'PageMaster_Form_Plugin_Float';
        $classNames[] = 'PageMaster_Form_Plugin_Image';
        $classNames[] = 'PageMaster_Form_Plugin_Int';
        $classNames[] = 'PageMaster_Form_Plugin_List';
        $classNames[] = 'PageMaster_Form_Plugin_Ms';
        $classNames[] = 'PageMaster_Form_Plugin_MultiCheck';
        $classNames[] = 'PageMaster_Form_Plugin_MultiList';
        $classNames[] = 'PageMaster_Form_Plugin_Pub';
        $classNames[] = 'PageMaster_Form_Plugin_String';
        $classNames[] = 'PageMaster_Form_Plugin_Text';
        $classNames[] = 'PageMaster_Form_Plugin_Upload';
        $classNames[] = 'PageMaster_Form_Plugin_Url';

        $plugins = array();
        foreach ($classNames as $className) {
            $plugin = PMgetPlugin($className);
            $plugins[] = array (
                    'plugin' => $plugin,
                    'class' => str_replace('PageMaster_Form_Plugin_', '', $className)
            );
        }

        $event->data = $plugins;
    }
}