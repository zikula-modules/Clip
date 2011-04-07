{pagesetvar name="title" value="`$pubdata.core_title` - `$pubtype.title` - `$modvars.ZConfig.sitename`"}
{clip_hitcount pid=$pubdata.core_pid tid=$pubdata.core_tid}

{include file='clip_generic_navbar.tpl' section='display'}

<div id="clip-page-{$pubdata.core_pid}" class="clip-page clip-page-{$pubdata.core_pid}">
    <h2 class="clip-page-title">
        {gt text=$pubdata.core_title}
    </h2>

    <div class="clip-page-content">
        {$pubdata.content|safehtml|notifyfilters:"clip.hook.`$pubtype.tid`.ui.filter"}
    </div>

    {if $pubdata.displayinfo}
    <div class="clip-page-info">
        {capture assign='author'}<span class="author vcard">{$pubdata.core_author|uidprofilelink}</span>{/capture}
        <span class="clip-page-date">{gt text='Posted by %1$s on %2$s' tag1=$author tag2=$pubdata.core_publishdate|dateformat:'datelong'}</span>

        <br />
        {checkpermissionblock component='clip:input:' instance="$pubtype.tid::" level=ACCESS_ADD}
        <span class="clip-page-edit-link">
            <span class="z-nowrap">
                <a href="{modurl modname='Clip' type='user' func='edit' tid=$pubtype.tid pid=$pubdata.core_pid}">{gt text='Edit'}</a>
            </span>
        </span>

        <span class="text_separator">|</span>
        {/checkpermissionblock}

        <span class="clip-page-reads">{gt text='%s read' plural='%s reads' count=$pubdata.core_hitcount tag1=$pubdata.core_hitcount}</span>

        {if $pubdata.category}
        <span class="text_separator">|</span>

        <span class="clip-page-category">
            {gt text='Category:'}
            <a href="{modurl modname='Clip' func='view' tid=$pubtype.tid filter="category:sub:`$pubdata.category.id`"}" title="{gt text='View all posts in %s' tag1=$pubdata.category.fullTitle}">
                {$pubdata.category.fullTitle|safetext}
            </a>
        </span>
        {/if}
    </div>
    {/if}
</div>

{notifydisplayhooks eventname="clip.hook.`$pubtype.tid`.ui.view" area="modulehook_area.clip.item.`$pubtype.tid`" subject=$pubdata module='Clip'}
