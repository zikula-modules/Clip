<?php?>
<?php/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://github.com/zikula-modules/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage TaggedObjectMeta
 */
namespace Clip\TaggedObjectMeta;

use Clip_Url;
use Clip_Util;
use ModUtil;
use DateUtil;
use UserUtil;
class ClipTaggedObjectMeta extends \\Tag_AbstractTaggedObjectMeta
{
    public function __construct(
        $objectId,
        $areaId,
        $module,
        $urlString = null,
        Zikula_ModUrl $urlObject = null
    ) {
        parent::__construct($objectId, $areaId, $module, $urlString, $urlObject);
        if (!$urlObject instanceof Clip_Url) {
            return;
        }
        Clip_Util::boot();
        $apiargs = array('tid' => $urlObject->getArg('tid'), 'pid' => $urlObject->getArg('pid'), 'array' => true, 'checkperm' => true, 'handleplugins' => false, 'loadworkflow' => false, 'rel' => array());
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

}<?php 