<?php

class Clip_ContentType_ClipPub extends Content_AbstractContentType
{
    protected $tid;
    protected $pid;
    protected $tpl;

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
    }
    public function display()
    {
        $tid = DataUtil::formatForDisplayHTML($this->tid);
        $pid = DataUtil::formatForDisplayHTML($this->pid);
        $tpl = DataUtil::formatForDisplayHTML($this->tpl);

        $url = ModUtil::url('Clip', 'user', 'display', array('tid' => $tid, 'pid' => $pid));
        $url = htmlspecialchars($url);

        // get the formatted publication
        $publication = ModUtil::apiFunc('Clip', 'user', 'getPubFormatted', array(
            'tid' => $tid,
            'pid' => $pid,
            'format' => $tpl,
            'useTransformHooks' => false,
            'coreExtra' => array(
                'page' => 0,
                'baseURL' => $url,
                'format' => $tpl)));

        // render instance - assign publication
        $this->view->assign('publication', $publication);

        return $this->view->fetch($this->getTemplate());
    }
    public function displayEditing()
    {
        $tid = DataUtil::formatForDisplayHTML($this->tid);
        $pid = DataUtil::formatForDisplayHTML($this->pid);
        $tpl = DataUtil::formatForDisplayHTML($this->tpl);

        $url = ModUtil::url('clip', 'user', 'list', array('tid' => $tid, 'pid' => $pid));
        $url = htmlspecialchars($url);

        // get the formatted publication
        $publication = ModUtil::apiFunc('clip', 'user', 'getPubFormatted', array(
            'tid' => $tid,
            'pid' => $pid,
            'format' => $tpl,
            'useTransformHooks' => false,
            'coreExtra' => array(
                'page' => 0,
                'baseURL' => $url,
                'format' => $tpl)));

        $this->view->assign('publication', $publication);

        return $this->view->fetch($this->getTemplate()); // not getEditTemplate??
    }
    public function getDefaultData()
    {
        // deault values
        return array('tid' => ModUtil::getVar('clip', 'frontpagePubType'), 'pid' => '', 'tpl' => 'full');
    }
}