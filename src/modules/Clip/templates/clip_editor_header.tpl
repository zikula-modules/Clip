
{switch expr=$func}
    {case expr='main'}
    <div class="clip-editorpanelicon">{img modname='core' src='package_editors.png' set='icons/large' alt=''}</div>
    {/case}
    {case expr='list'}
    <div class="clip-editorpanelicon">{img modname='core' src='lists.png' set='icons/large' alt=''}</div>
    {/case}
{/switch}

{* hidden as navbar includes an icon to the Admin Panel
    <div id="clip_editor_buttons" class="z-floatright z-buttons">
        <a class="clip-bt-configure" title="{gt text='Switch to the admin panel'}" href="{modurl modname='Clip' type='admin' func='main'}">
            {gt text='Admin Panel'}
        </a>
    </div>
*}
    <h2>{$pagetitle}</h2>

    {include file='clip_generic_navbar.tpl'}
