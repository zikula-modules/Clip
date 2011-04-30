
<ul class="clip-block-list">
    {foreach from=$publist item='item'}
        <li>
            <a href="{modurl modname='Clip' type='user' func='display' tid=$pubtype.tid pid=$item.core_pid title=$item.core_title|formatpermalink}">{$item.core_title}</a>
            {checkpermissionblock component='clip:input:' instance="$pubtype.tid::" level=ACCESS_ADD}
                {strip}
                &nbsp;
                <a href="{modurl modname='Clip' type='user' func='edit' tid=$pubtype.tid pid=$item.core_pid}">
                    {img modname='core' src='edit.png' set='icons/extrasmall' __title='Edit' __alt='Edit'}
                </a>
                {/strip}
            {/checkpermissionblock}
        </li>
    {foreachelse}
        <li class="z-dataempty">
            {gt text='No publications found.'}
        </li>
    {/foreach}
</ul>

