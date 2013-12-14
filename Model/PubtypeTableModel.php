<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://github.com/zikula-modules/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Model
 */

namespace Clip\Model;

class PubtypeTableModel extends \Clip_Doctrine_Table
{
    public function getPubtypes()
    {
        return $this->selectCollection(
            '',
            'title',
            -1,
            -1,
            'tid'
        );
    }

}