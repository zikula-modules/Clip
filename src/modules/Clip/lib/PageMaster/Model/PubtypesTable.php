<?php
/**
 * PageMaster
 *
 * @copyright   (c) PageMaster Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package     Zikula_3rdParty_Modules
 * @subpackage  pagemaster
 */

/**
 * Doctrine_Table class used to implement own special entity methods.
 */
class PageMaster_Model_PubtypesTable extends Zikula_Doctrine_Table
{
    public function getPubtypes()
    {
        return $this->selectCollection('', 'title', -1, -1, 'tid');
    }
}