{pageaddvar name='stylesheet' value='system/Theme/style/pagercss.css'}

{include file='clip_admin_header.tpl'}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname='core' src='folder_documents.png' set='icons/large' __alt='Publications list'}</div>

    <h2>{gt text='Publications list'}</h2>

    {clip_admin_submenu tid=$pubtype.tid}

    {$maincontent}
</div>
