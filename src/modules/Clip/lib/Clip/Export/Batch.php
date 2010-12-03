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

        $this->format = strtolower($this->format);
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
        $this->sections[] = $section;
    }

    /**
     * Execution method.
     *
     * @return void
     */
    public function execute()
    {
        $classname = 'Clip_Export_Formatter_'.strtoupper($this->format);
        $formatter = new $classname();

        $this->output .= $formatter->insertHeader();

        $t = count($this->sections);
        foreach ($this->sections as $section) {
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
                // TODO
                break;

            case 1: // Browser
                $filename = "clip-export-".DateUtil::getDatetime_Date().'.'.$this->format;
                header("Content-disposition: attachment; filename=$filename");

                switch ($this->format) {
                    case 'xml':
                        header('Content-type: text/xml');
                        print $this->output;
                        break;
                }
                System::shutDown();
                break;
        }
    }
}
