
<div class="clip-main clip-main-{$pubtype.urltitle}">
    {include file='generic_navbar.tpl'}

    <h2>{$pubtype.title|safetext}</h2>

    {if $pubtype.description}
    <p class="clip-pagedescription">
        {$pubtype.description|safehtml}
    </p>
    {/if}

    <ul>
        <li>
            <a href="{clip_url func='list' tid=$pubtype.tid}">{gt text='Go to the list'}</a>
        </li>
        {clip_accessblock tid=$pubtype.tid context='submit'}
        <li>
            <a href="{clip_url func='edit' tid=$pubtype.tid}">{gt text='Submit a publication'}</a>
        </li>
        {/clip_accessblock}
    </ul>
</div>
