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
 * Clip Publist ContentType.
 */
class Clip_ContentType_ClipPublist extends Content_AbstractContentType
{
    protected $tid;
    protected $orderby;
    protected $orderdir;
    protected $filter;
    protected $numitems;
    protected $offset;
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

    public function getOrderby()
    {
        return $this->orderby;
    }

    public function setOrderby($orderby)
    {
        $this->orderby = $orderby;
    }

    public function getOrderdir()
    {
        return $this->orderdir;
    }

    public function setOrderdir($orderdir)
    {
        $this->orderdir = $orderdir;
    }

    public function getFilter()
    {
        return $this->filter;
    }

    public function setFilter($filter)
    {
        $this->filter = $filter;
    }

    public function getNumitems()
    {
        return $this->numitems;
    }

    public function setNumitems($numitems)
    {
        $this->numitems = $numitems;
    }

    public function getOffset()
    {
        return $this->offset;
    }

    public function setOffset($offset)
    {
        $this->offset = $offset;
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
        return $this->__('Clip publication list');
    }

    public function getDescription()
    {
        return $this->__('Clip list of filtered, ordered and limited publications.');
    }

    public function isTranslatable()
    {
        return false;
    }

    public function loadData(&$data)
    {
        $this->tid      = $data['tid'];
        $this->orderby  = $data['orderby'];
        $this->orderdir = $data['orderdir'];
        $this->filter   = $data['filter'];
        $this->numitems = isset($data['numpubs']) ? $data['numpubs'] : $data['numitems'];
        $this->offset   = $data['offset'];
        $this->tpl      = $data['tpl'];
        $this->clt      = $data['clt'];
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
        if (!Clip_Util::validateTid($this->tid)) {
            return $alert ? LogUtil::registerError($this->__f('Error! Invalid publication type ID passed [%s].', DataUtil::formatForDisplay($this->tid))) : null;
        }

        $orderstr = !empty($this->orderby) ? $this->orderby . (!$this->orderdir ? ':desc' : ':asc') : '';

        $args = array(
            'tid'           => $this->tid,
            'orderby'       => $orderstr,
            'filter'        => !empty($this->filter) ? $this->filter : '()',
            'itemsperpage'  => (int)$this->numitems > 0 ? $this->numitems : 5,
            'startnum'      => !empty($this->offset) ? $this->offset : 0,
            'template'      => !empty($this->tpl) ? $this->tpl : 'block',
            'cachelifetime' => isset($this->clt) ? $this->clt : null
        );

        return ModUtil::func('Clip', 'user', 'list', $args);
    }

    public function displayEditing()
    {
        return $this->display();
    }

    public function getDefaultData()
    {
        // default values
        return array(
            'tid'      => '',
            'orderby'  => '',
            'orderdir' => 0,
            'filter'   => '',
            'numitems' => 5,
            'offset'   => 0,
            'tpl'      => '',
            'clt'      => null
        );
    }
}
