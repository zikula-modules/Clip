<?php
/**
 * PostNuke Application Framework
 *
 * @copyright (c) 2007, Philipp Niethammer
 * @link http://www.guite.de
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @author	Philipp Niethammer <webmaster@nochwer.de>
 * @package PostNuke
 * @subpackage FilterUtil
 */

Loader::loadClass('FilterUtil_Common', FILTERUTIL_CLASS_PATH);

class FilterUtil_ReplaceCommon extends FilterUtil_Common {
    /**
     * Activated pairs (old => new)
     */
    public $pair;

    /**
     * default handler
     */
    protected $default = false;

    /**
     * ID of the plugin
     */
    protected $id;

    /**
	 * Constructor
	 *
	 * @access public
	 * @param array $config Configuration array
	 * @return object FilterUtil_Plugin_* object
	 */
	public function __construct($config = array())
	{
    	parent::__construct($config);

    	if (isset($config['pairs']) && (!isset($this->pair) || !is_array($this->pair))) {
    		$this->addPairs($config['pairs']);
    	}

    	if ($config['default'] == true || !isset($this->pair) || !is_array($this->pair)) {
    		$this->default = true;
    	}
    }

    /**
     * set the plugin id
     *
     * @access public
     * @param int $id Plugin ID
     */
    public function setID($id)
    {
    	$this->id = $id;
    }

    /**
     * Adds fields to list in common way
     *
     * @access public
     * @param mixed $pairs Pairs to add
     */
    public function addPairs($pairs)
    {
    	if (!is_array($pairs)) {
    		return;
    	}
    	foreach ($pairs as $f => $t) {
    		if (is_array($t)) {
    			$this->addPairs($t);
    		} else {
    			$this->pair[$f] = $t;
    		}
    	}
    }

    /**
     * Get fields in list in common way
     *
     * @access public
     * @return mixed Pairs in list
     */
    public function getPairs()
    {
    	return $this->pair;
    }
}
?>