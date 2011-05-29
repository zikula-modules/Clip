{if !$homepage}{pagesetvar name="title" value="`$pubtype.title` - `$modvars.ZConfig.sitename`"}{/if}

{* Open Graph tags
{ogtag prop='title' content=$pubtype.title}
{ogtag prop='type' content='site_section'}
{ogtag prop='image' content=''}
{ogtag prop='url' content=$returnurl}
{ogtag prop='site_name' content=$modvars.ZConfig.sitename}
*}

{include file='clip_generic_navbar.tpl' section='list'}

<h2>{gt text=$pubtype.title}</h2>

<div class="clip-pages-categories">
    {clip_category_browser tid=$pubtype.tid field='category' template='category'}
</div>
