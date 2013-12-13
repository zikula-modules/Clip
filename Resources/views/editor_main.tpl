{gt text="Editor's Panel" assign='pagetitle'}
{pagesetvar name='title' value="`$pagetitle` - `$modvars.ZConfig.sitename`"}

<div class="clip-editorpanel">
    {clip_include file='editor_header.tpl'}

    {clip_editorpanel data=$grouptypes}
</div>
