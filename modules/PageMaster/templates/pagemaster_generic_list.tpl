
<h2>{gt text=$pubtype.title}</h2>

{include file='pagemaster_generic_navbar.tpl' section='list'}

{if $pubtype.description neq ''}
    <p class="pm-pubtype-desc">{gt text=$pubtype.description}</p>
{/if}

<table class="z-datatable pm-pub-list">
    <tbody>
        {foreach from=$publist item='pubitem'}
        <tr class="{cycle values='z-even,z-odd'}">
            <td>
                <a href="{modurl modname='PageMaster' type='user' func='display' tid=$pubtype.tid pid=$pubitem.core_pid}">{$pubitem.$core_titlefield}</a>
            </td>
            <td class="z-right">
                {strip}
                <a href="{modurl modname='PageMaster' type='user' func='display' tid=$pubtype.tid pid=$pubitem.core_pid}">
                    {img modname='core' src='demo.gif' set='icons/extrasmall' __title='View' __alt='View'}
                </a>
                {secauthaction_block component='pagemaster:input:' instance="$pubtype.tid::" level=ACCESS_ADD}
                &nbsp;
                <a href="{modurl modname='PageMaster' type='user' func='edit' tid=$pubtype.tid pid=$pubitem.core_pid}">
                    {img modname='core' src='edit.gif' set='icons/extrasmall' __title='Edit' __alt='Edit'}
                </a>
                {/secauthaction_block}
                {/strip}
            </td>
        </tr>
        {foreachelse}
        <tr class="z-datatableempty">
            <td>{gt text='No publications found.'}</td>
        </tr>
        {/foreach}
    </tbody>
</table>

{if isset($pager)}
    {pager display='page' rowcount=$pager.numitems limit=$pager.itemsperpage posvar='startnum'}
{/if}

{modcallhooks hookobject='category' hookaction='display' module='PageMaster' returnurl=$returnurl}
