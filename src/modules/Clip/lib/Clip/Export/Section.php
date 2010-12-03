<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Export
 */

/**
 * Export section class.
 */
class Clip_Export_Section
{
    protected $name;
    protected $rowname;
    protected $pagesize = 0;
    protected $query;

    protected $page = 0;

    /**
     * Constructor.
     *
     * @param string         $args['name']     Name of the section.
     * @param string         $args['rowname']  Name of each row/register.
     * @param integer        $args['pagesize'] Size of the page to extract at time.
     * @param Doctrine_Query $args['query']    Query object to process.
     *
     * @return void
     */
    public function __construct($args)
    {
        $objInfo = get_class_vars(get_class($this));

        // Iterate through all params: place known params in member variables
        foreach ($args as $name => $value) {
            if (array_key_exists($name, $objInfo) && !in_array($name, array('page'))) {
                $this->$name = $value;
            }
        }
    }

    /**
     * Name getter.
     *
     * @return string Name of the section.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Row name getter.
     *
     * @return string Name of each row.
     */
    public function getRowname()
    {
        return $this->rowname;
    }

    /**
     * Execution method.
     *
     * @return array
     */
    public function execute()
    {
        $data = array();

        // omit a second call is there's no pagesize
        if (!$this->pagesize && $this->page) {
            return $data;
        }

        // adds the offset if not zero
        $limitOffset = $this->page * $this->pagesize;
        if ($limitOffset > 0) {
            $this->query->offset($limitOffset);
        }
        $this->page++;

        // adds the limit if not zero
        if ($this->pagesize > 0) {
            $this->query->limit($this->pagesize);
        }

        return $this->query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
    }
}
