<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2008, Zikula Development Team
 * @link http://www.zikula.org
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_Generated_Modules
 * @subpackage Pagemaster
 * @author Axel Guckelsberger
 * @url http://modulestudio.de
 */

/*
 * generated at Sun Aug 03 14:43:13 CEST 2008 by ModuleStudio 0.4.10 (http://modulestudio.de)
 */


Loader::loadClass('PagemasterFilterUtil_Common', Pagemaster_FILTERUTIL_CLASS_PATH);

class PagemasterFilterUtil_ReplaceCommon extends PagemasterFilterUtil_Common {
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
     * @return object PagemasterFilterUtil_Plugin_* object
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
