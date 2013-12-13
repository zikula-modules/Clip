
<ul class="clip-block-list">
    {foreach from=$publist item='pubdata'}
        <li class="z-clearfix">
            {strip}
            {clip_accessblock pub=$pubdata context='edit'}
            <a class="z-floatright" href="{clip_url func='edit' pub=$pubdata}">
                {img modname='core' src='edit.png' set='icons/extrasmall' __title='Edit' __alt='Edit'}
            </a>
            {/clip_accessblock}

            <a href="{clip_url func='display' pub=$pubdata}">
                {$pubdata.core_title|safetext}
            </a>
            {/strip}
        </li>
    {foreachelse}
        <li class="z-dataempty">
            {gt text='No publications found.'}
        </li>
    {/foreach}
</ul>
