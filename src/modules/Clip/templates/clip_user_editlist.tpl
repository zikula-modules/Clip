{php}
// if the request came from the pubeditlist block, we figure out
// the referer and pass it through the url to the form handler
if ($this->_tpl_vars['source'] == 'block') {
$requestURI = $_SERVER['REQUEST_URI'];
$pos = strpos ($requestURI, 'index.php');
if ($pos !== false) {
$zkURI = substr ($requestURI, $pos);
$this->assign ('referer', DataUtil::formatForDisplayHTML($zkURI));
} else {
$this->assign ('referer', 'index.php');
}
}
{/php}

{if $returntype eq 'admin'}
{include file='clip_admin_header.tpl'}
<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname='core' src='folder_documents.png' set='icons/large' __alt='Publications list'}</div>
{/if}

    <h2>{gt text='Publications edit list'}</h2>
    <table>
        <tbody>
            <tr>
                {if $menu AND $source neq 'block'}
                <td width="10%" valign="top" class="z-nowrap">
                    {include file='clip_user_editlist_menu.tpl'}
                </td>
                {/if}
                {if ($menu && $edit)}
                <td style="width: 1px; background-color: #cccccc;"><td>
                {/if}
                {if ($edit)}
                <td>
                    {if ($tid)}
                    {modfunc modname='Clip' type='user' func='edit' tid=$tid pid=$pid}
                    {/if}
                </td>
                {/if}
            </tr>
        </tbody>
    </table>

{if $returntype eq 'admin'}
</div>
{/if}
