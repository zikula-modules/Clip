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

/**
 * Listeners EventHandler.
 */
class PageMaster_EventHandler_Listeners
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
        $classNames['Date']       = 'PageMaster_Form_Plugin_Date';
        $classNames['Email']      = 'PageMaster_Form_Plugin_Email';
        
        $event->setData(array_merge((array)$event->getData(), $classNames));
        */
    }
}
