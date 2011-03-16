{pagesetvar name="title" value="`$pubtype.title` - `$modvars.ZConfig.sitename`"}

{include file='clip_generic_navbar.tpl' section='list'}

<h2>{gt text=$pubtype.title}</h2>

<p>{gt text='Available categories'}:</p>

<div class="clip-pages-categories">
    {clip_category_browser tid=$pubtype.tid field='category' template='category'}
</div>
