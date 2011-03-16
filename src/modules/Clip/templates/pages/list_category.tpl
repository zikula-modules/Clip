{include file='clip_generic_navbar.tpl' section='list'}

{* resolve the title depending of any existing *}
{if 'sub'|in_array:$clipargs.userapi_getall.filter.category.ops}
    {assign var='op' value='sub'}
{elseif 'eq'|in_array:$clipargs.userapi_getall.filter.category.ops}
    {assign var='op' value='eq'}
{/if}

{if $op}
    {category_path id=$clipargs.userapi_getall.filter.category.$op.0 idcolumn='id' field='display_name' assign='categorytitle'}
    {if $categorytitle[$modvars.ZConfig.language_i18n]}
        {assign var='categorytitle' value=$categorytitle[$modvars.ZConfig.language_i18n]}
    {else}
        {category_path id=$clipargs.userapi_getall.filter.category.$op.0 idcolumn='id' field='name' assign='categorytitle'}
    {/if}
    {pagesetvar name="title" value="$categorytitle - `$pubtype.title` - `$modvars.ZConfig.sitename`"}
    <h2>{gt text='Category:'} {$categorytitle}</h2>
{else}
    {pagesetvar name="title" value="`$pubtype.title` - `$modvars.ZConfig.sitename`"}
    <h2>{gt text=$pubtype.title}</h2>
{/if}

<span>{gt text='Pages published under this category:'}</span>

<table class="z-datatable clip-pub-list">
    <tbody>
        {foreach from=$publist item='item'}
        <tr class="{cycle values='z-even,z-odd'}">
            <td>
                <a href="{modurl modname='Clip' type='user' func='display' tid=$pubtype.tid pid=$item.core_pid}">{$item.core_title}</a>
                <span class="z-sub z-floatright">{gt text='%s read' plural='%s reads' count=$item.core_hitcount tag1=$item.core_hitcount}</span>
            </td>
            <td class="z-right z-nowrap">
                {strip}
                <a href="{modurl modname='Clip' type='user' func='display' tid=$pubtype.tid pid=$item.core_pid}">
                    {img modname='core' src='demo.png' set='icons/extrasmall' __title='View' __alt='View'}
                </a>
                {checkpermissionblock component='clip:input:' instance="$pubtype.tid::" level=ACCESS_ADD}
                &nbsp;
                <a href="{modurl modname='Clip' type='user' func='edit' tid=$pubtype.tid pid=$item.core_pid}">
                    {img modname='core' src='edit.png' set='icons/extrasmall' __title='Edit' __alt='Edit'}
                </a>
                {/checkpermissionblock}
                {/strip}
            </td>
        </tr>
        {foreachelse}
        <tr class="z-datatableempty">
            <td>{gt text='No pages found.'}</td>
        </tr>
        {/foreach}
    </tbody>
</table>

{if $pager.itemsperpage neq $modvars.Clip.maxperpage}
    {pager display='page' posvar='page' rowcount=$pager.numitems limit=$pager.itemsperpage}
    {*pager display='startnum' posvar='startnum' rowcount=$pager.numitems limit=$pager.itemsperpage*}
{/if}
