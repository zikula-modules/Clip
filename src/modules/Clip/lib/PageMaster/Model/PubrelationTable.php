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
class PageMaster_Model_PubrelationTable extends Zikula_Doctrine_Table
{
    public function getRelations()
    {
        $relations = $this->selectCollection('', 'tid2')
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
