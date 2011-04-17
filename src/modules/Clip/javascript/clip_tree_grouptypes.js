// Copyright Clip Team 2011 - license GNU/LGPLv3 (or at your option, any later version).

Zikula.define('Clip');

Event.observe(window, 'load', function() {
    Zikula.TreeSortable.trees.grouptypesTree.config.onSave = Zikula.Clip.Resequence;
    Zikula.UI.Tooltips($$('.tree a'));
});

Zikula.Clip.Indicator = function() {
    return $('ajax_indicator') ? $('ajax_indicator') : new Element('img',{id: 'ajax_indicator', src: 'images/ajax/indicator_circle.gif'});
};

Zikula.Clip.Resequence = function(node, params, data) {
    // only allow inserts of grouptypes on root level
    var id = Zikula.TreeSortable.trees.grouptypesTree.getNodeId(node)
    if (node.up('li') === undefined && id != parseInt(id)) {
        return false;
    }

    node.insert({bottom: Zikula.Clip.Indicator()});

    var request = new Zikula.Ajax.Request(
        "ajax.php?module=Clip&func=treeresequence",
        {
            parameters: {'data': data},
            onComplete: Zikula.Clip.ResequenceCallback
        });

    return request.success();
};

Zikula.Clip.ResequenceCallback = function(req)
{
    if (!req.isSuccess()) {
        Zikula.showajaxerror(req.getMessage());
        return Zikula.TreeSortable.grouptypesTree.revertInsertion();
    }

    return true;
};
