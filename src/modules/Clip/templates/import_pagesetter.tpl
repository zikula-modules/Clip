{include file='clip_admin_header.tpl'}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname='core' src='folder_sent_mail.png' set='icons/small' __alt='Export'}</div>

    <h3>{gt text='Import pagesetter publications'}</h3>

    {if $alreadyexists}
    <p class="z-warningmsg">{gt text='Be sure to know what you are doing on this process.'}</p>
    {/if}

    <p>{gt text='On this page you can import publications from Pagesetter.<br />You can only import publications when no Clip publication exist.<br />Please configure the Upload Path before you import something.<br />No Pagesetter data will be changed due the import.'}</p>

    <ul>
        <li>
            <a href="{modurl modname='Clip' type='import' func='importps' step='1'}">{gt text='Import lists,'}</a>
        </li>
        <li>
            <a href="{modurl modname='Clip' type='import' func='importps' step='2'}">{gt text="Import publication types,"}</a>
        </li>
        <li>
            <a href="{modurl modname='Clip' type='import' func='importps' step='3'}">{gt text="Create the database tables,"}</a>
        </li>
        <li>
            <a href="{modurl modname='Clip' type='import' func='importps' step='4'}">{gt text="Import data."}</a>
        </li>
    </ul>
</div>
{adminfooter}