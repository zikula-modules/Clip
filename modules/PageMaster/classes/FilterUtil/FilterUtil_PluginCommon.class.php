<?php
/**
 * Zikula Application Framework
 *
 * @copyright  (c) Zikula Development Team
 * @link       http://www.zikula.org
 * @version    $Id: FilterUtil_PluginCommon.class.php 25078 2008-12-17 08:39:04Z Guite $
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @author     Philipp Niethammer <philipp@zikula.org>
 * @category   Zikula_Core
 * @package    Object_Library
 * @subpackage FilterUtil
 */

Loader::loadClass('FilterUtil_PluginCommon', FILTERUTIL_CLASS_PATH);

/**
 * FilterUtil_PluginCommon class
 * 
 * @category   Zikula_Core
 * @package    Object_Library
 * @subpackage FilterUtil
 * @author     Philipp Niethammer <philipp@zikula.org>
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @link       http://www.zikula.org 
 */
class FilterUtil_PluginCommon extends FilterUtil_Common
{
    /**
     * default handler
     */
    protected $default = false;

    /**
     * ID of the plugin
     */
    protected $id;
    
    public function __construct($config)
    {
        parent::__construct($config);
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
}
