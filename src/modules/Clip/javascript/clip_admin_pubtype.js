/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Javascript
 */

function clip_pubtype_init()
{
    var i = 0;
    while ($('view_load_'+i)) {
        $('view_load_'+i).observe('change', clip_view_load_onclick);
        i++;
    }
    i = 0;
    while ($('display_load_'+i)) {
        $('display_load_'+i).observe('change', clip_display_load_onclick);
        i++;
    }
    $('view_processrefs').observe('change', clip_view_process_onclick);
    $('display_processrefs').observe('change', clip_display_process_onclick);
    $('edit_load').observe('change', clip_edit_load_onclick);

    clip_view_load_onclick();
    clip_view_process_onclick();
    clip_display_load_onclick();
    clip_display_process_onclick();
    clip_edit_load_onclick();

    new Zikula.UI.Panels('clip-pubtype-form', {
                                               headerSelector: '.pubtype-panel-header',
                                               headerClassName: 'z-panel-indicator',
                                               active: [0]
                                              });
}

function clip_view_load_onclick()
{
    Zikula.radioswitchdisplaystate('view_load', 'view_advancedconfig', true);
}

function clip_view_process_onclick()
{
    Zikula.checkboxswitchdisplaystate('view_processrefs', 'view_advancedprocess', true);
}

function clip_display_load_onclick()
{
    Zikula.radioswitchdisplaystate('display_load', 'display_advancedconfig', true);
}

function clip_display_process_onclick()
{
    Zikula.checkboxswitchdisplaystate('display_processrefs', 'display_advancedprocess', true);
}

function clip_edit_load_onclick()
{
    Zikula.checkboxswitchdisplaystate('edit_load', 'edit_advancedprocess', true);
}
