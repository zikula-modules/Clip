/**
 * PageMaster
 *
 * @copyright   (c) PageMaster Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @version     $ Id: pubfieldlist.css 220 2007-08-11 15:23:48Z mateo $
 * @package     Zikula_3rdParty_Modules
 * @subpackage  pagemaster
 */

function pubfieldlistorderchanged()
{
    var params = 'module=PageMaster&func=changedlistorder&authid=' + $F('pnFormAuthid')
                   + '&tid=' + $('pm_tid').innerHTML
                   + '&' + Sortable.serialize('pubfieldlist');

    var myAjax = new Ajax.Request(
        'ajax.php', 
        {
            method: 'get', 
            parameters: params, 
            onComplete: pubfieldlistorderchanged_response
        });
}

function pubfieldlistorderchanged_response(req)
{
    if (req.status != 200) {
        pnshowajaxerror(req.responseText);
        return;
    }

    var json = pndejsonize(req.responseText);
    pnupdateauthids(json.authid);

    pnrecolor('pubfieldlist', 'pubfieldlistheader');
}

function pubfieldlistsortinit()
{
    Sortable.create('pubfieldlist',
        {
            dropOnEmpty: true,
            only: 'z-sortable',
            onUpdate: pubfieldlistorderchanged
        }
    );

    // also add the anchor to the form 
    $('pnFormForm').action = $('pnFormForm').action + '#newpubfield'
}

Event.observe(window, 'load', pubfieldlistsortinit, false);
