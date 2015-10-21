<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://github.com/zikula-modules/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Form_Plugin
 */

namespace Matheo\Clip\Form\Plugin;

use ZLanguage;

class BigInt extends \Clip_Form_Plugin_Int
{
    // plugin definition
    public $columnDef = 'I8';
    public function setup()
    {
        $this->setDomain(ZLanguage::getModuleDomain('Clip'));
        //! field type name
        $this->pluginTitle = $this->__('Big Integer');
    }
    
    public function getFilename()
    {
        return __FILE__;
    }

}
