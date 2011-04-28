
{admincategorymenu}

<div class="z-adminbox">
    <div id="clip_admin_buttons" class="z-floatright z-buttons">
        {*<a class="z-bt-preview" title="{gt text='Switch to the editor context'}" href="{modurl modname='Clip' type='editor' func='main'}">
            {gt text='Editor context'}
        </a>*}
        <a class="z-bt-new" title="{gt text='Create a new publication type'}" href="{modurl modname='Clip' type='admin' func='pubtype'}">
            {gt text='New publication type'}
        </a>
    </div>

    <h1>{$modinfo.displayname} v{$modinfo.version} &raquo; {gt text='Module Management'}</h1>
    <div class="z-warningmsg">
        <strong>Development note</strong>: Clip's Admin Panel is under rework, some ajax links are broken.
        <br />Edit publications and the editlist block will get deprecated with the new Editors interface.
    </div>
    {modulelinks modname='Clip' type='admin'}
</div>
