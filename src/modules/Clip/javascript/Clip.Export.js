/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://github.com/zikula-modules/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Javascript
 */

document.observe('dom:loaded', Zikula.Clip.Export.Init);

Zikula.define('Clip');

Zikula.Clip.Export =
{
    Init: function()
    {
        var i = 0;
        while ($('outputto'+i)) {
            $('outputto'+i).observe('change', Zikula.Clip.Export.ListenerFilename);
            i++;
        }

        Zikula.Clip.Export.ListenerFilename()
    },

    ListenerFilename: function()
    {
        Zikula.radioswitchdisplaystate('output_options', 'wrap_filename', false);
    }
};
