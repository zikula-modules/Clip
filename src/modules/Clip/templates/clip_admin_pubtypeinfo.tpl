{pageaddvar name='javascript' value='zikula.ui'}

{include file='clip_admin_header.tpl'}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname='core' src='folder_documents.png' set='icons/large' __alt='Publication type information'}</div>

    {$maincontent}

    <div class="z-right">
        <span class="z-sub">Clip  v{$modinfo.version}</span>
    </div>
</div>
