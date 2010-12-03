/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Javascript
 */

Event.observe(window, 'load', clip_export_init);

function clip_export_init()
{
    var i = 0;
    while ($('outputto'+i)) {
        $('outputto'+i).observe('change', clip_filename_onclick);
        i++;
    }
    clip_filename_onclick()
}

function clip_filename_onclick()
{
    Zikula.radioswitchdisplaystate('output_options', 'wrap_filename', false);
}
