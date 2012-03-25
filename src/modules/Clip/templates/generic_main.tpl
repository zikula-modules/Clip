
<div class="clip-wrapper clip-main clip-main-{$pubtype.urltitle}">
    {include file='generic_navbar.tpl'}

    <h2>{$pubtype.title|safetext}</h2>

    {if $pubtype.description}
    <p class="clip-pagedescription">
        {$pubtype.description|safehtml}
    </p>
    {/if}

    <ul>
        <li>
            <a href="{clip_url func='list'}">{gt text='Go to the list'}</a>
        </li>
        {clip_accessblock context='submit'}
        <li>
            <a href="{clip_url func='edit'}">{gt text='Submit a publication'}</a>
        </li>
        {/clip_accessblock}
    </ul>
</div>
