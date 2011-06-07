
{if !$homepage}{pagesetvar name="title" value="`$pubdata.core_title` - `$pubtype.title` - `$modvars.ZConfig.sitename`"}{/if}
{clip_hitcount pid=$pubdata.core_pid tid=$pubdata.core_tid}

{include file='clip_generic_navbar.tpl'}

<h2>{$pubdata.core_title|safetext}</h2>

<div class="z-form clip-pub-details">
{$code}
</div>

<div class="clip-display-hooks">
    {notifydisplayhooks eventname="clip.ui_hooks.pubtype`$pubtype.tid`.display_view" id=$pubdata.core_uniqueid}
</div>
