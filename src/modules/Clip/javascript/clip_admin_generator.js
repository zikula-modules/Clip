/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Javascript
 */

Event.observe(window, 'load', clip_generator_init);

function clip_generator_init()
{
    Zikula.UI.Tooltips($$('.tooltips'));

    clip_generator_cleancode.delay(0.5);
}

function clip_generator_cleancode()
{
    // cleanup browser intervention on the generated code
    $('clip_generatorcode').innerHTML = $('clip_generatorcode').innerHTML.gsub(/href="(.*?)"/, function (match) {
        match[1] = match[1].replace(Zikula.Config.baseURL, '');
        return 'href="'+match[1]+'"';
    });
}
