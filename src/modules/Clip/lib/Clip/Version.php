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
        $bundle = new Zikula_Version_HookSubscriberBundle('modulehook_area.clip.item', $this->__('Clip Item Hooks'));
        $bundle->addType('ui.view', 'clip.item.ui.view');
        $bundle->addType('ui.create', 'clip.item.ui.create');
        $bundle->addType('ui.edit', 'clip.item.ui.edit');
        $bundle->addType('validate.create', 'clip.item.validate.create');
        $bundle->addType('validate.update', 'clip.item.validate.update');
        $bundle->addType('validate.delete', 'clip.item.validate.delete');
        $bundle->addType('process.create', 'clip.item.process.create');
        $bundle->addType('process.update', 'clip.item.process.update');
        $bundle->addType('process.delete', 'clip.item.process.delete');

        $bundle = new Zikula_Version_HookSubscriberBundle('modulehook_area.clip.config', $this->__('Clip Config'));
        $bundle->addType('ui.edit', 'clip.config.ui.edit');
        $bundle->addType('validate.update', 'clip.config.validate.update');
        $bundle->addType('process.update', 'clip.config.process.update');
        $this->addHookSubscriberBundle($bundle);

        // TODO register filter hooks
    }

    public function getMetaData()
    {
        $meta = array();
        $meta['displayname']    = $this->__('Clip');
        $meta['description']    = $this->__('Dynamic content publishing platform for Zikula.');
        $meta['oldnames']       = array('PageMaster');
        //! module name that appears in URL
        $meta['url']            = $this->__('clip');
        $meta['version']        = '0.4.8';
        $meta['core_min']       = '1.3.0';

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
