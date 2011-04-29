{pageaddvar name='javascript' value='zikula.ui'}

{include file='clip_admin_header.tpl'}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname='core' src='folder_documents.png' set='icons/large' __alt='Publication type information'}</div>

    <h2>{$pubtype.title}</h2>

    <ul id="pubtype{$pubtype.tid}_info">
        <li class="tab"><a href="#p{$pubtype.tid}manage">{gt text='Manage'}</a></li>
        <li class="tab"><a href="#p{$pubtype.tid}code">{gt text='Get the code'}</a></li>
        {*<li class="tab"><a href="#p{$pubtype.tid}perms">{gt text='Permissions'}</a></li>*}
    </ul>

    <div id="p{$pubtype.tid}manage" class="clip-infotab">
        <ul class="float_right z-buttons">
            <li>
                <a class="z-bt-new" href="{modurl modname='Clip' type='user' func='edit' tid=$pubtype.tid}" title="{gt text='Add new publications to this publication type'}">
                    {gt text='New publication'}
                </a>
            </li>
            <li>
                <a class="z-bt-preview" href="{modurl modname='Clip' type='admin' func='publist' tid=$pubtype.tid}" title="{gt text='Go to the Admin publication list'}">
                    {gt text='Admin list'}
                </a>
            </li>
            <li>
                <a class="z-bt-preview" href="{modurl modname='Clip' type='user' func='view' tid=$pubtype.tid}" title="{gt text='Go to the public publication list'}">
                    {gt text='Public list'}
                </a>
            </li>
        </ul>
        <ul>
            <li>
                <a href="{modurl modname='Clip' type='admin' func='pubtype' tid=$pubtype.tid}" title="{gt text='Edit this publication type'}">
                    {gt text="Edit '%s'" tag1=$pubtype.title|safetext}
                </a>
                <p>
                    {gt text='Edit the basic information of the publication type, the output/input settings, relations settings, etc.'}
                </p>
            </li>
            <li>
                <a href="{modurl modname='Clip' type='admin' func='pubfields' tid=$pubtype.tid}" title="{gt text='Add, sort or modify the fields of this publication type'}">
                    {gt text='Manage its fields'}
                </a>
                <p>
                    {gt text='Sort, edit and configure the fields of this publication type.'}
                </p>
            </li>
            <li>
                <a href="{modurl modname='Clip' type='admin' func='relations' withtid1=$pubtype.tid op='or' withtid2=$pubtype.tid}" title="{gt text='Add, sort or modify the fields of this publication type'}">
                    {gt text='Manage its relations'}
                </a>
                <p>
                    {gt text='Edit and configure the relations.'}<br /><br />
                    {if $pubtype->getRelations(false)}
                        {gt text='Currently they are as follows:'}
                    {else}
                        {gt text='There are no relations defined for this publication type.'}
                    {/if}
                </p>
                {if $pubtype->allrelations}
                    <ul>
                    {foreach from=$pubtype->allrelations key='ralias' item='rinfo'}
                        <li>
                        {if $rinfo.single}
                            {gt text='Has one %s' tag1=$rinfo.title}
                        {else}
                            {gt text='Has many %s' tag1=$rinfo.title}
                        {/if}
                        {if $rinfo.own}
                            <a href="{modurl modname='Clip' type='admin' func='relations' id=$rinfo.id withtid1=$pubtype.tid op='and' withtid2=$rinfo.tid}">
                                {img width='12' height='12' modname='core' src='edit.png' set='icons/extrasmall' __title='Edit' __alt='Edit'}
                            </a>
                        {else}
                            <a href="{modurl modname='Clip' type='admin' func='relations' id=$rinfo.id withtid1=$rinfo.tid op='and' withtid2=$pubtype.tid}">
                                {img width='12' height='12' modname='core' src='edit.png' set='icons/extrasmall' __title='Edit' __alt='Edit'}
                            </a>
                        {/if}
                        </li>
                    {/foreach}
                    </ul>
                {/if}
            </li>
        </ul>
    </div>

    <div id="p{$pubtype.tid}code" class="clip-infotab">
        <ul>
            <li>
                <a href="{modurl modname='Clip' type='admin' func='showcode' mode='input' tid=$pubtype.tid}">
                    {gt text='Form'}
                </a>
                <span>
                    {gt text='Get the input form code of this publication type.'}
                </span>
            </li>
            <li>
                <a href="{modurl modname='Clip' type='admin' func='showcode' mode='outputlist' tid=$pubtype.tid}">
                    {gt text='List'}
                </a>
                <span>
                    {gt text='Get the list code of this publication type.'}
                </span>
            </li>
            <li>
                <a href="{modurl modname='Clip' type='admin' func='showcode' mode='outputfull' tid=$pubtype.tid}">
                    {gt text='Display'}
                </a>
                <span>
                    {gt text='Get the display code of this publication type.'}
                </span>
            </li>
            <li>
                <a href="{modurl modname='Clip' type='admin' func='showcode' mode='blocklist' tid=$pubtype.tid}">
                    {gt text='List block'}
                </a>
                <span>
                    {gt text='Get the generic code for a publications List block.'}
                </span>
            </li>
            <li>
                <a href="{modurl modname='Clip' type='admin' func='showcode' mode='blockpub' tid=$pubtype.tid}">
                    {gt text='Publication block'}
                </a>
                <span>
                    {gt text='Get the generic code for a Publication block.'}
                </span>
            </li>
        </ul>
    </div>

    {*<div id="p{$pubtype.tid}perms" class="clip-infotab">
        TODO
    </div>*}
</div>

<script type="text/javascript">
    var pubtypetabs = new Zikula.UI.Tabs('pubtype{{$pubtype.tid}}_info', {equal: true});
    var maxWidth = $$('.clip-infotab ul.z-buttons a').invoke('getContentWidth').max();
    $$('.clip-infotab ul.z-buttons a').invoke('setStyle', {width: maxWidth.toUnits()});
</script>
