<?php

class Listeners
{
    public static function getFormPlugins(Zikula_Event $event)
    {
        $mypluginList = array();
        $event->data = $mypluginList;
    }
}