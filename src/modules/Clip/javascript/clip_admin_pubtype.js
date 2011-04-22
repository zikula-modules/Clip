/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Javascript
 */

Event.observe(window, 'load', clip_pubtype_init);

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
    clip_urltitle_init();
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

function clip_urltitle_init()
{
    $('clip_pubtype_collapse').observe('click', clip_urltitle_click);
    $('clip_pubtype_collapse').addClassName('z-toggle-link');
    if (!$('urltitle').hasClassName('z-form-error')) {
        clip_urltitle_click();
    }
}

function clip_urltitle_click()
{
    if ($('clip_pubtype_urltitle').style.display != 'none') {
        Element.removeClassName.delay(0.9, $('clip_pubtype_collapse'), 'z-toggle-link-open');
    } else {
        $('clip_pubtype_collapse').addClassName('z-toggle-link-open');
    }
    Zikula.switchdisplaystate('clip_pubtype_urltitle');
}
