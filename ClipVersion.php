<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://github.com/zikula-modules/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Version
 */

namespace Matheo\Clip;

use ModUtil;
use Matheo\Clip\Util;
use HookUtil;

class ClipVersion extends \Zikula_AbstractVersion
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
        static $loaded = false;
        // paranoic check for module upgrade
        if (!$loaded) {
            $pubtypes = Util::getPubType();
            foreach ($pubtypes as $pubtype) {
                $pubtype->registerHookBundles($this);
            }
            $loaded = true;
        }
    }
    
    public function getMetaData()
    {
        $meta = array();
        $meta['displayname'] = $this->__('Clip');
        $meta['description'] = $this->__('Dynamic content publishing platform for Zikula.');
        $meta['oldnames'] = array('PageMaster', 'Clip');
        //! module name that appears in URL
        $meta['url'] = $this->__('clip');
        $meta['version'] = '0.9.4';
        $meta['core_min'] = '1.4.1';
        $meta['core_max'] = '1.4.99';
        // Capabilities
        $meta['capabilities'] = array(HookUtil::SUBSCRIBER_CAPABLE => array('enabled' => true, HookUtil::SUBSCRIBE_OWN => true));
        // Permissions schema
        $meta['securityschema'] = array('Clip::' => '::', 'Clip:grouptypeid:main' => 'tid::templateid', 'Clip:grouptypeid:list' => 'tid::templateid', 'Clip:grouptypeid:display' => 'tid:pid:templateid', 'Clip:grouptypeid:edit' => 'tid::', 'Clip:grouptypeid:edit' => 'tid:pid:workflowstate');
        // Module depedencies
        $meta['dependencies'] = array(array('modname' => 'Scribite', 'minversion' => '4.2.1', 'maxversion' => '', 'status' => ModUtil::DEPENDENCY_RECOMMENDED));
        return $meta;
    }

}
