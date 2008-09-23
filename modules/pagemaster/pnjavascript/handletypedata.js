/**
 * PageMaster
 *
 * @copyright (c) 2008, PageMaster Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @version     $ Id: handletypedata.js 220 2007-08-11 15:23:48Z mateo $
 * @package     Zikula_3rd_party_Modules
 * @subpackage  pagemaster
 */

function showTypeDiv(id) {
	$('Modalcontainer').appear({ to: 0.5 });
	$('typeDataDiv').appear({ to: 1 });
	Event.observe(window, 'resize', resizeModalcontainer, false);
}

function closeTypeData() {
	$('Modalcontainer').fade({ from: 0.5 });
	$('typeDataDiv').fade({ from: 1 });
	Event.stopObserving(window, 'resize', resizeModalcontainer);
}

function resizeModalcontainer() {
	var dimensions = document.viewport.getDimensions();
	var offset = $('Modalcontainer').viewportOffset();
	var styles = {'width': dimensions['width']+'px',
				  'height': dimensions['height']+'px'};
	if (offset[0] > 0)
		styles['margin-left'] = '-'+offset[0]+'px';
	if (offset[1] > 0)
		styles['margin-top'] = '-'+offset[1]+'px';
	$('Modalcontainer').setStyle(styles);
}

Event.observe(window, 'load', function() {
	if ($('Modalcontainer')) {
		$(document.body).insert($('Modalcontainer'));
		resizeModalcontainer();
	}
}, false);
