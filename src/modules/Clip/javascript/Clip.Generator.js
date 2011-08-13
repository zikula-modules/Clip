/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Javascript
 */

document.observe('dom:loaded', Zikula.Clip.Generator.Init);

Zikula.define('Clip');

Zikula.Clip.Generator =
{
    Init: function()
    {
        Zikula.UI.Tooltips($$('.tooltips'));

        Zikula.Clip.Generator.CleanCode.delay(0.5);
    },

    CleanCode: function()
    {
        // cleanup browser intervention on the generated code
        $('clip_generatorcode').innerHTML = $('clip_generatorcode').innerHTML.gsub(/href="(.*?)"/, function (match) {
            match[1] = match[1].replace(Zikula.Config.baseURL, '');
            return 'href="'+match[1]+'"';
        });
        $('clip_generatorcode').innerHTML = $('clip_generatorcode').innerHTML.gsub(/src="(.*?)"/, function (match) {
            match[1] = match[1].replace(Zikula.Config.baseURL, '');
            return 'src="'+match[1]+'"';
        });
    }
};
