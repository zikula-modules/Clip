
{checkpermission component='Clip::' instance="`$pubtype.tid`::" level=ACCESS_EDIT assign='auth_editor'}

<ul class="clip-block-list">
    {foreach from=$publist item='item'}
        <li>
            <a href="{modurl modname='Clip' type='user' func='display' tid=$pubtype.tid pid=$item.core_pid title=$item.core_title|formatpermalink}">{$item.core_title}</a>
            {if $auth_editor}
                {strip}
                &nbsp;
                <a href="{modurl modname='Clip' type='user' func='edit' tid=$pubtype.tid pid=$item.core_pid}">
                    {img modname='core' src='edit.png' set='icons/extrasmall' __title='Edit' __alt='Edit'}
                </a>
                {/strip}
            {/if}
        </li>
    {foreachelse}
        <li class="z-dataempty">
            {gt text='No publications found.'}
        </li>
    {/foreach}
</ul>

