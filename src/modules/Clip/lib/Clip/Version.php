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
        $modinfo = ModUtil::getInfoFromName($this->getName());
        if ($modinfo['state'] == ModUtil::STATE_ACTIVE) {
            $this->setupPubtypeBundles();
        }
    }

    public function setupPubtypeBundles()
    {
        static $loaded = false; // paranoic check for module upgrade

        if (!$loaded) {
            $pubtypes = Clip_Util::getPubType();

            foreach ($pubtypes as $pubtype) {
                $pubtype->registerHookBundles($this);
            }
            $loaded = true;
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
        $meta['version']        = '0.9.2';
        $meta['core_min']       = '1.3.2';
        $meta['core_max']       = '1.3.99';

        // Capabilities
        $meta['capabilities'] = array(
                HookUtil::SUBSCRIBER_CAPABLE => array('enabled' => true, HookUtil::SUBSCRIBE_OWN => true),
        );

        // Permissions schema
        $meta['securityschema'] = array(
                'Clip::'                   => '::',
                'Clip:grouptypeid:main'    => 'tid::templateid',
                'Clip:grouptypeid:list'    => 'tid::templateid',
                'Clip:grouptypeid:display' => 'tid:pid:templateid',
                'Clip:grouptypeid:edit'    => 'tid::',
                'Clip:grouptypeid:edit'    => 'tid:pid:workflowstate'
        );

        // Module depedencies
        $meta['dependencies'] = array(
                array('modname'    => 'Scribite',
                      'minversion' => '4.2.1',
                      'maxversion' => '',
                      'status'     => ModUtil::DEPENDENCY_RECOMMENDED)
        );

        return $meta;
    }
}
