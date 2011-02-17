<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage EventHandler
 */

/**
 * Listeners EventHandler.
 */
class Clip_EventHandler_Listeners
{
    /**
     * Example provider handler.
     *
     * Simple add to, or override elements of the the array contained in $event->data
     *
     * @param Zikula_Event $event
     */
    public static function getFormPlugins(Zikula_Event $event)
    {
        /*
        $classNames = array();
        $classNames['Date']       = 'Clip_Form_Plugin_Date';
        $classNames['Email']      = 'Clip_Form_Plugin_Email';

        $event->setData(array_merge((array)$event->getData(), $classNames));
        */
    }

    /**
     * Filters provider handler.
     *
     * Attach the Clip filters to the available ones
     *
     * @param Zikula_Event $event
     */
    public static function getFilterClasses(Zikula_Event $event)
    {
        $classNames = array();
        $classNames['cliplist']  = 'Clip_Filter_List';
        $classNames['clipmlist'] = 'Clip_Filter_MultiList';
        $classNames['clipuser']  = 'Clip_Filter_User';

        $event->setData(array_merge((array)$event->getData(), $classNames));
    }
}
