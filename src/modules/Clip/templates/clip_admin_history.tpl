
{include file='clip_admin_header.tpl'}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname='core' src='clock.png' set='icons/large' __alt='History'}</div>

    <h2>{gt text='History'}</h2>

    {clip_admin_submenu tid=$pubtype.tid}

    {$maincontent}
</div>
