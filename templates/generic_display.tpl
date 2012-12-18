
{clip_hitcount}

<div class="clip-wrapper clip-display clip-display-{$pubtype.urltitle}">
    {include file='generic_navbar.tpl'}

    <h2>{$pubdata.core_title|safetext}</h2>

    <div class="z-form clip-pagedetails">
    {$code}
    </div>

    <div class="clip-hooks-display">
        {notifydisplayhooks eventname=$pubtype->getHooksEventName() urlObject=$pubdata->clipUrl() id=$pubdata.core_pid}
    </div>
</div>
