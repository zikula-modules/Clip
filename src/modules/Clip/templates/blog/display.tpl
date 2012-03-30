
{clip_hitcount}

{* Open Graph tags
{ogtag prop='title' content=$pubdata.core_title}
{ogtag prop='type' content='article'}
{ogtag prop='image' content=''}
{ogtag prop='url' content=$returnurl}
{ogtag prop='site_name' content=$modvars.ZConfig.defaultpagetitle}
*}

<div class="clip-display clip-display-{$pubtype.urltitle}">
    {include file='generic_navbar.tpl'}

    <div class="clip-post clip-post-{$pubdata.core_pid} z-floatbox">
        {*
        <div class="clip-post-socialise">
            {twitter url=$returnurl title=$pubdata.core_title count='vertical'}
            {fblike url=$returnurl layout='vertical' tpl='xfbml' rel='display'}
        </div>
        *}
        <h2 class="clip-post-title">{$pubdata.core_title|safetext}</h2>

        <div class="clip-post-meta">
            {capture assign='author'}<span class="author vcard">{$pubdata.core_author|profilelinkbyuid}</span>{/capture}
            <span class="clip-post-date">{gt text='Posted on %1$s by %2$s' tag1=$pubdata.core_publishdate|dateformat:'datebrief' tag2=$author}</span>
            <span class="clip-post-reads">({gt text='%s read' plural='%s reads' count=$pubdata.core_hitcount tag1=$pubdata.core_hitcount})</span>
        </div>
        <div class="clip-post-content z-floatbox">
            {$pubdata.content|safehtml|clip_notifyfilters:$pubtype}
        </div>
        <div class="clip-post-utility">
            {strip}
            <span class="clip-post-category">
            {if $pubdata.category.id}
                {capture assign='category'}
                <a href="{clip_url func='list' filter="category^sub^`$pubdata.category.id`"}" title="{gt text='View all posts in %s' tag1=$pubdata.category.fullTitle}">
                    {$pubdata.category.fullTitle}
                </a>
                {/capture}
                {gt text='Posted in %s' tag1=$category}
            {else}
                <a href="{clip_url func='list' filter="category^null"}" title="{gt text='View all uncategorized posts'}">
                    {gt text='Uncategorized'}
                </a>
            {/if}
            </span>
            {/strip}

            <span class="text_separator">|</span>

            <span class="clip-post-permalink">
                {clip_url func='display' pub=$pubdata assign='bookmark_url'}
                {gt text='Permalink to %s' tag1=$pubdata.core_title assign='bookmark_title'}
                {gt text='Bookmark the <a rel="bookmark" title="%1$s" href="%2$s">permalink</a>' tag1=$bookmark_title|safehtml tag2=$bookmark_url|safehtml}
            </span>

            <span class="text_separator">|</span>

            <span class="clip-post-edit-link">
                {clip_accessblock pub=$pubdata context='edit'}
                <span class="z-nowrap">
                    <a href="{clip_url func='edit' pub=$pubdata}">{gt text='Edit'}</a>
                </span>
                {/clip_accessblock}
            </span>
            {*
            <span class="text_separator">|</span>
            {sharethis id=$pubdata.core_uniqueid url=$returnurl title=$pubdata.core_title __text='Share'}
            *}
            {*sexybookmarks url=$returnurl title=$pubdata.core_title*}
        </div>
    </div>

    <div class="clip-hooks-display">
        {notifydisplayhooks eventname=$pubtype->getHooksEventName() urlObject=$pubdata->clipUrl() id=$pubdata.core_pid}
    </div>
</div>
