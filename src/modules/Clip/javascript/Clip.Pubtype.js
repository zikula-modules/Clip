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
        while ($('list_load_'+i)) {
            $('list_load_'+i).observe('change', Zikula.Clip.Pubtype.ListenerViewLoad);
            i++;
        }

        i = 0;
        while ($('display_load_'+i)) {
            $('display_load_'+i).observe('change', Zikula.Clip.Pubtype.ListenerDisplayLoad);
            i++;
        }

        $('edit_load').observe('change', Zikula.Clip.Pubtype.ListenerEditLoad);

        Zikula.Clip.Pubtype.ListenerViewLoad();
        Zikula.Clip.Pubtype.ListenerDisplayLoad();
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
        Zikula.radioswitchdisplaystate('list_load', 'list_advancedconfig', true);
    },

    ListenerDisplayLoad: function()
    {
        Zikula.radioswitchdisplaystate('display_load', 'display_advancedconfig', true);
    },

    ListenerEditLoad: function()
    {
        Zikula.checkboxswitchdisplaystate('edit_load', 'edit_advancedprocess', true);
    }
};
