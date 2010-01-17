/**
 * Zikula Application Framework
 * 
 * @copyright (c) Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_Javascript
 * @subpackage Utilities
 */

/**
 * Markup example
 * uppercase for the used vars in the javascript
 * @TODO: if the iteminput have no X in its ID, add it in the final
 * 
 * <a onclick="javascript:itemlist_LISTID.appenditem();" href="javascript:void(0);">Append new item</a>
 * <ul id="LISTID" class="itemlist">
 *   <li ... class="itemlistheader">
 *     ...
 *   </li>
 *   <li id="listitem_LISTID_ITEMID" class="listitem_LISTID sortable z-odd/z-even">
 *     ...
 *     <input id="vars_ITEMID_fieldname" name="vars[ITEMID][fieldname]" ... />
 *     <a ...id="buttondelete_LISTID_ITEMID" class="buttondelete">...</a>
 *     (<span>ITEMID</span>)
 *     ...
 *   </li>
 * </ul>
 * <ul style="display:none">
 *   <li id="LISTID_emptyitem">
 *     ...
 *     <input class="iteminput" id="vars_X_fieldname" ... />
 *     <a ...id="buttondelete_LISTID_X" class="buttondelete">...</a>
 *     (<span class="itemid">ITEMID</span>)
 *     ...
 *   </li>
 * </ul>
 * 
 * <script>
 * //<![CDATA[
 * var itemlist_LISTID = null;
 * Event.observe(window, 'load', function() {
 *     itemlist_LISTID = new Zikula.itemlist(LISTID, true);
 * }, false);
 * //]]>
 * </script> 
 */

if (typeof(Zikula) == 'undefined')
    Zikula = {};

Zikula.itemlist = Class.create({
    /**
     * Initialize the list
     * @param   listid          string   ID of the list to work with
     * @param   headerpresent   bool     (true) if the list have a header (false) otherwise
     */
    initialize: function(listid, options) {
        this.id  = listid;
        this.options = {
            headerpresent: false,
            quotekeys: false,
            afterInitialize: Prototype.emptyFunction,
            beforeAppend: Prototype.emptyFunction,
            afterAppend: Prototype.emptyFunction
        };
        Object.extend(this.options, options || {});

        this.lastitemid = 0;
        var offset = 0;
        if (this.options.headerpresent) {
            offset = 1;
        }
        if ($(this.id).immediateDescendants().size() == offset) {
            this.appenditem();
        }

        // assign a value after initialize if itemid differs of the count
        this.lastitemid = $(this.id).immediateDescendants().size() - offset;

        // define a rule to delete a menuitem when the trash icon is clicked
        var buttondeleteselector = '#'+this.id+' .buttondelete';
        var ruleset = new Object;
        ruleset[buttondeleteselector] = function(delbutton){
                Event.observe(delbutton, 
                              'click', 
                              function()
                              {
                                  var itemid = this.id.replace('buttondelete', 'listitem');
                                  if ($(itemid)) {
                                      $(itemid).remove();
                                  }
                                  // recolor the list trusting in the var name convention
                                  var listid = $A(itemid.split('_'));
                                  listid.shift();  // remove the listitem prefix
                                  listid.pop();    // remove the item id
                                  listid = listid.join('_');
                                  eval('itemlist_'+listid+'.itemlistrecolor()');
                              },
                              true);
        };

        // register the ruleset
        Behaviour.register(ruleset);

        // apply the ruleset to all existing delete buttons
        Behaviour.apply();

        Sortable.create(this.id,
                        { 
                          only: 'sortable',
                          constraint: false,
                          onUpdate: this.itemlistrecolor.bind(this)
                        });
        $A($(this.id).getElementsByClassName('sortable')).each(
            function(node) { node.setStyle({'cursor': 'move'}); }
        );
    },

    /**
     * Parses the ID and generate an standar name
     */
    getnamefromid: function(id) {
        var chunks = id.split('_');
        var result = chunks[0];

        chunks = chunks.slice(1);
        chunks.each( function(chunk){
            if (this.options.quotekeys && chunk != '') {
        	    result += "['"+chunk+"']";
            } else {
                result += '['+chunk+']';
            }
        }.bind(this));
        return result;
    },


    /**
     * Recolor the itemlist
     */
    itemlistrecolor: function()
    {
        pnrecolor(this.id, 'itemlistheader');
    },

    /**
     * Appends a new item by cloning a predefined one
     * @return int last item id
     */
    appenditem: function()
    {
        // clone the new item
        var newitem = $(this.id+'_emptyitem').cloneNode(true);

        this.lastitemid++;
        lastid = this.lastitemid;
        newitem.id = 'listitem_'+this.id+'_'+lastid;

        if ($(newitem).hasClassName('z-odd')) {
            $(newitem).removeClassName('z-odd');
            $(newitem).addClassName('z-even');
        } else {
            $(newitem).removeClassName('z-even');
            $(newitem).addClassName('z-odd');
        }

        $A(newitem.getElementsByClassName('iteminput')).each(
            function(node) {
                node.id   = node.id.replace(/X/g, lastid);
                node.name = this.getnamefromid(node.id);
                // prevent duplicated IDs for simple IDs like "var_"
                if (this.id.endsWith('_')) {
                	this.id += lastid;
                }
            }.bind(this)
        );
        $A(newitem.getElementsByTagName('button')).each(
            function(node) {
                node.id = node.id.replace(/X/g, lastid);
            }
        );
        $A(newitem.getElementsByClassName('itemid')).each(
            function(node) {
                if (node.hasAttribute('id')) { node.id = node.id.replace(/X/g, lastid); }
                if (node.hasAttribute('value')) { node.writeAttribute('value', lastid); }
                node.update(lastid)
            }
        );

        $(this.id).appendChild(newitem);

        // re-apply the ruleset to all existing delete buttons
        Behaviour.apply();

        Sortable.create(this.id,
                        { 
                          only: 'sortable',
                          constraint: false,
                          onUpdate: this.itemlistrecolor.bind(this)
                        });
        $A(document.getElementsByClassName('sortable')).each(
            function(node) { node.setStyle({'cursor': 'move'}); }
        );
        this.itemlistrecolor();

        return lastid;
    }
});
 