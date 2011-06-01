
<ul class="clip-block-list">
    {foreach from=$publist item='item'}
        <li>
            {strip}
            <a href="{modurl modname='Clip' type='user' func='display' tid=$pubtype.tid pid=$item.core_pid title=$item.core_title|formatpermalink}">
                {$item.core_title}
            </a>

            {clip_accessblock tid=$pubtype.tid pid=$item context='edit'}
            &nbsp;
            <a href="{modurl modname='Clip' type='user' func='edit' tid=$pubtype.tid pid=$item.core_pid}">
                {img modname='core' src='edit.png' set='icons/extrasmall' __title='Edit' __alt='Edit'}
            </a>
            {/clip_accessblock}
            {/strip}
        </li>
    {foreachelse}
        <li class="z-dataempty">
            {gt text='No publications found.'}
        </li>
    {/foreach}
</ul>

