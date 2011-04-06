<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Version
 */

/**
 * Clip Version Info.
 */
class Clip_Version extends Zikula_AbstractVersion
{
    protected function setupHookBundles()
    {
        $pubtypes = Clip_Util::getPubType();

        foreach ($pubtypes as $pubtype) {
            $pubtype->registerHookBundles($this);
        }
    }

    public function getMetaData()
    {
        $meta = array();
        $meta['displayname']    = $this->__('Clip');
        $meta['description']    = $this->__('Dynamic content publishing platform for Zikula.');
        $meta['oldnames']       = array('PageMaster');
        //! module name that appears in URL
        $meta['url']            = $this->__('clip');
        $meta['version']        = '0.4.12';
        $meta['core_min']       = '1.3.0';

        // Capabilities
        $meta['capabilities'] = array(
                HookUtil::SUBSCRIBER_CAPABLE => array('enabled' => true)
        );

        // Permissions schema
        $meta['securityschema'] = array(
                'Clip::'      => '::',
                'Clip:input:' => 'tid::',
                'Clip:input:' => 'tid:pid:workflowstate',
                'Clip:full:'  => 'tid:pid:template',
                'Clip:list:'  => 'tid::template'
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
