
{include file='clip_admin_header.tpl'}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname='core' src='exec.png' set='icons/large' __alt='Show code'}</div>

    <h2>{gt text='Show code'}</h2>

    {clip_admin_submenu tid=$pubtype.tid mode=$mode}

    {$maincontent}
</div>
