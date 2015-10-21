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

namespace Matheo\Clip\Event;

class GenericEvent extends \Zikula_Event
{
    /**
     * Pubtype checker.
     *
     * @return boolean
     */
    public function pubtypeIs($value, $field = 'urltitle')
    {
        return isset($this->subject[$field]) && $this->subject[$field] == $value;
    }

}
