<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage TaggedObjectMeta
 */

/**
 * Class used by Tag to display Clip's publications details.
 *
 * The methods of this class behaves as Smarty plugins.
 * The 'assign' parameter is handled automatically.
 */
class Clip_TaggedObjectMeta_Clip extends Tag_AbstractTaggedObjectMeta
{
    function __construct($objectId, $areaId, $module, $urlString = null, Zikula_ModUrl $urlObject = null)
    {
        parent::__construct($objectId, $areaId, $module, $objectUrl, $urlObject);

        if (!$urlObject instanceof Clip_Url) {
            return;
        }

        Clip_Util::boot();

        $apiargs = array(
            'tid'           => $urlObject->getArg('tid'),
            'pid'           => $urlObject->getArg('pid'),
            'array'         => true,
            'checkperm'     => true,
            'handleplugins' => false,
            'loadworkflow'  => false,
            'rel'           => array()
        );

        $apiargs['where'] = array();
        //if (!Clip_Access::toPubtype($apiargs['tid'], 'editor')) {
            $apiargs['where'][] = array('core_online = ?', 1);
            $apiargs['where'][] = array('core_intrash = ?', 0);
        //}

        $pubdata = ModUtil::apiFunc('Clip', 'user', 'get', $apiargs);

        if ($pubdata) {
            $this->setObjectTitle($pubdata['core_title']);
            $this->setObjectDate($pubdata['core_publishdate']);
            $this->setObjectAuthor($pubdata['core_author']);
            $this->setObjectUrl(ModUtil::url('Clip', 'user', 'display', array('tid' => $tid, 'pid' => $pid)));
        }
    }

    public function setObjectTitle($title)
    {
        $this->title = $title;
    }

    public function setObjectDate($date)
    {
        $this->date = DateUtil::formatDatetime($date, 'datetimebrief');
    }

    public function setObjectAuthor($uid)
    {
        $this->author = UserUtil::getVar('uname', $uid);
    }
}
