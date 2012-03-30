
<div class="z-admin-content-pagetitle">
    {img modname='core' src='folder_documents.png' set='icons/small' __title='Publication type information' alt=''}
    <h3>{$pubtype.title}</h3>
    {clip_adminmenu}
</div>

{insert name='getstatusmsg'}

{$pubtype.description|paragraph}

<ul id="pubtype{$pubtype.tid}_info">
    <li class="tab"><a href="#p{$pubtype.tid}manage">{gt text='Manage'}</a></li>
    <li class="tab"><a href="#p{$pubtype.tid}code">{gt text='Get the code'}</a></li>
    <li class="tab"><a href="#p{$pubtype.tid}fields">{gt text='Fields'}</a></li>
    {*<li class="tab"><a href="#p{$pubtype.tid}perms">{gt text='Permissions'}</a></li>*}
</ul>

<div id="p{$pubtype.tid}manage" class="clip-infotab">
    <ul class="z-floatright z-buttons">
        <li>
            <a class="z-bt-new" href="{modurl modname='Clip' type='user' func='edit' tid=$pubtype.tid}" title="{gt text='Add new publications to this publication type'}">
                {gt text='New article'}
            </a>
        </li>
        <li>
            <a class="z-bt-preview" href="{modurl modname='Clip' type='editor' func='list' tid=$pubtype.tid}" title="{gt text='Go to the Editor publication list'}">
                {gt text='Editor list'}
            </a>
        </li>
        <li>
            <a class="z-bt-preview" href="{modurl modname='Clip' type='user' func='list' tid=$pubtype.tid}" title="{gt text='Go to the public publication list'}">
                {gt text='Public list'}
            </a>
        </li>
    </ul>
    <ul>
        <li class="z-clearfix">
            <a href="{clip_url func='pubtype'}" title="{gt text='Edit this publication type'}">
                {gt text="Edit '%s'" tag1=$pubtype.title|safetext}
            </a>
            <p>
                {gt text='Edit the basic information of the publication type, the admin, user and relations settings.'}
            </p>

            {gt text='None' assign='none'}
            <dl class="z-floatleft z-w30">
                <dt>{gt text='URL title'}</dt>
                    <dd>{$pubtype.urltitle|default:$none}</dd>
                <dt>{gt text='Folder'}</dt>
                    <dd>{$pubtype.folder|default:$none}</dd>
                <dt>{gt text='Workflow'}</dt>
                    <dd>{$pubtype.workflow}</dd>
            </dl>
            <dl class="z-floatleft z-w30">
                <dt>{gt text='Fixed filter'}</dt>
                    <dd>{$pubtype.fixedfilter|default:$none}</dd>
                <dt>{gt text='Default filter'}</dt>
                    <dd>{$pubtype.defaultfilter|default:$none}</dd>
                <dt>{gt text='Items per page'}</dt>
                    <dd>{$pubtype.itemsperpage|default:0}</dd>
            </dl>
        </li>
        <li class="z-clearfix">
            <a href="{clip_url func='pubfields'}" title="{gt text='Add, sort or modify the fields of this publication type'}">
                {gt text='Manage its fields'}
            </a>
            <p>
                {gt text='Sort, edit and configure the fields of this publication type.'}
            </p>
        </li>
        <li class="z-clearfix">
            <a href="{clip_url func='relations' withtid1=$pubtype.tid op='or' withtid2=$pubtype.tid}" title="{gt text='Edit the relations of this publication type'}">
                {gt text='Manage its relations'}
            </a>
            <p>
                {gt text='Edit and configure the relations.'}<br /><br />
            </p>
        </li>
    </ul>
</div>

<div id="p{$pubtype.tid}code" class="clip-infotab">
    <ul>
        <li>
            <a href="{clip_url func='generator' code='edit'}">
                {gt text='Form'}
            </a>
            <p>
                {gt text='Get the input form code of this publication type.'}
            </p>
        </li>
        <li>
            <a href="{clip_url func='generator' code='list'}">
                {gt text='List'}
            </a>
            <p>
                {gt text='Get the list code of this publication type.'}
            </p>
        </li>
        <li>
            <a href="{clip_url func='generator' code='display'}">
                {gt text='Display'}
            </a>
            <p>
                {gt text='Get the display code of this publication type.'}
            </p>
        </li>
        <li>
            <a href="{clip_url func='generator' code='blocklist'}">
                {gt text='List block'}
            </a>
            <p>
                {gt text='Get the generic code for a publications List block.'}
            </p>
        </li>
        <li>
            <a href="{clip_url func='generator' tid=$pubtype.tid code='blockpub'}">
                {gt text='Publication block'}
            </a>
            <p>
                {gt text='Get the generic code for a Publication block.'}
            </p>
        </li>
    </ul>
</div>

<div id="p{$pubtype.tid}fields" class="clip-infotab">
    <a class="z-floatright" href="{clip_url func='pubfields' fragment='newpubfield'}">{gt text='Add a new field'}</a>

    <h4>{gt text='Fields'}</h4>

    <table class="z-datatable">
        <thead>
            <th class="z-w10">{gt text='Alias'}</th>
            <th>{gt text='Title'}</th>
            <th>{gt text='Description'}</th>
            <th class="z-w10">{gt text='Plugin'}</th>
            <th class="z-w05">{gt text='Edit'}</th>
        </thead>
        <tbody>
            {foreach from=$pubtype->getFields(true) item='item'}
            <tr class="{cycle name='pfieldlist' values='z-even,z-odd'}">
                <td><code>{$item.name|safetext}</code></td>
                <td>{$item.title|safetext}</td>
                <td>{$item.description|safetext}</td>
                <td>{$item.fieldplugin|clip_plugintitle|safetext}</td>
                <td>
                    <a href="{clip_url func='pubfields' tid=$item.tid id=$item.id fragment='newpubfield'}">
                        {img width='12' height='12' modname='core' src='edit.png' set='icons/extrasmall' __title='Edit' __alt='Edit'}
                    </a>
                    {if $item.attrs.cid|default:false}
                    <a href="{modurl modname='Categories' type='user' func='edit' dr=$item.attrs.cid}">
                        {img width='12' height='12' modname='core' src='xedit.png' set='icons/extrasmall' __title='List' __alt='List'}
                    </a>
                    {/if}
                </td>
            </tr>
            {foreachelse}
            <tr>
                <td colspan="5">{gt text='There are no fields defined yet.'}</td>
            </tr>
            {/foreach}
        </tbody>
    </table>

    <a class="z-floatright" href="{clip_url func='relations' withtid1=$item.tid op='or' withtid2=$pubtype.tid fragment='relform'}">{gt text='Add a new relation'}</a>

    <h4>{gt text='Relations'}</h4>

    <table class="z-datatable">
        <thead>
            <tr>
                <th class="z-w10">{gt text='Alias'}</th>
                <th>{gt text='Title'}</th>
                <th>{gt text='Description'}</th>
                <th class="z-w10">{gt text='Opposite'}</th>
                <th class="z-w05">{gt text='Edit'}</th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$relations item='item'}
            <tr class="{cycle name='prelationlist' values='z-even,z-odd'}">
                <td><code>{$item.alias|safetext}</code></td>
                <td>
                    {capture assign='rellink'}{strip}
                        </span>
                        {if $pubtype.tid neq $item.tid}
                            <a href="{clip_url func='pubtypeinfo' tid=$item.tid}">{$item.title|safetext}</a>
                        {else}
                            {$item.title|safetext}
                        {/if}
                    {/strip}{/capture}
                    {if $item.single}
                        <span class="z-sub">{gt text='Has one %s' tag1=$rellink}
                    {else}
                        <span class="z-sub">{gt text='Has many %s' tag1=$rellink}
                    {/if}
                </td>
                <td>{$item.descr|safetext}</td>
                <td>{$item.opposite|safetext}</td>
                <td>
                    {if $item.own}
                        <a href="{clip_url func='relations' id=$item.id tid=$pubtype.tid withtid1=$pubtype.tid op='or' withtid2=$item.tid}">
                            {img width='12' height='12' modname='core' src='edit.png' set='icons/extrasmall' __title='Edit' __alt='Edit'}
                        </a>
                    {else}
                        <a href="{clip_url func='relations' id=$item.id tid=$pubtype.tid withtid1=$item.tid op='or' withtid2=$pubtype.tid}">
                            {img width='12' height='12' modname='core' src='edit.png' set='icons/extrasmall' __title='Edit' __alt='Edit'}
                        </a>
                    {/if}
                </td>
            </tr>
            {foreachelse}
            <tr>
                <td colspan="5">{gt text='There are no relations defined.'}</td>
            </tr>
            {/foreach}
        </tbody>
    </table>
</div>

{*<div id="p{$pubtype.tid}perms" class="clip-infotab">
    TODO
</div>*}

<script type="text/javascript">
    var pubtypetabs = new Zikula.UI.Tabs('pubtype{{$pubtype.tid}}_info', {equal: true});
    var maxWidth = $$('.clip-infotab ul.z-buttons a').invoke('getContentWidth').max();
    $$('.clip-infotab ul.z-buttons a').invoke('setStyle', {width: maxWidth.toUnits()});
</script>
