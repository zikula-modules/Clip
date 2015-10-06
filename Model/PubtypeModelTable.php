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

namespace Matheo\Clip\Model;

use Matheo\Clip\Doctrine\TableDoctrine;

class PubtypeModelTable extends TableDoctrine
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
