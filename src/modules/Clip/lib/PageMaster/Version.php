<?php
/**
 * PageMaster
 *
 * @copyright (c) PageMaster Team
 * @link      http://code.zikula.org/pagemaster/
 * @license   GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

/**
 * PageMaster Version Info.
 */
class PageMaster_Version extends Zikula_Version
{
    public function getMetaData()
    {
        $meta = array();
        $meta['displayname']    = $this->__('PageMaster');
        $meta['description']    = $this->__('Content Module like pagesetter');
        $meta['oldnames']       = array('pagemaster');
        //! module name that appears in URL
        $meta['url']            = $this->__('pagemaster');
        $meta['version']        = '0.4.3';
        $meta['core_min']       = '1.3.0';

        // Permissions schema
        $meta['securityschema'] = array(
                'pagemaster::'      => '::',
                'pagemaster:input:' => 'tid::',
                'pagemaster:input:' => 'tid:pid:workflowstate',
                'pagemaster:full:'  => 'tid:pid:template',
                'pagemaster:list:'  => 'tid::template'
        );

        // Module depedencies
        $meta['dependencies'] = array(
                array('modname'    => 'Scribite',
                      'minversion' => '4.2.1',
                      'maxversion' => '',
                      'status'     => ModUtil::DEPENDENCY_RECOMMENDED),
                array('modname'    => 'Thumbnail',
                      'minversion' => '1.1',
                      'maxversion' => '',
                      'status'     => ModUtil::DEPENDENCY_RECOMMENDED),
        );

        return $meta;
    }
}
