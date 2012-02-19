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
     * Decorate the Admin Controller output with the panel header.
     *
     * @param Zikula_Event $event
     */
    public static function decorateOutput(Zikula_Event $event)
    {
        // intercept the Admin Controller output only
        if (get_class($event->getSubject()) == 'Clip_Controller_Admin') {
            $view = $event->getSubject()->getView();

            // acts only when the request type is 'admin'
            if ($view->getRequest()->getControllerName() == 'admin') {
                $func = $event->getArg('modfunc');

                // and only for the methods using base templates
                if (in_array($func[1], array('pubtypeinfo', 'pubtype', 'pubfields', 'relations', 'generator'))) {
                    $view->assign('maincontent', $event->getData());

                    $output = $view->fetch("clip_admin_decorator.tpl");

                    $event->setData($output);
                }
            }
        }
    }

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

    /**
     * ContentType discovery event handler.
     * 
     * @param Zikula_Event $event
     */
    public static function getContentTypes(Zikula_Event $event)
    {
        $types = $event->getSubject();

        // add content types with add('classname')
        $types->add('Clip_ContentType_ClipPub');
        $types->add('Clip_ContentType_ClipPublist');
    }
}
