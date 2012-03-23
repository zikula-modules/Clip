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

        $this->reset();

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
            'pids' => array(), // publications map
            'updt' => array()
        );
    }

    /**
     * Internal pubtype saver.
     */
    protected function updateTables()
    {
        foreach (self::$idmap['tids'] as $tid) {
            if (!isset(self::$idmap['updt'][$tid])) {
                Doctrine_Core::getTable('Clip_Model_Pubtype')->find($tid)->updateTable(true);
                self::$idmap['updt'][$tid] = true;
            }
        }
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
                $obj = $tbl->getRecord()->copy();
                $oid = (int)$args['pubtype']['tid'];
                unset($args['pubtype']['tid']);
                // validate the non-duplication of the urlname
                while ($tbl->findBy('urltitle', $args['pubtype']['urltitle'])->count()) {
                    $args['pubtype']['urltitle']++;
                }
                // process the record
                $obj->fromArray($args['pubtype']);
                // see if we have the next tid already
                static $nexttid;
                if (!isset($nexttid)) {
                    // get the connection name to figure our the database name in use
                    $tablename = Doctrine_Core::getTable('Clip_Model_Pubtype')->getTableName();
                    $statement = Doctrine_Manager::getInstance()->connection();
                    $connname  = $statement->getName();
                    // get the databases list
                    $serviceManager = ServiceUtil::getManager();
                    $databases = $serviceManager['databases'];
                    $result    = $statement->execute("SELECT AUTO_INCREMENT
                                                        FROM information_schema.TABLES
                                                       WHERE TABLE_NAME = '$tablename'
                                                         AND TABLE_SCHEMA = '{$databases[$connname]['dbname']}'");
                    $nexttid = (int)$result->fetchColumn();
                } else {
                    $nexttid++;
                }
                // save the pubtype and create the table
                $obj->save();
                self::$idmap['tids'][$oid] = $nexttid;
                break;

            case 'pubfields':
                $tid = $args['pubfield']['tid'];
                if (!isset(self::$idmap['tids'][$tid])) {
                    continue;
                }
                $tbl = Doctrine_Core::getTable('Clip_Model_Pubfield');
                $obj = $tbl->getRecord()->copy();
                $oid = (int)$args['pubfield']['id'];
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
                self::$idmap['fids'][$oid] = (int)$obj['id'];
                break;

            case 'pubdata':
                $this->updateTables();
                $tid = Clip_Util::getTidFromString($args['section']);
                if (!isset(self::$idmap['tids'][$tid])) {
                    continue;
                }
                $oid = (int)$args['pub']['id'];
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
                $tbl = Doctrine_Core::getTable('ClipModels_Pubdata'.$newtid);
                $obj = $tbl->getRecord()->copy();
                // process the record
                $obj->fromArray($args['pub']);
                $obj->save();
                self::$idmap['pids'][$oid] = (int)$obj['id'];
                break;

            case 'workflows':
                $tid = Clip_Util::getTidFromString($args['section']);
                $newtid = self::$idmap['tids'][$tid];
                // update the new id refs
                $args['workflow']['obj_id'] = self::$idmap['pids'][$args['workflow']['obj_id']];
                $args['workflow']['obj_table'] = 'clip_pubdata'.$newtid;
                // handled with DBUtil
                DBUtil::insertObject($args['workflow'], 'workflows');
                break;
        }
    }
}
