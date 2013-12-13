<?php?>
<?php/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://github.com/zikula-modules/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Event
 */
namespace Clip\Event;

use InvalidArgumentException;
class GenericEvent extends \\Zikula_Event
{
    /**
         * EventManager instance.
         * 
         * @var Zikula_EventManagerInterface
         * /
        protected $eventManager;
    
        /**
         * Name of the event.
         *
         * @var string
         * /
        protected $name;
    
        /**
         * Pubtype as the subject.
         *
         * @var object involved pubtype instance.
         * /
        protected $subject;
    
        /**
         * Storage of the event data.
         *
         * @var mixed
         * /
        public $data;
    
        /**
         * Array of arguments.
         *
         * @var array
         * /
        protected $args;
    
        /**
         * Signal to stop further notification.
         *
         * @var boolean
         * /
        protected $stop = false;
    
        /**
         * Exception.
         *
         * @var Exception
         * /
        protected $exception;
    */
    /**
     * Encapsulate an event called $name.
     *
     * @param string $name Name of the event.
     * @param mixed  $data Convenience argument of data for optional processing.
     * @param array  $args Arguments to store in the event.
     *
     * @throws InvalidArgumentException When name is empty.
     */
    public function __construct(
        $name,
        $subject,
        $data = null,
        $args = array()
    ) {
        // must have a name
        if (empty($name)) {
            throw new InvalidArgumentException('Event name cannot be empty.');
        }
        $this->name = $name;
        $this->subject = $subject;
        $this->data = $data;
        $this->args = $args;
    }
    
    /**
     * Pubtype checker.
     *
     * @return boolean
     */
    public function pubtypeIs($value, $field = 'urltitle')
    {
        return isset($this->subject[$field]) && $this->subject[$field] == $value;
    }

}<?php 