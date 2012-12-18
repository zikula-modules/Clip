
{* Open Graph tags
{ogtag prop='title' content=$pubtype.title}
{ogtag prop='type' content='site_section'}
{ogtag prop='image' content=''}
{ogtag prop='url' content=$returnurl}
{ogtag prop='site_name' content=$modvars.ZConfig.defaultpagetitle}
*}

<div class="clip-main clip-main-{$pubtype.urltitle}">
    {include file='generic_navbar.tpl'}

    <h2>{$pubtype.title|safetext}</h2>

    {if $pubtype.description}
    <p class="clip-pagedescription">
        {$pubtype.description|safehtml}
    </p>
    {/if}

    {clip_category_browser field='category'}
</div>
