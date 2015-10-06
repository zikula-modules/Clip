<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://github.com/zikula-modules/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Event
 */

namespace Matheo\Clip;

use Matheo\Clip\Event\GenericEvent;
use Matheo\Clip\Model\PubtypeModel;
use Matheo\Clip\Doctrine\PubdataDoctrine;
use Matheo\Clip\Util;
use Zikula_View;
use InvalidArgumentException;
use EventUtil;

class EventHelper
{
    const NAME_PATTERN = 'module.clip.%s';

    /**
     * Clip Event name resolver.
     *
     * @param string $name Name abbreviation (i.e. ui.display, data.list).
     *
     * @return string
     */
    public static function getName($name)
    {
        return sprintf(self::NAME_PATTERN, $name);
    }
    
    /**
     * Clip Event invoker.
     *
     * @param string $name Name abbreviation (i.e. ui.display, data.list).
     * @param mixed  $data Data to process.
     * @param array  $args Context arguments.
     *
     * @throws InvalidArgumentException When the subject cannot be resolved.
     *
     * @return boolean
     */
    public static function notify(
        $name,
        $data,
        $args = array()
    ) {
        // format the name abbreviation
        $name = self::getName($name);

        // resolve the subject
        if ($data instanceof Zikula_View) {
            $pubtype = $data->getTplVar('pubtype');
            if ($pubtype instanceof PubtypeModel) {
                $subject = $pubtype;
            }

        } elseif ($data instanceof PubdataDoctrine) {
            $subject = Util::getPubType($data['core_tid']);

        } elseif ($args instanceof PubdataDoctrine) {
            $subject = Util::getPubType($args['core_tid']);

        } elseif (isset($args['tid'])) {
            $subject = Util::getPubType($args['tid']);
        }

        if (empty($subject)) {
            throw new InvalidArgumentException('Invalid event parameters. Unable to determine que subject.');
        }

        // TODO upgrade event handling to PublicationEvent
        $event = new GenericEvent($name, $subject, $data, $args);
        return EventUtil::notify($event);
    }

}
