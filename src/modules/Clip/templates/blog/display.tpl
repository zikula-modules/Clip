{pagesetvar name="title" value="`$pubdata.core_title` - `$pubtype.title` - `$modvars.ZConfig.sitename`"}
{clip_hitcount pid=$pubdata.core_pid tid=$pubdata.core_tid}

{* Open Graph tags
{ogtag prop='title' content=$pubdata.core_title}
{ogtag prop='type' content='article'}
{ogtag prop='image' content=''}
{ogtag prop='url' content=$returnurl}
{ogtag prop='site_name' content=$modvars.ZConfig.sitename}
*}

{include file='clip_generic_navbar.tpl' section='display'}

<div id="clip-post-{$pubdata.core_pid}" class="clip-post clip-post-{$pubdata.core_pid}">
    {*
    <div class="clip-post-socialise">
        {twitter url=$returnurl title=$pubdata.core_title count='vertical'}
        {fblike url=$returnurl layout='vertical' tpl='xfbml' rel='display'}
    </div>
    *}
    <h2 class="clip-post-title">
        {gt text=$pubdata.core_title}
    </h2>
    <div class="clip-post-meta">
        {capture assign='author'}<span class="author vcard">{$pubdata.core_author|profilelinkbyuid}</span>{/capture}
        <span class="clip-post-date">{gt text='Posted on %1$s by %2$s' tag1=$pubdata.core_publishdate|dateformat:'datebrief' tag2=$author}</span>
        <span class="clip-post-reads">({gt text='%s read' plural='%s reads' count=$pubdata.core_hitcount tag1=$pubdata.core_hitcount})</span>
    </div>
    <div class="clip-post-content">
        {$pubdata.content|safehtml|notifyfilters:"clip.hook.`$pubtype.tid`.ui.filter"}
    </div>
    <div class="clip-post-utility">
        {if $pubdata.category}
        <span class="clip-post-category">
            {capture assign='category'}
            <a href="{modurl modname='Clip' func='view' tid=$pubtype.tid filter="category:sub:`$pubdata.category.id`"}" title="{gt text='View all posts in %s' tag1=$pubdata.category.fullTitle}">
                {$pubdata.category.fullTitle|safetext}
            </a>
            {/capture}
            {gt text='Posted in %s' tag1=$category}
        </span>

        <span class="text_separator">|</span>
        {/if}
        <span class="clip-post-permalink">
            {gt text='Permalink to %s' tag1=$pubdata.core_title assign='bookmark_title'}
            {modurl modname='Clip' type='user' func='display' tid=$pubtype.tid pid=$pubdata.core_pid title=$pubdata.core_title|formatpermalink assign='bookmark_url'}
            {gt text='Bookmark the <a rel="bookmark" title="%1$s" href="%2$s">permalink</a>' tag1=$bookmark_title tag2=$bookmark_url}
        </span>

        <span class="text_separator">|</span>

        <span class="clip-post-edit-link">
            {checkpermissionblock component='clip:input:' instance="$pubtype.tid::" level=ACCESS_ADD}
                <span class="z-nowrap">
                    <a href="{modurl modname='Clip' type='user' func='edit' tid=$pubtype.tid pid=$pubdata.core_pid}">{gt text='Edit'}</a>
                </span>
            {/checkpermissionblock}
        </span>
        {*
        <span class="text_separator">|</span>
        {sharethis id=$pubdata.core_uniqueid url=$returnurl title=$pubdata.core_title __text='Share'}
        *}
        {*sexybookmarks url=$returnurl title=$pubdata.core_title*}
    </div>
</div>

<div class="clip-display-hooks">
    {notifydisplayhooks eventname="clip.hook.`$pubtype.tid`.ui.view" area="modulehook_area.clip.item.`$pubtype.tid`" subject=$pubdata module='Clip'}
</div>
