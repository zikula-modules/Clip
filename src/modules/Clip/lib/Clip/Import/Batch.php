<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Import
 */

/**
 * Import main class.
 */
class Clip_Import_Batch
{
    static $idmap;

    protected $filename;
    protected $file;
    protected $url;
    protected $format;

    protected $data = array();
    protected $gzip = false;

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
            if (array_key_exists($name, $objInfo) && !in_array($name, array('sections', 'data', 'gzip'))) {
                $this->$name = $value;
            }
        }

        if (empty($this->file) && empty($this->url)) {
            return LogUtil::registerError($this->__('Must specify a file or an url to import from.'));
        } else {
            $this->filename = !empty($this->url) ? $this->url : $this->file['name'];
            $this->file     = !empty($this->url) ? $this->url : $this->file['tmp_name'];
        }

        $this->format = FileUtil::getExtension($this->filename);
        if ($this->format == 'gz') {
            $this->gzip = true;
            $this->format = FileUtil::getExtension(substr($this->filename, -3));
        }

        $this->format = strtoupper($this->format);

        // reset the old/new IDs map
        self::$idmap = array(
            'tids' => array(), // pubtypes map
            'fids' => array(), // pubfields map
            'pids' => array()  // publications map
        );
    }

    /**
     * Execution method.
     *
     * @return void
     */
    public function execute()
    {
        // TODO validate the existance of the parser class
        $classname = 'Clip_Import_Parser_'.$this->format;
        $parser = new $classname($this->file);

        return $parser->parseSections(array('Clip_Import_Batch', 'parseSection'));
    }

    /**
     * Parse callback.
     *
     * @return void
     */
    static public function parseSection($args)
    {
        var_dump($args);
    }
}
