
<h1>{$pubtype.title}</h1>

{include file='clip_generic_navbar.tpl'}

<p class="z-statusmsg">{gt text='Publication accepted, pending moderation.'}</p>

<p>
    {gt text='Thanks for your submission!'}
    <br />
    <a href="{modurl modname='Clip' tid=$pubtype.tid}">
        {gt text='Go back to the list'}
    </a>
</p>
