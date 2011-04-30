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
 * Clip Events Handler.
 */
class Clip_EventHandler
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
                if (in_array($func[1], array('pubtypeinfo', 'publist', 'history', 'showcode'))) {
                    $view->assign('maincontent', $event->getData());

                    $output = $view->fetch("clip_admin_{$func[1]}.tpl");

                    $event->setData($output);
                }
            }
        }
    }
}
