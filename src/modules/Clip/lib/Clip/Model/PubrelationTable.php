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
class Clip_Model_PubrelationTable extends Clip_Doctrine_Table
{
    public function getClipRelations()
    {
        $relations = $this->selectCollection(array(), 'tid2')
                          ->toArray();

        $result = array(
            'own' => array(),
            'not' => array()
        );

        foreach ($relations as $relation) {
            $result['own'][$relation['tid1']][] = $relation;
            $result['not'][$relation['tid2']][] = $relation;
        }

        return $result;
    }
}
