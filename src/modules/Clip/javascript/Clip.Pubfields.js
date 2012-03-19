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

Zikula.Clip.Pubfields =
{
    Init: function()
    {
        Sortable.create('pubfieldlist',
            {
                dropOnEmpty: true,
                only: 'z-sortable',
                containment:["pubfieldlist"],
                onUpdate: Zikula.Clip.Pubfields.OrderListener
            }
        );

        // also add the anchor to the form
        var form = $$('form.z-form').first();
        //form.action = form.action + '#newpubfield'

        // enable the tooltips
        Zikula.UI.Tooltips($$('.tooltips'));

        // initialize the plugin config
        if ($('pluginConfigButton')) {
            Zikula.Clip.Pubfields.InitPluginConfig();
        } else {
            $('typedata_wrapper').hide();
        }
    },

    InitPluginConfig: function()
    {
        if (Zikula.Clip.Pubfields.Dialog) {
            Zikula.Clip.Pubfields.Dialog.destroy();
            Zikula.Clip.Pubfields.Dialog.container.remove();
        }

        Zikula.Clip.Pubfields.Dialog = new Zikula.UI.Dialog(
                              $('pluginConfigButton'),
                              [
                                  {
                                      'name':  'save',
                                      'class': 'z-bt-ok z-btgreen',
                                      'label': Zikula.__('Save', 'module_clip_js')
                                  },
                                  {
                                      'name':  'cancel',
                                      'class': 'z-bt-cancel z-btred',
                                      label:Zikula.__('Cancel', 'module_clip_js')
                                  }
                              ],
                              {
                                  callback: Zikula.Clip.Pubfields.ConfigCallback,
                                  modal: true,
                                  title: Zikula.__('Plugin configuration', 'module_clip_js'),
                                  width: 600,
                                  overlayOpacity: 0.6
                              }
                          );
    },

    Dialog: null,

    ConfigCallback: function(button)
    {
        switch (button.name) {
            case 'save':
                Zikula.Clip.Pubfields.ConfigSave();
                break;
            case 'cancel':
                Zikula.Clip.Pubfields.ConfigClose();
                break;
        }
    },

    ConfigSave: function()
    {
        Zikula.Clip.Pubfields.ConfigClose();
    },

    ConfigClose: function()
    {
        Zikula.Clip.Pubfields.Dialog.closeHandler();
    },

    OrderListener: function()
    {
        var params = 'module=Clip&type=ajaxexec&func=changelistorder&tid=' + $('clip_tid').innerHTML
                       + '&' + Sortable.serialize('pubfieldlist');

        new Zikula.Ajax.Request(
            'ajax.php',
            {
                method: 'get',
                parameters: params,
                onComplete: Zikula.Clip.Pubfields.OrderResponse
            });
    },

    OrderResponse: function(req)
    {
        if (req.status != 200) {
            Zikula.showajaxerror(req.responseText);
            return;
        }

        /*var json = Zikula.dejsonize(req.responseText);
        Zikula.updateauthids(json.authid);*/

        Zikula.recolor('pubfieldlist', 'pubfieldlistheader');
    }
};
