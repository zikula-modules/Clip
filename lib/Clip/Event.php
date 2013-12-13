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

namespace Clip;

use Clip_Model_Pubtype;
use Clip_Doctrine_Pubdata;
use Clip_Util;
use Zikula_View;
use InvalidArgumentException;
use Clip_Event_Generic;
use EventUtil;

/**
 * Clip Event utility class.
 */
class Event
{
    const NAME_PATTERN = 'module.clip.%s';
    /**
     * Clip Event name resolver.
     *
     * @param $name Name abbreviation (i.e. ui.display, data.list).
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
     * @param $name Name abbreviation (i.e. ui.display, data.list).
     * @param $data Data to process.
     * @param $args Context arguments.
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
            if ($pubtype instanceof Clip_Model_Pubtype) {
                $subject = $pubtype;
            }
        } elseif ($data instanceof Clip_Doctrine_Pubdata) {
            $subject = Clip_Util::getPubType($data['core_tid']);
        } elseif ($args instanceof Clip_Doctrine_Pubdata) {
            $subject = Clip_Util::getPubType($args['core_tid']);
        } elseif (isset($args['tid'])) {
            $subject = Clip_Util::getPubType($args['tid']);
        }
        if (empty($subject)) {
            throw new InvalidArgumentException('Invalid event parameters. Unable to determine que subject.');
        }
        $event = new Clip_Event_Generic($name, $subject, $data, $args);
        //$event = new Zikula_Event(self::getName($name), $subject, $args, $data);
        return EventUtil::notify($event);
    }

}