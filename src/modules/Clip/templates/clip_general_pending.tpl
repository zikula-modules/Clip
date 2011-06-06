
{include file='clip_generic_navbar.tpl'}

<h2>{$pubtype.title}</h2>

<p class="z-statusmsg">{gt text='Publication accepted, pending moderation.'}</p>

<p>
    {gt text='Thanks for your submission!'}
    <br />
    <a href="{modurl modname='Clip' type='user' func='main' tid=$pubtype.tid}">
        {gt text='Go back to the index'}
    </a>
</p>
