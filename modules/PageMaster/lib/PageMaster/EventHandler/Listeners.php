<?php

class PageMaster_EventHandler_Listeners
{
    public static function getFormPlugins(Zikula_Event $event)
    {
        $classDirs = array();
        $classDirs[] = 'config/classes/modules/PageMaster/lib/PageMaster/Form/Plugin';
        $classDirs[] = 'modules/PageMaster/lib/PageMaster/Form/Plugin';

        $plugins = array();
        foreach ($classDirs as $classDir) {
            $files = FileUtil::getFiles($classDir, false, true, 'php', 'f');
            foreach ($files as $file) {
                $pluginclass = substr($file, 0, strlen($file)-4);
                $plugin = PMgetPlugin($pluginclass);
                $plugins[] = array (
                        'plugin' => $plugin,
                        'class' => $pluginclass
                );
            }
        }

        $event->data = $plugins;
    }
}