
/**
  * Listcontrol is a very basic class for folding and unfolding an existing tree structure taking into account the passed category ID
  * Example of use
  * onload=ListControll.init(this)
  * @author Nikola Stjelja <nikola@komponenta.com>
  * @version 0.2
  */
var ListControl={

	tree:null,

	/**
	  * Initializes the folded tree
	  * @param HTMLElement object - where the tree is stored.
	  */
	init:function(object){
		this.tree=object;

		this.SetHandlers(); // Go trough all sub elements and set handlers , do so recursivelly

		// Unfold a path if the selected in the url is visible filter[category_id]=
		var id=this.find_id(window.location.href);
		if (id) { 
			this.UnFoldIt(id);
		}
	},
	

	/**
	  * Unfolds the given directory structure
	  * @param Int id - of the link specified
	  */
	UnFoldIt:function(id) {
		// Get All anchors from the tree
		var lists=this.tree.getElementsByTagName('li');

		// Pass trought the anchors and find which has the given id inside it
		for (var i=0;i<lists.length;i++) {
			if(id==lists[i].id.replace('item_','')) { // find the id of the current list item
				break; 
			}
		}

		// Get the list on focus
		var parentLi=lists[i];

		// If the parents has a ul unfold it
		var childUl=parentLi.getElementsByTagName('ul');
		if (childUl.length>0) {
			childUl[0].style.display='';
		}

		// Now go up throuh the tree and unfold each predecessor ul up to the top
		var top=parentLi.parentNode;
		while(top){
			if (top.nodeName.toLowerCase()=='ul') {
				top.style.display='';
				top=top.parentNode.parentNode; // Now find its parent and make it top or false
			} else {
				break;
			}
		}
	},

	/**
	  * Find number of the product inside the ulr string
	  * @param String url
	  * @return int - id , or false if not found
	  */
	find_id:function(url) {
		// Set the id As False per default
		id=false;

		// Take the object number
		re=/_id=(\w+)/gi;
		str=url;
		str.replace(re,function(glob,num){id=num;return num;});
		return id;
	},

	/**
	  * Sets handlers on the sub lists
	  */
	SetHandlers:function() {

		// Get the sublists
		var sublists=this.tree.getElementsByTagName('ul');

		// The object
		var ListCont=this;

		for(var i=0;i<sublists.length;i++) {

			// Add the cursors the event
			sublists[i].parentNode.style.cursor='pointer';

			// Ad the event
			sublists[i].parentNode.onclick=function(e) {
				// Stop the propagation of the event
				ListControl.stopBubble(e);

				// Get the first ul
				var ul=this.getElementsByTagName('ul')[0];

				// Display logic
				if (ul.style.display=='none') { 
					ul.style.display='';
				} else { 
					ul.style.display='none';
				}
			}

			// Fold the tree Luke, fold it
			sublists[i].style.display='none';
		}

		// Add the normal curos on thos li elements which don't have a ul subtype, prevent Bubbling on click
		var li=this.tree.getElementsByTagName('li');

		for(var i=0;i<li.length;i++) {
			if (li[i].getElementsByTagName('ul').length==0) {
				li[i].style.cursor='text';
				
				// Prevent bubbling
				li[i].onclick=function(e) {
					ListControl.stopBubble(e);
				}
			} 
		}
	},

	/**
	  * Stops the bubbling of the event
	  */ 
	stopBubble:function(e) {
		if (e && e.stopPropagation) {
			e.stopPropagation();  // the DOM way
		} else {
			window.event.cancelBubble=true; // My way
		}
	}
}
