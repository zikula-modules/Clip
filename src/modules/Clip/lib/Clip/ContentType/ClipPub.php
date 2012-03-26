<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Installer
 */

/**
 * Clip Pub ContentType.
 */
class Clip_ContentType_ClipPub extends Content_AbstractContentType
{
    protected $tid;
    protected $pid;
    protected $tpl;
    protected $clt;

    // Properties getters and setters
    public function getTid()
    {
        return $this->tid;
    }

    public function setTid($tid)
    {
        $this->tid = $tid;
    }

    public function getPid()
    {
        return $this->pid;
    }

    public function setPid($pid)
    {
        $this->pid = $pid;
    }

    public function getTpl()
    {
        return $this->tpl;
    }

    public function setTpl($tpl)
    {
        $this->tpl = $tpl;
    }

    public function getCtl()
    {
        return $this->clt;
    }

    public function setCtl($clt)
    {
        $this->clt = $clt;
    }

    // Content getters and setters
    public function getTitle()
    {
        return $this->__('Clip publication');
    }

    public function getDescription()
    {
        return $this->__('Display a Clip publication.');
    }

    public function isTranslatable()
    {
        return false;
    }

    public function loadData(&$data)
    {
        $this->tid = $data['tid'];
        $this->pid = $data['pid'];
        $this->tpl = $data['tpl'];
        $this->clt = $data['clt'];
    }

    public function display()
    {
        if (!ModUtil::available('Clip') || !ModUtil::load('Clip')) {
            return;
        }

        $alert = ModUtil::getVar('Clip', 'devmode', false) && Clip_Access::toClip(ACCESS_ADMIN);

        // validation of required parameters
        if (empty($this->tid)) {
            return $alert ? $this->__f('Required parameter [%s] not set or empty.', 'tid') : null;
        }
        if (empty($this->pid)) {
            return $alert ? $this->__f('Required parameter [%s] not set or empty.', 'pid') : null;
        }
        if (!Clip_Util::validateTid($this->tid)) {
            return $alert ? LogUtil::registerError($this->__f('Error! Invalid publication type ID passed [%s].', DataUtil::formatForDisplay($this->tid))) : null;
        }

        $args = array(
            'tid'           => $this->tid,
            'pid'           => $this->pid,
            'template'      => !empty($this->tpl) ? $this->tpl : 'block',
            'cachelifetime' => !empty($this->clt) ? $this->clt : null
        );

        return ModUtil::func('Clip', 'user', 'display', $args);
    }

    public function displayEditing()
    {
        return $this->display();
    }

    public function getDefaultData()
    {
        // default values
        return array(
            'tid' => '',
            'pid' => '',
            'tpl' => '',
            'clt' => null
        );
    }
}
