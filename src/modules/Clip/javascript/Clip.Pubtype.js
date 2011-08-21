/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Javascript
 */

Zikula.define('Clip');

Zikula.Clip.Pubtype =
{
    Init: function()
    {
        var i = 0;
        while ($('view_load_'+i)) {
            $('view_load_'+i).observe('change', Zikula.Clip.Pubtype.ListenerViewLoad);
            i++;
        }

        $('view_processrefs').observe('change', Zikula.Clip.Pubtype.ListenerViewProcess);

        i = 0;
        while ($('display_load_'+i)) {
            $('display_load_'+i).observe('change', Zikula.Clip.Pubtype.ListenerDisplayLoad);
            i++;
        }

        $('display_processrefs').observe('change', Zikula.Clip.Pubtype.ListenerDisplayProcess);

        $('edit_load').observe('change', Zikula.Clip.Pubtype.ListenerEditLoad);

        Zikula.Clip.Pubtype.ListenerViewLoad();
        Zikula.Clip.Pubtype.ListenerViewProcess();
        Zikula.Clip.Pubtype.ListenerDisplayLoad();
        Zikula.Clip.Pubtype.ListenerDisplayProcess();
        Zikula.Clip.Pubtype.ListenerEditLoad();

        new Zikula.UI.Panels('clip-pubtype-form',
            {
                headerSelector: '.pubtype-panel-header',
                headerClassName: 'z-panel-indicator',
                active: [0,2]
            });
    },

    ListenerViewLoad: function()
    {
        Zikula.radioswitchdisplaystate('view_load', 'view_advancedconfig', true);
    },

    ListenerViewProcess: function()
    {
        Zikula.checkboxswitchdisplaystate('view_processrefs', 'view_advancedprocess', true);
    },

    ListenerDisplayLoad: function()
    {
        Zikula.radioswitchdisplaystate('display_load', 'display_advancedconfig', true);
    },

    ListenerDisplayProcess: function()
    {
        Zikula.checkboxswitchdisplaystate('display_processrefs', 'display_advancedprocess', true);
    },

    ListenerEditLoad: function()
    {
        Zikula.checkboxswitchdisplaystate('edit_load', 'edit_advancedprocess', true);
    }
};
