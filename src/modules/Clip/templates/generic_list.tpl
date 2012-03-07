
<div class="clip-wrapper clip-list clip-list-{$pubtype.urltitle}">
    {include file='generic_navbar.tpl'}

    <h2>{$pubtype.title}</h2>

    {*clip_pagerabc*}

    <table class="z-datatable clip-list-items">
        <tbody>
            {foreach from=$publist item='pubdata'}
            <tr class="{cycle values='z-even,z-odd'}">
                <td>
                    <a href="{clip_url func='display' pub=$pubdata}">{$pubdata.core_title|safetext}</a>
                    <span class="z-sub z-floatright">({gt text='%s read' plural='%s reads' count=$pubdata.core_hitcount tag1=$pubdata.core_hitcount})</span>
                </td>
                <td class="z-right z-nowrap">
                    {strip}
                    <a href="{clip_url func='display' pub=$pubdata}">
                        {img modname='core' src='demo.png' set='icons/extrasmall' __title='View' __alt='View'}
                    </a>
                    {clip_accessblock pub=$pubdata context='edit'}
                    &nbsp;
                    <a href="{clip_url func='edit' pub=$pubdata}">
                        {img modname='core' src='edit.png' set='icons/extrasmall' __title='Edit' __alt='Edit'}
                    </a>
                    {/clip_accessblock}
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

    {if $pager.itemsperpage neq $modvars.Clip.maxperpage}
        {if $clipargs.list.startnum eq 0}
            {pager display='page' posvar='page' rowcount=$pager.numitems limit=$pager.itemsperpage maxpages=7}
        {else}
            {pager display='startnum' posvar='startnum' rowcount=$pager.numitems limit=$pager.itemsperpage maxpages=7}
        {/if}
    {/if}
</div>
