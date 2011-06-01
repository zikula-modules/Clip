{include file='clip_generic_navbar.tpl' section='list'}

{* resolve the title depending of any existing filter *}
{if 'null'|in_array:$clipargs.getallapi.filter.category.ops}
    {assign var='op' value='null'}
{elseif 'sub'|in_array:$clipargs.getallapi.filter.category.ops}
    {assign var='op' value='sub'}
{elseif 'eq'|in_array:$clipargs.getallapi.filter.category.ops}
    {assign var='op' value='eq'}
{/if}

{if $op eq 'null'}
    {* title for uncategorized list *}
    {gt text='Uncategorized' assign='title'}
    {if !$homepage}{pagesetvar name="title" value="$title - `$pubtype.title` - `$modvars.ZConfig.sitename`"}{/if}
    <h2>{$pubtype.title}</h2>
    <span>{gt text='Uncategorized pages published:'}</span>
{elseif $op AND $clipargs.getallapi.filter.category.$op.0}
    {* title for a specific category requested *}
    {category_path id=$clipargs.getallapi.filter.category.$op.0 idcolumn='id' field='display_name' assign='categorytitle'}
    {if isset($categorytitle[$modvars.ZConfig.language_i18n])}
        {assign var='categorytitle' value=$categorytitle[$modvars.ZConfig.language_i18n]}
    {else}
        {category_path id=$clipargs.getallapi.filter.category.$op.0 idcolumn='id' field='name' assign='categorytitle'}
    {/if}
    {if !$homepage}{pagesetvar name="title" value="$categorytitle - `$pubtype.title` - `$modvars.ZConfig.sitename`"}{/if}
    <h2>{gt text='Category:'} {$categorytitle}</h2>
    <span>{gt text='Pages published under this category:'}</span>
{else}
    {* generic title *}
    {if !$homepage}{pagesetvar name="title" value="`$pubtype.title` - `$modvars.ZConfig.sitename`"}{/if}
    <h2>{$pubtype.title}</h2>
{/if}

{checkpermission component='Clip:input:' instance="`$pubtype.tid`::" level=ACCESS_ADD assign='auth_tid'}

<table class="z-datatable clip-pub-list">
    <tbody>
        {foreach from=$publist item='item'}
        <tr class="{cycle values='z-even,z-odd'}">
            <td>
                <a href="{modurl modname='Clip' type='user' func='display' tid=$pubtype.tid pid=$item.core_pid title=$item.core_title|formatpermalink}">{$item.core_title}</a>
                <span class="z-sub z-floatright">{gt text='%s read' plural='%s reads' count=$item.core_hitcount tag1=$item.core_hitcount}</span>
            </td>
            <td class="z-right z-nowrap">
                {strip}
                <a href="{modurl modname='Clip' type='user' func='display' tid=$pubtype.tid pid=$item.core_pid title=$item.core_title|formatpermalink}">
                    {img modname='core' src='demo.png' set='icons/extrasmall' __title='View' __alt='View'}
                </a>
                {if $auth_tid}
                &nbsp;
                <a href="{modurl modname='Clip' type='user' func='edit' tid=$pubtype.tid pid=$item.core_pid}">
                    {img modname='core' src='edit.png' set='icons/extrasmall' __title='Edit' __alt='Edit'}
                </a>
                {/if}
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
    {pager display='page' posvar='page' rowcount=$pager.numitems limit=$pager.itemsperpage maxpages=7}
    {*pager display='startnum' posvar='startnum' rowcount=$pager.numitems limit=$pager.itemsperpage maxpages=7*}
{/if}
