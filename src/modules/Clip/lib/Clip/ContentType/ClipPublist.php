<?php

/**
 * Content clip list plugin
 *
 * @copyright (C) 2008, Content Development Team
 * @link http://code.zikula.org/content
 * @license See license.txt
 */

class Clip_ContentType_ClipPublist extends Content_ContentType
{
    protected $tid;
    protected $numpubs;
    protected $offset;
    protected $filter;
    protected $order;
    protected $tpl;

    public function getTid()
    {
        return $this->tid;
    }

    public function setTid($tid)
    {
        $this->tid = $tid;
    }

    public function getNumpubs()
    {
        return $this->numpubs;
    }

    public function setNumpubs($numpubs)
    {
        $this->numpubs = $numpubs;
    }

    public function getOffset()
    {
        return $this->offset;
    }

    public function setOffset($offset)
    {
        $this->offset = $offset;
    }

    public function getFilter()
    {
        return $this->filter;
    }

    public function setFilter($filter)
    {
        $this->filter = $filter;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function setOrder($order)
    {
        $this->order = $order;
    }

    public function getTpl()
    {
        return $this->tpl;
    }

    public function setTpl($tpl)
    {
        $this->tpl = $tpl;
    }

    function getTitle()
    {
        return $this->__('Clip publication list');
    }
    function getDescription()
    {
        return $this->__('Clip list of filtered, ordered, and/or formatted publications.');
    }
    function isTranslatable()
    {
        return false;
    }
    function loadData(&$data)
    {
        $this->tid = $data['tid'];
        $this->numpubs = $data['numpubs'];
        $this->offset = $data['offset'];
        $this->filter = $data['filter'];
        $this->order = $data['order'];
        $this->tpl = $data['tpl'];
    }
    function display()
    {
        // retrieve filtered and ordered publication list
        $plargs = array(
            'tid' => $this->tid,
            'noOfItems' => $this->numpubs,
            'offsetItems' => $this->offset,
            'language' => ZLanguage::getLanguageCode(),
            'orderByStr' => $this->order);

        $filters = preg_split("/\s*&\s*/", $this->filter);
        if (is_array($filters) && strlen(trim($filters[0]))) {
            $plargs['filterSet'] = $filters;
        }

        $publist = ModUtil::apiFunc('clip', 'user', 'getPubList', $plargs);

        // retrieve formatted publications
        $publications = array();
        if ($publist !== false) {
            foreach ($publist['publications'] as $pub) {
                $pub = ModUtil::apiFunc('clip', 'user', 'getPubFormatted', array(
                    'tid' => $this->tid,
                    'pid' => $pub['pid'],
                    'format' => $this->tpl,
                    'updateHitCount' => false));
                if ($pub !== false)
                    $publications[] = $pub;
            }
        }

        $this->view->assign('publications', $publications);

        return $this->view->fetch($this->getTemplate());
    }
    function displayEditing()
    {
        $tid = DataUtil::formatForDisplayHTML($this->tid);
        $numpubs = DataUtil::formatForDisplayHTML($this->numpubs);
        $offset = DataUtil::formatForDisplayHTML($this->offset);
        $filter = DataUtil::formatForDisplayHTML($this->filter);
        $order = DataUtil::formatForDisplayHTML($this->order);
        $tpl = DataUtil::formatForDisplayHTML($this->tpl);
    }
    function getDefaultData()
    {
        // deault values
        return array(
            'tid' => ModUtil::getVar('clip', 'frontpagePubType'),
            'numpubs' => 5,
            'offset' => 0,
            'filter' => '',
            'order' => '',
            'tpl' => 'inlineList');
    }
}