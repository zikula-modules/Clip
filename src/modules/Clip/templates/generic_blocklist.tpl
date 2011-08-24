
<ul class="clip-block-list">
    {foreach from=$publist item='pubdata'}
        <li class="z-clearfix">
            {strip}
            {clip_accessblock tid=$pubtype.tid pid=$pubdata context='edit'}
            <a class="z-floatright" href="{modurl modname='Clip' type='user' func='edit' tid=$pubtype.tid pid=$pubdata.core_pid}">
                {img modname='core' src='edit.png' set='icons/extrasmall' __title='Edit' __alt='Edit'}
            </a>
            {/clip_accessblock}

            <a href="{modurl modname='Clip' type='user' func='display' tid=$pubtype.tid pid=$pubdata.core_pid title=$pubdata.core_title|formatpermalink}">
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
