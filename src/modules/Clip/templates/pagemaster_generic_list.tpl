
<h2>{gt text=$pubtype.title}</h2>

{include file='clip_generic_navbar.tpl' section='list'}

{if $pubtype.description neq ''}
    <p class="pm-pubtype-desc">{gt text=$pubtype.description}</p>
{/if}

<table class="z-datatable pm-pub-list">
    <tbody>
        {foreach from=$publist item='item'}
        <tr class="{cycle values='z-even,z-odd'}">
            <td>
                <a href="{modurl modname='Clip' type='user' func='display' tid=$pubtype.tid pid=$item.core_pid}">{$item[$pubtype.titlefield]}</a>
                <span class="z-sub z-floatright">({gt text='%s read' plural='%s reads' count=$item.core_hitcount tag1=$item.core_hitcount})</span>
            </td>
            <td class="z-right">
                {strip}
                <a href="{modurl modname='Clip' type='user' func='display' tid=$pubtype.tid pid=$item.core_pid}">
                    {img modname='core' src='demo.gif' set='icons/extrasmall' __title='View' __alt='View'}
                </a>
                {checkpermissionblock component='clip:input:' instance="$pubtype.tid::" level=ACCESS_ADD}
                &nbsp;
                <a href="{modurl modname='Clip' type='user' func='edit' tid=$pubtype.tid pid=$item.core_pid}">
                    {img modname='core' src='edit.gif' set='icons/extrasmall' __title='Edit' __alt='Edit'}
                </a>
                {/checkpermissionblock}
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

{if $pager.itemsperpage neq 0}
    {pager display='page' rowcount=$pager.numitems limit=$pager.itemsperpage posvar='startnum'}
{/if}

{modcallhooks hookobject='category' hookaction='display' module='Clip' returnurl=$returnurl}
