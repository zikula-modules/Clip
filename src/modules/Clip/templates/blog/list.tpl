{if !$homepage}{pagesetvar name="title" value="`$pubtype.title` - `$modvars.ZConfig.sitename`"}{/if}

{* Open Graph tags
{ogtag prop='title' content=$pubtype.title}
{ogtag prop='type' content='site_section'}
{ogtag prop='image' content=''}
{ogtag prop='url' content=$returnurl}
{ogtag prop='site_name' content=$modvars.ZConfig.sitename}
*}

<div class="clip-list clip-list-{$pubtype.urltitle}">
    {include file='clip_generic_navbar.tpl'}

    <div class="clip-list-items">
    {foreach from=$publist item='pubdata'}
        <div id="clip-post-{$pubdata.core_pid}" class="clip-listpost">
            <h2 class="clip-post-title">
                <a rel="bookmark" href="{modurl modname='Clip' type='user' func='display' tid=$pubtype.tid pid=$pubdata.core_pid title=$pubdata.core_title|formatpermalink}">{$pubdata.core_title|safetext}</a>
            </h2>
            <div class="clip-post-meta">
                {capture assign='author'}<span class="author vcard">{$pubdata.core_author|profilelinkbyuid}</span>{/capture}
                <span class="clip-post-date">{gt text='Posted on %1$s by %2$s' tag1=$pubdata.core_publishdate|dateformat:'datebrief' tag2=$author}</span>
            </div>
            <div class="clip-post-content">
                {$pubdata.content|safehtml}
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

                {*EZComments counter plugin here*}

                <span class="clip-post-edit-link">
                    {clip_accessblock tid=$pubtype.tid pid=$pubdata context='edit'}
                    <span class="z-nowrap">
                        <a href="{modurl modname='Clip' type='user' func='edit' tid=$pubtype.tid pid=$pubdata.core_pid}">{gt text='Edit'}</a>
                    </span>
                    {/clip_accessblock}
                </span>
            </div>
        </div>
    {foreachelse}
        <div class="z-informationmsg">
            {gt text='No posts found.'}
        </div>
    {/foreach}
    </div>

    {if $pager.itemsperpage neq $modvars.Clip.maxperpage}
        {if $clipargs.list.startnum eq 0}
            {pager display='page' posvar='page' rowcount=$pager.numitems limit=$pager.itemsperpage maxpages=7}
        {else}
            {pager display='startnum' posvar='startnum' rowcount=$pager.numitems limit=$pager.itemsperpage maxpages=7}
        {/if}
    {/if}
</div>
