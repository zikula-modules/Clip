
<h2>{gt text='Import pagesetter publications'}</h2>

<ul class="z-menulinks clip-menu">
    <li>
        <a href="{modurl modname='Clip' type='admin' func='modifyconfig'}">{gt text="Back to the module configuration"}</a>
    </li>
</ul>

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
