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
    static $tablescreated;

    protected $filename;
    protected $file;
    protected $url;
    protected $format;

    protected $data = array();
    protected $gzip = false;

    public function  __construct($args)
    {
        $this->setup($args);
    }

    /**
     * Setup function.
     *
     * @param string  $args['filter']   Filter to use in the export (optional).
     * @param string  $args['format']   Format of the output.
     * @param integer $args['outputto'] Type of output (0: File, 1: Browser).
     * @param string  $args['filename'] File name to use if the output is to a file (0).
     *
     * @return boolean True on success, false otherwise.
     */
    public function setup($args)
    {
        $objInfo = get_class_vars(get_class($this));

        // Iterate through all params: place known params in member variables
        foreach ($args as $name => $value) {
            if (array_key_exists($name, $objInfo) && !in_array($name, array('sections', 'data', 'gzip'))) {
                $this->$name = $value;
            }
        }

        if (empty($this->file) && empty($this->url)) {
            return LogUtil::registerError($this->__('You must specify a file to import from.'));
            //return LogUtil::registerError($this->__('You must specify a file or a url to import from.'));
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

        return true;
    }

    /**
     * Internal reset.
     */
    protected function reset()
    {
        // reset the old/new IDs map
        self::$idmap = array(
            'tids' => array(), // pubtypes map
            'fids' => array(), // pubfields map
            'pids' => array()  // publications map
        );

        self::$tablescreated = false;
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

        // disable the Doctrine_Manager validation for the import
        $manager = Doctrine_Manager::getInstance();
        $manager->setAttribute(Doctrine_Core::ATTR_VALIDATE, Doctrine_Core::VALIDATE_NONE);

        $result = $parser->parseSections(array($this, 'parseSection'));

        if ($result) {
            // redirect to the first pubtype imported info screen
            $result = ModUtil::url('Clip', 'admin', 'pubtypeinfo', array('tid' => reset(self::$idmap['tids'])));
        }

        // reset this object for later clean use
        $this->reset();

        return $result;
    }

    /**
     * Parse callback.
     *
     * @return void
     */
    public function parseSection($args)
    {
        switch (Clip_Util::getStringPrefix($args['section']))
        {
            case 'pubtypes':
                $tbl = Doctrine_Core::getTable('Clip_Model_Pubtype');
                $obj = $tbl->getRecord();
                $oid = $args['pubtype']['tid'];
                unset($args['pubtype']['tid']);
                // validate the non-duplication of the urlname
                while ($tbl->findBy('urltitle', $args['pubtype']['urltitle'])->count()) {
                    $args['pubtype']['urltitle']++;
                }
                // process the record
                $obj->fromArray($args['pubtype']);
                $obj->save();
                self::$idmap['tids'][$oid] = $obj['tid'];
                break;

            case 'pubfields':
                if (!isset(self::$idmap['tids'][$args['pubfield']['tid']])) {
                    continue;
                }
                $tbl = Doctrine_Core::getTable('Clip_Model_Pubfield');
                $obj = $tbl->getRecord();
                $oid = $args['pubfield']['id'];
                $tid = $args['pubfield']['tid'];
                unset($args['pubfield']['id']);
                // null fields check
                if (empty($args['pubfield']['fieldmaxlength'])) {
                    $args['pubfield']['fieldmaxlength'] = null;
                }
                // update the id refs
                $args['pubfield']['tid'] = self::$idmap['tids'][$tid];
                // process the record
                $obj->fromArray($args['pubfield']);
                $obj->save();
                self::$idmap['fids'][$oid] = $obj['id'];
                break;

            case 'pubdata':
                if (!self::$tablescreated) {
                    // recreate models once the field has been added
                    Clip_Generator::loadModelClasses(true);
                    // create the new tables
                    foreach (self::$idmap['tids'] as $tid) {
                        Doctrine_Core::getTable('Clip_Model_Pubdata'.$tid)->createTable();
                    }
                    self::$tablescreated = true;
                }
                $tid = Clip_Util::getTidFromString($args['section']);
                if (!isset(self::$idmap['tids'][$tid])) {
                    continue;
                }
                $oid = $args['pub']['id'];
                unset($args['pub']['id']);
                // null fields check
                if (empty($args['pub']['core_publishdate'])) {
                    $args['pub']['core_publishdate'] = null;
                }
                if (empty($args['pub']['core_expiredate'])) {
                    $args['pub']['core_expiredate'] = null;
                }
                // get a record instance of the new pub
                $newtid = self::$idmap['tids'][$tid];
                $tbl = Doctrine_Core::getTable('Clip_Model_Pubdata'.$newtid);
                $obj = $tbl->getRecord();
                // process the record
                $obj->fromArray($args['pub']);
                $obj->save();
                self::$idmap['pids'][$oid] = $obj['id'];
                break;

            case 'workflows':
                $tid = Clip_Util::getTidFromString($args['section']);
                $newtid = self::$idmap['tids'][$tid];
                // update the new id refs
                $args['workflow']['obj_id'] = self::$idmap['pids'][$args['workflow']['obj_id']];;
                $args['workflow']['obj_table'] = 'clip_pubdata'.$newtid;
                // handled with DBUtil
                DBUtil::insertObject($args['workflow'], 'workflows');
                break;
        }
    }
}
