// Copyright Clip Team 2011 - license GNU/LGPLv3 (or at your option, any later version).

/* Clip load */
Zikula.define('Clip');

/* Ajax view functions */
Zikula.Clip.Ajax =
{
    Busy: false,
    Container: null,

    Request: function(pars, func, type, container, callback)
    {
        if (Zikula.Clip.Ajax.Busy || typeof pars != 'object' || typeof pars['tid'] == 'undefined') {
            return;
        }

        if (container) {
            Zikula.Clip.Ajax.Container = $(container);
        }

        if (!Zikula.Clip.Ajax.Container) {
            return;
        }

        Zikula.Clip.Ajax.showIndicator();

        pars.module = 'Clip';
        pars.type   = type ? type : 'ajax';
        pars.func   = func ? func : 'list';

        // perform the ajax request
        new Zikula.Ajax.Request(
            'ajax.php?lang='+Zikula.Config.lang,
            {
                method: 'get',
                parameters: pars,
                onComplete: callback ? callback : Zikula.Clip.Ajax.Callback
            });
    },

    Callback: function(req)
    {
        Zikula.Clip.Ajax.hideIndicator();

        if (!req.isSuccess()) {
            Zikula.Clip.Ajax.Busy = false;
            Zikula.showajaxerror(req.getMessage());
            return false;
        }

        Zikula.Clip.Ajax.Container.update(req.getData());

        Zikula.Clip.Ajax.Busy = false;

        return true;
    },

    indicator: null,
    showIndicatorTimeout: null,

    showIndicator: function()
    {
        if (!Zikula.Clip.Ajax.indicator) {
            Zikula.Clip.Ajax.indicator = new Element('div', {class: 'z-window-indicator', display: 'none'});
            Zikula.Clip.Ajax.Container.insert('before', Zikula.Clip.Ajax.indicator);
        }

        Zikula.Clip.Ajax.showIndicatorTimeout = window.setTimeout(function(){
            Zikula.Clip.Ajax.indicator.show();
        }.bind(this), 50);
    },

    hideIndicator: function()
    {
        if (Zikula.Clip.Ajax.showIndicatorTimeout) {
            window.clearTimeout(Zikula.Clip.Ajax.showIndicatorTimeout);
        }
        Zikula.Clip.Ajax.indicator.hide();
    }
};
