<?php?>
<?php/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://github.com/zikula-modules/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Form
 */
namespace Clip\Form;

use Clip_Util_Selectors;
use ZLanguage;
use Clip_Util_Plugins;
use PageUtil;
class PluginTypeForm extends \\Zikula_Form_Plugin_DropdownList
{
    public function getFilename()
    {
        return __FILE__;
    }
    
    public function __construct($view, &$params)
    {
        $this->autoPostBack = true;
        $this->items = Clip_Util_Selectors::plugins();
        parent::__construct($view, $params);
    }
    
    public function setup()
    {
        $this->setDomain(ZLanguage::getModuleDomain('Clip'));
    }
    
    public function render($render)
    {
        // domain is not settled on postBack
        $this->setDomain(ZLanguage::getModuleDomain('Clip'));
        $this->cssClass = strpos($this->cssClass, 'clip-plugintypeselector') === false ? $this->cssClass . ' clip-plugintypeselector' : $this->cssClass;
        $result = parent::render($render);
        $config = $script = '';
        if (!empty($this->selectedValue) && !empty($this->items)) {
            $plugin = Clip_Util_Plugins::get($this->selectedValue);
            if (method_exists($plugin, 'getConfigHtml')) {
                PageUtil::addVar('javascript', 'zikula.ui');
                $script = '<script type="text/javascript">
//<![CDATA[
';
                $script .= '    Zikula.Clip.Pubfields.ConfigSave = ';
                if (method_exists($plugin, 'getConfigSaveJSFunc')) {
                    $script .= $plugin->getConfigSaveJSFunc($this) . '
';
                } else {
                    $script .= 'function() { Zikula.Clip.Pubfields.ConfigClose(); }' . '
';
                }
                $script .= '
// ]]>
</script>';
                $config = '
                <a id="pluginConfigButton" class="tooltips" href="#pluginConfigDiv" title="' . $this->__('Open the plugin configuration popup') . '"><img src="images/icons/extrasmall/configure.png" alt="' . $this->__('Configuration') . '" /></a>
                <div id="pluginConfigDiv" class="z-form" style="display: none">
                    ' . $plugin->getConfigHtml($this, $render) . '
                </div>';
            }
        }
        return $result . $config . $script;
    }

}<?php 