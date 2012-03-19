// Copyright Clip Team 2011 - license GNU/LGPLv3 (or at your option, any later version).

/* Clip load */
Zikula.define('Clip');

Event.observe(window, 'load', function()
{
    Zikula.Clip.TreeSortable.trees.grouptypesTree.config.onSave = Zikula.Clip.Resequence.Listener;

    Zikula.Clip.Container.register();

    Zikula.UI.Tooltips($$('.tree a'));

    // group buttons
    $('groupNew').observe('click', function(e) {
        e.findElement('a').insert({after: Zikula.Clip.Indicator()});
        e.preventDefault();
        Zikula.Clip.MenuAction(null, 'addroot');
    });
    $('groupExpand').observe('click', function(e) {
        e.preventDefault();
        Zikula.Clip.TreeSortable.trees.grouptypesTree.expandAll();
    });
    $('groupCollapse').observe('click', function(e) {
        e.preventDefault();
        Zikula.Clip.TreeSortable.trees.grouptypesTree.collapseAll();
    });

    $('groupControls').removeClassName('z-hide');

    Zikula.Clip.AttachMenu();

    // hash behaviors
    Event.observe(window, 'hashchange', Zikula.Clip.Hash.Listener)

    Zikula.Clip.Hash.Listener();
});

/* Hash manager */
Zikula.Clip.Hash = 
{
    Listener: function()
    {
        if (Zikula.Clip.Ajax.Busy || window.location.hash.empty() || window.location.hash == '#') {
            return;
        }

        var hash = window.location.hash;
        var args = hash.replace('#', '').split('/');
        var pars = {'tid': args[0]};
        var func = (typeof args[1] != 'undefined') ? args[1] : null;
        // additional parameters
        if (args.size() > 2 && args.size() % 2 == 0) {
            for (var i = 2; i < args.size(); i = i+2) {
                var key = args[i];
                pars[key] = args[i+1];
            }
        }

        Zikula.Clip.Ajax.Request(pars, func)
    },

    Update: function(pars, func)
    {
        Zikula.Clip.Ajax.Busy = true;

        var newhash = '';
        for (var i in pars) {
            if (i != 'tid') {
                newhash += '/'+i+'/'+pars[i];
            }
        }

        newhash = pars.tid+'/'+func+newhash;

        window.location.hash = newhash;
    }
};

/* Customization of TreeSortable */
Zikula.Clip.TreeSortable = Class.create(Zikula.TreeSortable,/** @lends Zikula.TreeSortable.prototype */
{
    /**
     * Redraws selected node - sets proper class names on node, removes orphaned ul elements
     * @private
     * @param {HTMLElement} node Node to draw
     * @return void
     */
    drawNode: function ($super, node)
    {
        $super(node);
        var a = node.down('a'), id = Zikula.Clip.TreeSortable.trees.grouptypesTree.getNodeId(node);
        if (id != parseInt(id)) {
            id = id.split('-')[1];
            a.writeAttribute('onClick', 'javascript:if (!Zikula.Clip.Ajax.Busy) { this.insert({after: Zikula.Clip.Indicator()}); Zikula.Clip.Ajax.Request({tid:\''+id+'\'}, \'pubtypeinfo\'); } return false;');
        } else {
            a.writeAttribute('onClick', 'return false;');
        }
    }
});

Object.extend(Zikula.Clip.TreeSortable,/** @lends Zikula.Clip.TreeSortable.prototype */
{
    /**
     * List of initilized trees.
     * Trees initilized via add method are avaiable as Zikula.Clip.TreeSortable.trees[element.id]
     * @static
     * @name Zikula.Clip.TreeSortable.trees
     */
    trees: {},
    /**
     * Static method allowing to initialize global available Zikula.Clip.TreeSortable instances
     * @see Zikula.Clip.TreeSortable construct for details
     * @static
     * @name Zikula.Clip.TreeSortable.add
     * @function
     * @param {HTMLElement|String} element Element id or reference
     * @param {Object} [config] Config object
     * @retun void
     */
    add: function(element, config)
    {
        if (!this.trees.hasOwnProperty(element)) {
            this.trees[element] = new Zikula.Clip.TreeSortable(element, config);
            this.trees[element].drawNodes();
        }
    }
});

Zikula.Clip.Container = Class.create(
{
    initialize: function(options)
    {
        this.indicatorEffects = {
            fade: false,
            appear: false
        };
        //options
        this.options = Object.extend({
            indicator: false,
            sidecol: null,
            content: null,
            fade: true,
            fadeDuration: 0.75
        }, options || {});

        this.indicator = this.options.indicator ? $(this.options.indicator) : $('clip_cols_indicator');
        this.sidecol   = this.options.sidecol ? $(this.options.sidecol) : $('clip_cols_sidecol');
        this.content   = this.options.content ? $(this.options.content) : $('clip_cols_maincontent');
    },

    updateHeights: function()
    {
        window.setTimeout(function() {
            this.content.removeAttribute('style');
            this.sidecol.removeAttribute('style');
            var max = Math.max(300, this.content.getHeight(), side = this.sidecol.getHeight());
            this.content.setAttribute('style', "min-height: "+max+"px");
            this.sidecol.setAttribute('style', "min-height: "+max+"px");
        }.bind(this), 150);
    },

    updateContent: function(content)
    {
        if (content) {
            // handle the content according the function
            switch (Zikula.Clip.Container.func)
            {
                case 'pubtype':
                    this.content.update(content);
                    Zikula.Clip.Pubtype.Init();
                    break;

                case 'pubfields':
                    this.content.update(content);
                    this.content.innerHTML = this.content.innerHTML.replace('FormDoPostBack', 'Zikula.Clip.Container.instance.formPostBack');
                    Zikula.Clip.Pubfields.Init();
                    break;

                case 'generator':
                    // detection of clip autogenerated code
                    var clipcode = '';
                    content = content.sub(/<script id="clip_generatorcode" type="text\/html">([\s\S\/]+)<pre class="clip-generatorcode">/, function (match) {
                        clipcode = match[0].sub(/<script id="clip_generatorcode" type="text\/html">/, '').sub(/<\/script>([\s\S]+)$/, '');
                        return '<pre class="clip-generatorcode">';
                    })

                    this.content.update(content);

                    // if there's autogenerated code, insert it appart'
                    if (clipcode != '') {
                        var script = document.createElement('script');
                        script.id  = 'clip_generatorcode';
                        script.type = 'text/html';
                        script.update(clipcode);
                        this.content.appendChild(script);
                        $('clip_generatorcode').innerHTML = $('clip_generatorcode').innerHTML.gsub(/href="(.*?)"/, function (match) {
                            match[1] = match[1].replace(Zikula.Config.baseURL, '');
                            return 'href="'+match[1]+'"';
                        });
                        $('clip_generatorcode').innerHTML = $('clip_generatorcode').innerHTML.gsub(/src="(.*?)"/, function (match) {
                            match[1] = match[1].replace(Zikula.Config.baseURL, '');
                            return 'src="'+match[1]+'"';
                        });
                    }
                    break;

                default:
                    this.content.update(content);
            }
            // observe the forms submit buttons
            switch (Zikula.Clip.Container.func)
            {
                case 'pubtype':
                case 'pubfields':
                case 'relations':
                    var form = this.content.select('form').first();
                    // observe the form buttons
                    form.select('input[type=submit]').each(function(e) {
                        // replace any onclick attribute
                        if (e.onclick) {
                            e.ajaxclick = e.onclick;
                            e.removeAttribute('onclick');
                            e.observe('click', function(event) {
                                if (e.ajaxclick()) {
                                    this.formSend(event)
                                } else {
                                    event.stop();
                                }
                            }.bind(this));
                        } else {
                            e.observe('click', this.formSend.bind(this));
                        }
                    }.bind(this));
                    Zikula.Clip.Container.formid = form.identify();
            }
            // update the loaded tooltips on the ajax content
            Zikula.UI.Tooltips(this.content.getElementsBySelector('.tooltips'));
        }
        this.updateHeights();
        this.hideIndicator();
    },

    formSend: function(event)
    {
        event.stop();

        Zikula.Clip.Ajax.Busy = true;
        this.showIndicator();

        var submit = event.findElement();
        var form   = event.findElement('form');

        form.select('input[type=submit]').each(function(e) {
            if (e != submit) {
                e.disable();
            }
        });

        new Zikula.Ajax.Request(
            form.action,
            {
                method: 'post',
                parameters: form.serialize(),
                onComplete: this.formProcess
            });

        form.select('input[type=submit]').invoke('enable');
    },

    formProcess: function(req)
    {
        Zikula.Clip.Ajax.Busy = false;

        if (!req.isSuccess()) {
            Zikula.showajaxerror(req.getMessage());
            Zikula.Clip.Container.instance.hideIndicator();
            return false;
        }

        if (req.getData()) {
            Zikula.Clip.Container.instance.updateContent(req.getData());

        } else {
            var result = req.decodeResponse();

            if (result.output) {
                // custom function output
                if (result.func) {
                    Zikula.Clip.Container.func = result.func;
                }
                if (result.pars) {
                    Zikula.Clip.Container.pars = result.pars;
                }
                Zikula.Clip.Container.instance.updateContent(result.output);

            } else if (result.redirect) {
                // url redirect
                window.location = result.redirect;

            } else {
                // redirect to a specified function or pubtypeinfo (on cancel)
                Zikula.Clip.Container.func = result.func ? result.func : 'pubtypeinfo';
                if (result.pars) {
                    Zikula.Clip.Container.pars = result.pars;
                }
                Zikula.Clip.Ajax.Request(Zikula.Clip.Container.pars, Zikula.Clip.Container.func);
            }

            // TODO update the hash
            // busy enabled should change the hash without problem, but it's not, it's generating a new ajax request
            //Zikula.Clip.Hash.Update(Zikula.Clip.Container.pars, Zikula.Clip.Container.func);
            //Zikula.Clip.Ajax.Busy = false;
        }

        return true;
    },

    formPostBack: function(eventTarget, eventArgument)
    {
        var form = $(Zikula.Clip.Container.formid);

        if (!form.onsubmit || form.onsubmit()) {
            Zikula.Clip.Ajax.Busy = true;
            Zikula.Clip.Container.instance.showIndicator();

            form.FormEventTarget.value = eventTarget;
            form.FormEventArgument.value = eventArgument;

            form.select('input[type=submit]').invoke('disable');

            new Zikula.Ajax.Request(
                form.action,
                //'ajax.php?module=Clip&type=ajax&func='+Zikula.Clip.Container.func+'&tid='+Zikula.Clip.Container.pars.tid+'&lang='+Zikula.Config.lang,
                {
                    method: 'post',
                    parameters: form.serialize(),
                    onComplete: Zikula.Clip.Container.instance.formProcess
                });

            form.select('input[type=submit]').invoke('enable');
        }
    },

    showIndicator: function()
    {
        this.showIndicatorTimeout = window.setTimeout(function(){
            if (this.options.fade){
                this.indicatorEffects.appear = new Effect.Appear(this.indicator, {
                    queue: {
                        position: 'front',
                        scope: 'Zikula.Clip.Container.1'
                    },
                    from: 0,
                    to: 1,
                    duration: this.options.fadeDuration / 2
                });
            } else {
                this.indicator.show();
            }
        }.bind(this), 50);
    },

    hideIndicator: function()
    {
        if (this.showIndicatorTimeout) {
            window.clearTimeout(this.showIndicatorTimeout);
        }
        this.indicator.hide();
    }
});

Object.extend(Zikula.Clip.Container,
{
    instance: null,
    formid: null,
    busy: false,
    func: '',
    pars: {
        tid: 0,
        id: 0
    },
    register: function(config)
    {
        if (!this.instance) {
            this.instance = new Zikula.Clip.Container(config);
            this.instance.updateHeights();
        }
    }
});

/* Context Menu */
Zikula.Clip.AttachMenu = function ()
{
    Zikula.Clip.ContextMenu = new Control.ContextMenu(Zikula.Clip.TreeSortable.trees.grouptypesTree.tree, {
        animation: false,
        beforeOpen: function(event) {
            Zikula.Clip.ContextMenu.lastClick = event;
            if (!event.findElement('a')) {
                throw $break;
            }
            var node = event.findElement('a').up('li');
            var id   = Zikula.Clip.TreeSortable.trees.grouptypesTree.getNodeId(node);
            Zikula.Clip.ContextMenu.isGrouptype = (id != parseInt(id)) ? false : true;
        }
    });

    /* Grouptype links */
    Zikula.Clip.ContextMenu.addItem({
        label: Zikula.__('Edit', 'module_clip_js'),
        condition: function() {
            return Zikula.Clip.ContextMenu.isGrouptype;
        },
        callback: function(node) {
            Zikula.Clip.MenuAction(node, 'edit');
        }
    });
    Zikula.Clip.ContextMenu.addItem({
        label: Zikula.__('Delete', 'module_clip_js'),
        condition: function() {
            return Zikula.Clip.ContextMenu.isGrouptype && !Zikula.Clip.ContextMenu.lastClick.findElement('a').up('li').down('ul');
        },
        callback: function(node){
            Zikula.Clip.DeleteMenuAction(node);
        }
    });
    Zikula.Clip.ContextMenu.addItem({
        label: Zikula.__('Add group after selected', 'module_clip_js'),
        condition: function() {
            return Zikula.Clip.ContextMenu.isGrouptype;
        },
        callback: function(node){
            Zikula.Clip.MenuAction(node, 'addafter');
        }
    });
    Zikula.Clip.ContextMenu.addItem({
        label: Zikula.__('Add subgroup into selected', 'module_clip_js'),
        condition: function() {
            return Zikula.Clip.ContextMenu.isGrouptype;
        },
        callback: function(node){
            Zikula.Clip.MenuAction(node, 'addchild');
        }
    });
    /* Pubtype links */
    Zikula.Clip.ContextMenu.addItem({
        label: Zikula.__('Edit', 'module_clip_js'),
        condition: function() {
            return !Zikula.Clip.ContextMenu.isGrouptype;
        },
        callback: function(node) {
            node.insert({after: Zikula.Clip.Indicator()});
            var tid = Zikula.Clip.TreeSortable.trees.grouptypesTree.getNodeId(node.up('li')).split('-')[1];
            Zikula.Clip.Ajax.Request({'tid':tid}, 'pubtype');
        }
    });
    Zikula.Clip.ContextMenu.addItem({
        label: Zikula.__('Fields', 'module_clip_js'),
        condition: function() {
            return !Zikula.Clip.ContextMenu.isGrouptype;
        },
        callback: function(node) {
            node.insert({after: Zikula.Clip.Indicator()});
            var tid = Zikula.Clip.TreeSortable.trees.grouptypesTree.getNodeId(node.up('li')).split('-')[1];
            Zikula.Clip.Ajax.Request({'tid':tid}, 'pubfields');
        }
    });
    Zikula.Clip.ContextMenu.addItem({
        label: Zikula.__('Relations', 'module_clip_js'),
        condition: function() {
            return !Zikula.Clip.ContextMenu.isGrouptype;
        },
        callback: function(node) {
            node.insert({after: Zikula.Clip.Indicator()});
            var tid = Zikula.Clip.TreeSortable.trees.grouptypesTree.getNodeId(node.up('li')).split('-')[1];
            Zikula.Clip.Ajax.Request({'tid':tid, 'withtid1':tid, 'op':'or', 'withtid2':tid}, 'relations');
        }
    });
    Zikula.Clip.ContextMenu.addItem({
        label: Zikula.__('Code', 'module_clip_js'),
        condition: function() {
            return !Zikula.Clip.ContextMenu.isGrouptype;
        },
        callback: function(node) {
            node.insert({after: Zikula.Clip.Indicator()});
            var tid = Zikula.Clip.TreeSortable.trees.grouptypesTree.getNodeId(node.up('li')).split('-')[1];
            Zikula.Clip.Ajax.Request({'tid':tid, 'code':'list'}, 'generator');
        }
    });
    /*
    Zikula.Clip.ContextMenu.addItem({
        label: Zikula.__('Editor list', 'module_clip_js'),
        condition: function() {
            return !Zikula.Clip.ContextMenu.isGrouptype;
        },
        callback: function(node) {
            node.insert({after: Zikula.Clip.Indicator()});
            var tid = Zikula.Clip.TreeSortable.trees.grouptypesTree.getNodeId(node.up('li')).split('-')[1];
            Zikula.Clip.Ajax.Request({'tid':tid}, 'list');
        }
    });
    Zikula.Clip.ContextMenu.addItem({
        label: Zikula.__('New publication', 'module_clip_js'),
        condition: function() {
            return !Zikula.Clip.ContextMenu.isGrouptype;
        },
        callback: function(node) {
            node.insert({after: Zikula.Clip.Indicator()});
            var tid = Zikula.Clip.TreeSortable.trees.grouptypesTree.getNodeId(node.up('li')).split('-')[1];
            Zikula.Clip.Ajax.Request({'tid':tid}, 'edit');
        }
    });
    */
};

Zikula.Clip.MenuAction = function(node, action)
{
    if (!['edit', 'delete', 'addafter', 'addchild', 'addroot'].include(action)) {
        return false;
    }

    if (node) {
        node.insert({after: Zikula.Clip.Indicator()});
    }

    var pars = {
            module: 'Clip',
            type: 'ajax',
            func: action+'group',
            mode: 'add',
            gid:  node ? Zikula.Clip.TreeSortable.trees.grouptypesTree.getNodeId(node.up('li')) : null
        };

    switch (action) {
        case 'edit':
            pars.mode = 'edit';
            break;
        case 'addafter':
            pars.func = 'editgroup';
            pars.pos  = 'after';
            pars.parent = Zikula.Clip.TreeSortable.trees.grouptypesTree.getNodeId(node.up('li'));
            break;
        case 'addchild':
            pars.func = 'editgroup';
            pars.pos  = 'bottom';
            pars.parent = Zikula.Clip.TreeSortable.trees.grouptypesTree.getNodeId(node.up('li'));
            break;
        case 'addroot':
            pars.func = 'editgroup';
            pars.pos  = 'root';
            break;
        case 'delete':
            pars.type = 'ajaxexec';
    }

    new Zikula.Ajax.Request(
        'ajax.php?lang='+Zikula.Config.lang, {
            parameters: pars,
            onComplete: Zikula.Clip.MenuActionCallback
        });

    return true;
};

Zikula.Clip.MenuActionCallback = function(req)
{
    Zikula.Clip.Indicator().remove();

    if (!req.isSuccess()) {
        Zikula.showajaxerror(req.getMessage());
        Zikula.Clip.Container.instance.hideIndicator();
        return false;
    }

    var data = req.getData();

    switch (data.action) {
        case 'delete':
            var node = $(Zikula.Clip.TreeSortable.trees.grouptypesTree.config.nodePrefix + data.gid);
            Droppables.remove(node);
            node.select('li').each(function(subnode) {
                Droppables.remove(subnode);
            });
            Effect.SwitchOff(node,{
                afterFinish: function() {node.remove();}
            });
            Zikula.Clip.TreeSortable.trees.grouptypesTree.drawNodes();
            break;
        case 'edit':
            $(document.body).insert(data.result);
            Zikula.Clip.OpenForm(data, Zikula.Clip.EditNode);
            break;
        case 'add':
            $(document.body).insert(data.result);
            Zikula.Clip.OpenForm(data, Zikula.Clip.AddNode);
            break;
    }

    return true;
};

/* Form Methods */
Zikula.Clip.OpenForm = function(data, callback)
{
    if (Zikula.Clip.Form) {
        Zikula.Clip.Form.destroy();
    }

    Zikula.Clip.Form = new Zikula.UI.FormDialog($('clip_ajax_form_container'), callback, {
        title: $('clip_ajax_form_container').title,
        width: 700,
        buttons: [
            {label: Zikula.__('Submit', 'module_clip_js'), type: 'submit', name: 'submit', value: 'submit', 'class': 'z-btgreen', close: false},
            {label: Zikula.__('Cancel', 'module_clip_js'), type: 'submit', name: 'cancel', value: false, 'class': 'z-btred', close: true}
        ]
    });

    return Zikula.Clip.Form.open();
};

Zikula.Clip.CloseForm = function()
{
    Zikula.Clip.Form.destroy();
    Zikula.Clip.Form = null;
};

Zikula.Clip.UpdateForm = function(data)
{
    $('clip_ajax_form_container').replace(data);
    Zikula.Clip.Form.window.indicator.fade({duration: 0.2});
    $('clip_ajax_form_container').show();
};

/* Mode callbacks */
Zikula.Clip.DeleteMenuAction = function(node)
{
    if (Zikula.Clip.ContextMenu.lastClick.findElement('a').up('li').down('ul')) {
        return false;
    }

    var msg = new Element('div', {id:'dialogContent'}).insert(
            new Element('p').update(Zikula.__('Do you really want to delete this group?', 'module_clip_js'))
        ),
        buttons = [
            {name: 'Delete', value: 'Delete', label: Zikula.__('Delete', 'module_clip_js'), 'class': 'z-btgreen'},
            {name: 'Cancel', value: 'Cancel', label: Zikula.__('Cancel', 'module_clip_js'), 'class': 'z-btred'},
        ];

    Zikula.Clip.DeleteDialog = new Zikula.UI.Dialog(
        msg,
        buttons,
        {title: Zikula.__('Confirmation prompt', 'module_clip_js'), width: 500, callback: function(res) {
             switch (res.value) {
                 case 'Delete':
                    Zikula.Clip.MenuAction(node, 'delete');
                    Zikula.Clip.DeleteDialog.destroy();
                    break;
                default:
                    Zikula.Clip.DeleteDialog.destroy();
             }
        }}
    );
    Zikula.Clip.DeleteDialog.open()
    Zikula.Clip.DeleteDialog.container.down('button[name=Cancel]').focus();
};

Zikula.Clip.EditNode = function(res)
{
    if (!res || (res.hasOwnProperty('cancel') && res.cancel === false)) {
        Zikula.Clip.CloseForm();
        return false;
    }

    Zikula.Clip.Form.window.indicator.appear({to: 0.7, duration: 0.2});

    var pars = Zikula.Clip.Form.serialize(true);
    pars.mode = 'edit';

    new Zikula.Ajax.Request('ajax.php?module=Clip&type=ajaxexec&func=savegroup&lang='+Zikula.Config.lang, {
        parameters: pars,
        onComplete: function(req) {
            var data = req.getData();
            if (!req.isSuccess()) {
                Zikula.showajaxerror(req.getMessage());
                Zikula.Clip.CloseForm();
            } else {
                var nodeId  = Zikula.Clip.TreeSortable.trees.grouptypesTree.config.nodePrefix + data.gid;
                var nodeOld = $(nodeId).replace(data.node);
                if (nodeOld.down('ul')) {
                    $(nodeId).insert(nodeOld.down('ul'))
                }
                Zikula.Clip.ReinitTreeNode($(nodeId), data);
                Zikula.Clip.CloseForm();
            }
        }
    });

    return true;
};

Zikula.Clip.AddNode = function(res)
{
    if (!res || (res.hasOwnProperty('cancel') && res.cancel === false)) {
        Zikula.Clip.CloseForm();
        return false;
    }

    Zikula.Clip.Form.window.indicator.appear({to: 0.7, duration: 0.2});

    var pars = Zikula.Clip.Form.serialize(true);
    pars.mode = 'add';

    new Zikula.Ajax.Request('ajax.php?module=Clip&type=ajaxexec&func=savegroup&lang='+Zikula.Config.lang, {
        parameters: pars,
        onComplete: function(req) {
            var data = req.getData();
            if (!req.isSuccess()) {
                Zikula.showajaxerror(req.getMessage());
                Zikula.Clip.CloseForm();
            } else {
                var relNode   = $(Zikula.Clip.TreeSortable.trees.grouptypesTree.config.nodePrefix + data.parent);

                if (data.pos == 'root') {
                    $('grouptypesTree').insert({'bottom': data.node});
                } else if (data.pos == 'after') {
                    relNode.insert({'after': data.node});
                } else {
                    var newParent = relNode.down('ul');
                    if (!newParent) {
                        newParent = new Element(('ul'), {'class': 'tree'});
                        relNode.insert(newParent);
                    }
                    newParent.insert({'bottom': data.node});
                }

                var node = $(Zikula.Clip.TreeSortable.trees.grouptypesTree.config.nodePrefix + data.gid);
                Zikula.Clip.ReinitTreeNode(node, data);
                Zikula.Clip.CloseForm();
            }
        }
    });

    return true;
};

Zikula.Clip.ReinitTreeNode = function(node, data)
{
    Zikula.Clip.TreeSortable.trees.grouptypesTree.initNode(node);
    Zikula.Clip.TreeSortable.trees.grouptypesTree.drawNodes();
    Zikula.UI.Tooltips(node.select('a'));
};



/* Ajax Indicator */
Zikula.Clip.Indicator = function() {
    return $('ajax_indicator') ? $('ajax_indicator') : new Element('img',{id: 'ajax_indicator', src: 'images/ajax/indicator_circle.gif'});
};



/* Reorder method */
Zikula.Clip.Resequence =
{
    Listener: function(node, params, data)
    {
        // only allow inserts of grouptypes on root level
        var id = Zikula.Clip.TreeSortable.trees.grouptypesTree.getNodeId(node)
        if (node.up('li') === undefined && id != parseInt(id)) {
            return false;
        }

        node.insert({bottom: Zikula.Clip.Indicator()});

        var request = new Zikula.Ajax.Request(
            'ajax.php?module=Clip&type=ajaxexec&func=treeresequence',
            {
                parameters: {'data': data},
                onComplete: Zikula.Clip.Resequence.Callback
            });

        return request.success();
    },

    Callback: function(req)
    {
        if (!req.isSuccess()) {
            Zikula.showajaxerror(req.getMessage());
            return Zikula.TreeSortable.grouptypesTree.revertInsertion();
        }

        return true;
    }
};



/* Ajax view functions */
Zikula.Clip.Ajax =
{
    Busy: false,

    Request: function(pars, func, type, callback)
    {
        if (Zikula.Clip.Ajax.Busy || typeof pars != 'object' || typeof pars['tid'] == 'undefined') {
            return;
        }

        Zikula.Clip.Container.instance.showIndicator();

        // update the hash
        func = func ? func : 'pubtypeinfo';
        Zikula.Clip.Hash.Update(pars, func);

        // backup the request basis in the class
        Zikula.Clip.Container.func = func;
        Zikula.Clip.Container.pars = Object.clone(pars);

        pars.module = 'Clip';
        pars.type   = type ? type : 'ajax';
        pars.func   = func;

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
        if ($('ajax_indicator')) {
            $('ajax_indicator').remove();
        }

        if (!req.isSuccess()) {
            Zikula.Clip.Ajax.Busy = false;
            Zikula.showajaxerror(req.getMessage());
            Zikula.Clip.Container.instance.hideIndicator();
            return false;
        }

        Zikula.Clip.Container.instance.updateContent(req.getData());

        Zikula.Clip.Ajax.Busy = false;

        return true;
    }
};
