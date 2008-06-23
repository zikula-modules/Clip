/**
 * PageMaster
 *
 * @copyright (c) 2008, PageMaster Team
 * @link        http://code.zikula.org/projects/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package     Zikula_3rd_party_Modules
 * @subpackage  pagemaster
 */

Event.observe(window, 'load', pubfieldlistsortinit, false);
function pubfieldlistsortinit() {
	Sortable.create("pubfieldlist",
		{ 
	    	dropOnEmpty: true,
	        only: 'pn-sortable',
	        constraint: false,
	        containment:["pubfieldlist"],
	        onUpdate: pubfieldlistorderchanged
	});
}

function pubfieldlistorderchanged() {
    var params = "module=pagemaster&func=changedlistorder&authid=" + $F('pnFormAuthid')
                   + "&tid=" + $F('tid')
                   + "&" + Sortable.serialize('pubfieldlist');
    var myAjax = new Ajax.Request(
        "ajax.php", 
        {
            method: 'get', 
            parameters: params, 
            onComplete: pubfieldlistorderchanged_response
        });
}

function pubfieldlistorderchanged_response() {
    pnrecolor('pubfieldlist', 'pubfieldlistheader');
}