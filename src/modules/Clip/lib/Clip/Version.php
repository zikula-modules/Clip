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
class Clip_Version extends Zikula_Version
{
    protected function setupHookBundles()
    {
        $this->registerHookSubscriberBundle($bundle);
        $bundle = new Zikula_Version_HookSubscriberBundle('modulehook_area.clip.item', $this->__('Clip Item Hooks'));
        $bundle->addType('ui.view', 'clip.hook.item.ui.view');
        $bundle->addType('ui.edit', 'clip.hook.item.ui.edit');
        $this->registerHookSubscriberBundle($bundle);

        // filter hooks
        $bundle = new Zikula_Version_HookSubscriberBundle('modulehook_area.clip.articlesfilter', $this->__('Filter articles'));
        $bundle->addType('ui.filter', 'clip.hook.articlesfilter.filter');
        $this->registerHookSubscriberBundle($bundle);
    }

    public function getMetaData()
    {
        $meta = array();
        $meta['displayname']    = $this->__('Clip');
        $meta['description']    = $this->__('Dynamic content publishing platform for Zikula.');
        $meta['oldnames']       = array('PageMaster');
        //! module name that appears in URL
        $meta['url']            = $this->__('clip');
        $meta['version']        = '0.4.9';
        $meta['core_min']       = '1.3.0';

        // Capabilities
        $meta['capabilities'] = array(
                HookUtil::SUBSCRIBER_CAPABLE => array('enabled' => true)
        );

        // Permissions schema
        $meta['securityschema'] = array(
                'clip::'      => '::',
                'clip:input:' => 'tid::',
                'clip:input:' => 'tid:pid:workflowstate',
                'clip:full:'  => 'tid:pid:template',
                'clip:list:'  => 'tid::template'
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
