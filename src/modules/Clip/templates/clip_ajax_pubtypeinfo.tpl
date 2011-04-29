
<h2>{$pubtype.title}</h2>

<ul id="pubtype{$pubtype.tid}_info">
    <li class="tab"><a href="#p{$pubtype.tid}manage">{gt text='Manage'}</a></li>
    <li class="tab"><a href="#p{$pubtype.tid}code">{gt text='Get the code'}</a></li>
    {*<li class="tab"><a href="#p{$pubtype.tid}perms">{gt text='Permissions'}</a></li>*}
</ul>

<div id="p{$pubtype.tid}manage" class="clip-infotab">
    <ul>
        <li>
            <a href="javascript:Zikula.Clip.AjaxRequest({ldelim}tid:{$pubtype.tid}{rdelim}, 'pubtype')" title="{gt text='Edit this publication type'}">
                {gt text="Edit '%s'" tag1=$pubtype.title|safetext}
            </a>
            <p>
                {gt text='Edit the basic information of the publication type, the output/input settings, relations settings, etc.'}
            </p>
        </li>
        <li>
            <a href="javascript:Zikula.Clip.AjaxRequest({ldelim}tid:{$pubtype.tid}{rdelim}, 'pubfields')" title="{gt text='Add, sort or modify the fields of this publication type'}">
                {gt text='Manage its fields'}
            </a>
            <p>
                {gt text='Sort, edit and configure the fields of this publication type.'}
            </p>
        </li>
        <li>
            <a href="javascript:Zikula.Clip.AjaxRequest({ldelim}withtid1:{$pubtype.tid}, op:'or', withtid2:{$pubtype.tid}{rdelim}, 'relations')" title="{gt text='Add, sort or modify the fields of this publication type'}">
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
                        <a href="javascript:Zikula.Clip.AjaxRequest({ldelim}id:{$rinfo.id},  withtid1:{$pubtype.tid}, op:'and', withtid2:{$rinfo.tid}{rdelim}, 'relations')">
                            {img width='12' height='12' modname='core' src='edit.png' set='icons/extrasmall' __title='Edit' __alt='Edit'}
                        </a>
                    {else}
                        <a href="javascript:Zikula.Clip.AjaxRequest({ldelim}id:{$rinfo.id},  withtid1:{$rinfo.tid}, op:'and', withtid2:{$pubtype.tid}{rdelim}, 'relations')">
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
            <a href="javascript:Zikula.Clip.AjaxRequest({ldelim}tid:{$pubtype.tid}, mode:'input'{rdelim}, 'showcode')">
                {gt text='Form'}
            </a>
            <span>
                {gt text='Get the input form code of this publication type.'}
            </span>
        </li>
        <li>
            <a href="javascript:Zikula.Clip.AjaxRequest({ldelim}tid:{$pubtype.tid}, mode:'outputlist'{rdelim}, 'showcode')">
                {gt text='List'}
            </a>
            <span>
                {gt text='Get the list code of this publication type.'}
            </span>
        </li>
        <li>
            <a href="javascript:Zikula.Clip.AjaxRequest({ldelim}tid:{$pubtype.tid}, mode:'outputfull'{rdelim}, 'showcode')">
                {gt text='Display'}
            </a>
            <span>
                {gt text='Get the display code of this publication type.'}
            </span>
        </li>
        <li>
            <a href="javascript:Zikula.Clip.AjaxRequest({ldelim}tid:{$pubtype.tid}, mode:'blocklist'{rdelim}, 'showcode')">
                {gt text='List block'}
            </a>
            <span>
                {gt text='Get the generic code for a publications List block.'}
            </span>
        </li>
        <li>
            <a href="javascript:Zikula.Clip.AjaxRequest({ldelim}tid:{$pubtype.tid}, mode:'blockpub'{rdelim}, 'showcode')">
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

<script type="text/javascript">
    var pubtypetabs = new Zikula.UI.Tabs('pubtype{{$pubtype.tid}}_info', {equal: true});
</script>