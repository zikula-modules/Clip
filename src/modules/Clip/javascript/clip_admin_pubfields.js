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
    var params = 'module=Clip&func=changelistorder&tid=' + $('clip_tid').innerHTML
                   + '&' + Sortable.serialize('pubfieldlist');

    new Zikula.Ajax.Request(
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
        Zikula.showajaxerror(req.responseText);
        return;
    }

    var json = Zikula.dejsonize(req.responseText);
    Zikula.updateauthids(json.authid);

    Zikula.recolor('pubfieldlist', 'pubfieldlistheader');
}

function pubfieldlistsortinit()
{
    Sortable.create('pubfieldlist',
        {
            dropOnEmpty: true,
            only: 'z-sortable',
            containment:["pubfieldlist"],
            onUpdate: pubfieldlistorderchanged
        }
    );

    // also add the anchor to the form
    var form = $$('form.z-form')[0];
    form.action = form.action + '#FormForm'

    // enable the tooltips
    Zikula.UI.Tooltips($$('.tooltips'));
}

Event.observe(window, 'load', pubfieldlistsortinit, false);
