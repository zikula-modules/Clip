/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Javascript
 */

function pubfieldlistorderchanged()
{
    var params = 'module=Clip&func=changedlistorder&authid=' + $F('FormAuthid')
                   + '&tid=' + $('clip_tid').innerHTML
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
    $('FormForm').action = $('FormForm').action + '#FormForm'
}

Event.observe(window, 'load', pubfieldlistsortinit, false);
