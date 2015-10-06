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

class PubrelationModelTable extends TableDoctrine
{
    public function getClipRelations()
    {
        $relations = $this->selectCollection(array(), 'tid2')->toArray();
        $result = array('own' => array(), 'not' => array());
        foreach ($relations as $relation) {
            $result['own'][$relation['tid1']][] = $relation;
            $result['not'][$relation['tid2']][] = $relation;
        }
        return $result;
    }

}
