<?php
/**
 * PageMaster.
 *
 * @copyright   (c) PageMaster Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/LGPL - http://www.gnu.org/copyleft/lgpl.html
 */

/**
 * PageMaster_Util
 */
class PageMaster_Util
{
    /**
     * ServiceManager instance.
     *
     * @var Zikula_ServiceManager
     */
    protected $serviceManager;

    /**
     * Event Manager instance.
     *
     * @var Zikula_EventManager
     */
    protected $eventManager;

    /**
     * Constructor.
     *
     * @param Zikula_ServiceManager $serviceManager ServiceManager.
     */
    public function __construct(Zikula_ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        $this->eventManager = $this->serviceManager->getService('zikula.eventmanager');
    }
}