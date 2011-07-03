
{if !$homepage}{pagesetvar name='title' value="`$pubdata.core_title` - `$pubtype.title` - `$modvars.ZConfig.sitename`"}{/if}
{clip_hitcount pid=$pubdata.core_pid tid=$pubdata.core_tid}

<div class="clip-display clip-display-{$pubtype.urltitle}">
    {include file='clip_generic_navbar.tpl'}

    <h2>{$pubdata.core_title|safetext}</h2>

    <div class="z-form clip-pagedetails">
    {$code}
    </div>

    <div class="clip-hooks-display">
        {notifydisplayhooks eventname="clip.ui_hooks.pubtype`$pubtype.tid`.display_view" id=$pubdata.core_uniqueid}
    </div>
</div>
