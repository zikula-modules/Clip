
{if !$homepage}{pagesetvar name="title" value="`$pubdata.core_title` - `$pubtype.title` - `$modvars.ZConfig.sitename`"}{/if}
{clip_hitcount pid=$pubdata.core_pid tid=$pubdata.core_tid}

{* Open Graph tags
{ogtag prop='title' content=$pubdata.core_title}
{ogtag prop='type' content='article'}
{ogtag prop='image' content=''}
{ogtag prop='url' content=$returnurl}
{ogtag prop='site_name' content=$modvars.ZConfig.sitename}
*}

<div class="clip-display clip-display-{$pubtype.urltitle}">
    {include file='clip_generic_navbar.tpl'}

    <div class="clip-post clip-post-{$pubdata.core_pid}">
        {*
        <div class="clip-post-socialise">
            {twitter url=$returnurl title=$pubdata.core_title count='vertical'}
            {fblike url=$returnurl layout='vertical' tpl='xfbml' rel='display'}
        </div>
        *}
        <h2>{$pubdata.core_title|safetext}</h2>

        <div class="clip-post-meta">
            {capture assign='author'}<span class="author vcard">{$pubdata.core_author|profilelinkbyuid}</span>{/capture}
            <span class="clip-post-date">{gt text='Posted on %1$s by %2$s' tag1=$pubdata.core_publishdate|dateformat:'datebrief' tag2=$author}</span>
            <span class="clip-post-reads">({gt text='%s read' plural='%s reads' count=$pubdata.core_hitcount tag1=$pubdata.core_hitcount})</span>
        </div>
        <div class="clip-post-content z-floatbox">
            {$pubdata.content|safehtml|notifyfilters:"clip.hook.`$pubtype.tid`.ui.filter"}
        </div>
        <div class="clip-post-utility">
            {strip}
            <span class="clip-post-category">
            {if $pubdata.category.id}
                {capture assign='category'}
                <a href="{modurl modname='Clip' type='user' func='list' tid=$pubtype.tid filter="category:sub:`$pubdata.category.id`"}" title="{gt text='View all posts in %s' tag1=$pubdata.category.fullTitle}">
                    {$pubdata.category.fullTitle}
                </a>
                {/capture}
                {gt text='Posted in %s' tag1=$category}
            {else}
                <a href="{modurl modname='Clip' type='user' func='list' tid=$pubtype.tid filter="category:null"}" title="{gt text='View all uncategorized posts'}">
                    {gt text='Uncategorized'}
                </a>
            {/if}
            </span>
            {/strip}

            <span class="text_separator">|</span>

            <span class="clip-post-permalink">
                {gt text='Permalink to %s' tag1=$pubdata.core_title assign='bookmark_title'}
                {modurl modname='Clip' type='user' func='display' tid=$pubtype.tid pid=$pubdata.core_pid title=$pubdata.core_title|formatpermalink assign='bookmark_url'}
                {gt text='Bookmark the <a rel="bookmark" title="%1$s" href="%2$s">permalink</a>' tag1=$bookmark_title tag2=$bookmark_url}
            </span>

            <span class="text_separator">|</span>

            <span class="clip-post-edit-link">
                {clip_accessblock tid=$pubtype.tid pid=$pubdata context='edit'}
                <span class="z-nowrap">
                    <a href="{modurl modname='Clip' type='user' func='edit' tid=$pubtype.tid pid=$pubdata.core_pid}">{gt text='Edit'}</a>
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
        {notifydisplayhooks eventname="clip.ui_hooks.pubtype`$pubtype.tid`.display_view" id=$pubdata.core_uniqueid}
    </div>
</div>
