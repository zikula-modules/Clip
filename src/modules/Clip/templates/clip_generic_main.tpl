
{if !$homepage}{pagesetvar name='title' value="`$pubtype.title` - `$modvars.ZConfig.sitename`"}{/if}

<div class="clip-main clip-main-{$pubtype.urltitle}">
    {include file='clip_generic_navbar.tpl'}

    <h2>{$pubtype.title|safetext}</h2>

    {if $pubtype.description}
    <p class="clip-pagedescription">
        {$pubtype.description|safehtml}
    </p>
    {/if}

    <ul>
        <li>
            <a href="{modurl modname='Clip' type='user' func='list' tid=$pubtype.tid}">{gt text='Go to the list'}</a>
        </li>
        {clip_accessblock tid=$pubtype.tid context='submit'}
        <li>
            <a href="{modurl modname='Clip' type='user' func='edit' tid=$pubtype.tid}">{gt text='Submit a publication'}</a>
        </li>
        {/clip_accessblock}
    </ul>
</div>
