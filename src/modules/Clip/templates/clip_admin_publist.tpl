{pageaddvar name='stylesheet' value='system/Theme/style/pagercss.css'}

{include file='clip_admin_header.tpl'}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname='core' src='folder_documents.png' set='icons/small' __alt='Publications list'}</div>

    {$maincontent}

    <div class="z-right">
        <span class="z-sub">Clip  v{$modinfo.version}</span>
    </div>
</div>
