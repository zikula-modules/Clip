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
 * Export main class.
 */
class Clip_Export_Batch
{
    protected $name;
    protected $filter;
    protected $format;
    protected $outputto;
    protected $filename;

    protected $sections = array();
    protected $output = '';

    /**
     * Constructor.
     *
     * @param string  $args['filter']   Filter to use in the export (optional).
     * @param string  $args['format']   Format of the output.
     * @param integer $args['outputto'] Type of output (0: File, 1: Browser).
     * @param string  $args['filename'] File name to use if the output is to a file (0).
     *
     * @return void
     */
    public function __construct($args)
    {
        $objInfo = get_class_vars(get_class($this));

        // Iterate through all params: place known params in member variables
        foreach ($args as $name => $value) {
            if (array_key_exists($name, $objInfo) && !in_array($name, array('sections', 'output'))) {
                $this->$name = $value;
            }
        }

        $this->format = strtoupper($this->format);
    }

    /**
     * Add a section to the batch.
     *
     * @param Clip_Export_Section $section Section to add.
     *
     * @return void
     */
    public function addSection(Clip_Export_Section $section)
    {
        $this->sections[$section->getName()] = $section;
    }

    /**
     * Execution method.
     *
     * @return void
     */
    public function execute()
    {
        // TODO validate existance of the formatter class
        $classname = 'Clip_Export_Formatter_'.$this->format;
        $formatter = new $classname();

        $this->output .= $formatter->insertHeader();

        $t = count($this->sections);
        foreach ($this->sections as &$section) {
            // checks if there's a section dependency
            if ($section->needsIds()) {
                $query = $section->getQuery();
                foreach ($section->needsIds() as $sname => $field) {
                    if (isset($this->sections[$sname])) {
                        $query->whereIn($field, array_unique($this->sections[$sname]->getIds()));
                    }
                }
                $section->setQuery($query);
            }
            // perform the execution
            $this->output .= $formatter->formatSection($section);
            $t--;
            if ($t) {
                $this->output .= $formatter->insertSeparator();
            }
        }

        $this->output .= $formatter->insertFooter();
    }

    /**
     * Return the output.
     *
     * @return mixed
     */
    public function output()
    {
        switch ($this->outputto) {
            case 0: // File
                // TODO Postponed to Clip 1.0
                break;

            case 1: // Browser
                $filename = "clip-{$this->name}-".DateUtil::getDatetime_Date().'.'.$this->format;
                header("Content-disposition: attachment; filename=$filename");

                switch ($this->format) {
                    case 'XML':
                        header('Content-type: text/xml');
                        print $this->output;
                        break;
                }
                System::shutDown();
                break;
        }
    }
}
