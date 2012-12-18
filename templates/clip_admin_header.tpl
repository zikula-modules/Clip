{admincategorymenu}

<div class="z-admin-content z-clearfix">
    <div id="clip_admin_buttons" class="z-floatright z-buttons">
        <a class="z-bt-preview" title="{gt text='Switch to the editor context'}" href="{modurl modname='Clip' type='editor' func='main'}">
            {gt text='Editor Panel'}
        </a>
        {if $func eq 'main'}
        <a class="z-bt-new" title="{gt text='Create a new publication type'}" href="{clip_url type='ajax' func='pubtype' tid=0}">
            {gt text='New publication type'}
        </a>
        {else}
        <a class="z-bt-new" title="{gt text='Create a new publication type'}" href="{modurl modname='Clip' type='admin' func='pubtype'}">
            {gt text='New publication type'}
        </a>
        {/if}
    </div>

    <div class="z-admin-content-modtitle">
        {img modname='Clip' src='admin.png' height='36'}
        <h2>{modgetinfo modname='Clip' info='displayname'}</h2>
    </div>

    {modulelinks modname='Clip' type='admin'}

{* must be used with the adminfooter *}