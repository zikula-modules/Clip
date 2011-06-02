{gt text="Editor's Panel" assign='pagetitle'}
{pagesetvar name='title' value="`$pagetitle` - `$modvars.ZConfig.sitename`"}

<div class="clip-editorpanel">
    {include file='clip_editor_header.tpl'}

    {clip_editorpanel data=$grouptypes}
</div>
