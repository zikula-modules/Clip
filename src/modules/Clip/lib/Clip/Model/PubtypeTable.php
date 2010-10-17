<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Model
 */

/**
 * Doctrine_Table class used to implement own special entity methods.
 */
class Clip_Model_PubtypeTable extends Clip_Doctrine_Table
{
    public function getPubtypes()
    {
        return $this->selectCollection('', 'title', -1, -1, 'tid');
    }
}
