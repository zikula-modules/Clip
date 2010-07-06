<?php
/**
 * PageMaster
 *
 * @copyright   (c) PageMaster Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

class PageMaster_Version extends Zikula_Version
{
    public function getMetaData()
    {
        $meta = array();
        $meta['displayname']    = __('PageMaster', $dom);
        $meta['description']    = __('Content Module like pagesetter', $dom);
        //! module name that appears in URL
        $meta['url']            = __('pagemaster', $dom);
        $meta['version']        = '0.4.1'; // 10 chars or less
        $meta['contact']        = ' ';

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
                array('modname'    => 'scribite',
                        'minversion' => '2.0',
                        'maxversion' => '',
                        'status'     => PNMODULE_DEPENDENCY_RECOMMENDED),
                array('modname'    => 'Thumbnail',
                        'minversion' => '1.1',
                        'maxversion' => '',
                        'status'     => PNMODULE_DEPENDENCY_RECOMMENDED)
        );
        return $meta;
    }
}
