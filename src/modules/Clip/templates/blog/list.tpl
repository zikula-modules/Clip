{if !$homepage}{pagesetvar name="title" value="`$pubtype.title` - `$modvars.ZConfig.sitename`"}{/if}

{* Open Graph tags
{modurl modname='Clip' func='view' tid=$pubtype.tid fqurl=true assign='url'}
{ogtag prop='title' content=$pubtype.title}
{ogtag prop='type' content='site_section'}
{ogtag prop='image' content=''}
{ogtag prop='url' content=$url}
{ogtag prop='site_name' content=$modvars.ZConfig.sitename}
*}

{include file='clip_generic_navbar.tpl' section='list'}

{foreach from=$publist item='item'}
<div id="clip-post-{$item.core_pid}" class="clip-post-{$item.core_pid} clip-post">
    <h2 class="clip-post-title">
        <a rel="bookmark" href="{modurl modname='Clip' type='user' func='display' tid=$pubtype.tid pid=$item.core_pid title=$item.core_title|formatpermalink}">{$item.core_title}</a>
    </h2>
    <div class="clip-post-meta">
        {capture assign='author'}<span class="author vcard">{$item.core_author|profilelinkbyuid}</span>{/capture}
        <span class="clip-post-date">{gt text='Posted on %1$s by %2$s' tag1=$item.core_publishdate|dateformat:'datebrief' tag2=$author}</span>
    </div>
    <div class="clip-post-content">
        {$item.content|safehtml}
    </div>
    <div class="clip-post-utility">
        {if $item.category}
        <span class="clip-post-category">
            {capture assign='category'}
            <a href="{modurl modname='Clip' func='view' tid=$pubtype.tid filter="category:sub:`$item.category.id`"}" title="{gt text='View all posts in %s' tag1=$item.category.fullTitle}">
                {$item.category.fullTitle|safetext}
            </a>
            {/capture}
            {gt text='Posted in %s' tag1=$category}
        </span>

        <span class="text_separator">|</span>
        {/if}

        {*EZComments plugin here*}

        <span class="clip-post-edit-link">
            {* FIXME edit own check? *}
            {checkpermissionblock component='Clip:input:' instance="`$pubtype.tid`:`$pubdata.core_pid`:" level=ACCESS_EDIT}
                <span class="z-nowrap">
                    <a href="{modurl modname='Clip' type='user' func='edit' tid=$pubtype.tid pid=$item.core_pid}">{gt text='Edit'}</a>
                </span>
            {/checkpermissionblock}
        </span>
    </div>
</div>
{foreachelse}
    <div class="z-informationmsg">
        {gt text='No posts found.'}
    </div>
{/foreach}

{if $pager.itemsperpage neq $modvars.Clip.maxperpage}
    {pager display='page' posvar='page' rowcount=$pager.numitems limit=$pager.itemsperpage maxpages=7}
{/if}
