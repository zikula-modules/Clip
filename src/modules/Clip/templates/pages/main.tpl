{if !$homepage}{pagesetvar name="title" value="`$pubtype.title` - `$modvars.ZConfig.sitename`"}{/if}

{* Open Graph tags
{ogtag prop='title' content=$pubtype.title}
{ogtag prop='type' content='site_section'}
{ogtag prop='image' content=''}
{ogtag prop='url' content=$returnurl}
{ogtag prop='site_name' content=$modvars.ZConfig.sitename}
*}

{include file='clip_generic_navbar.tpl'}

<h2>{$pubtype.title}</h2>

{if $pubtype.description}
<p class="pubtype-description">
    {$pubtype.description|safetext}
</p>
{/if}

{clip_category_browser tid=$pubtype.tid field='category'}