
<div class="z-admin-content-pagetitle">
    {icon type='hook' size='small'}
    {if $tid}
        <h3>{$pubtypes[$tid].title} &raquo; {gt text='Relations'}</h3>
        {clip_adminmenu}
    {else}
        <h3>{gt text='Manage Relations'}</h3>
    {/if}
</div>

{insert name='getstatusmsg'}

{form cssClass='z-form' enctype='application/x-www-form-urlencoded'}
<div class="z-list-relations">
    <fieldset>
        <legend>{gt text='Relations'}</legend>

        <span class="z-nowrap">
            {formlabel for='withtid1' __text='Filter by owner'}
            {formdropdownlist items=$typeselector id='withtid1' group='filter'}
        </span>
        <span class="z-nowrap">
            {formdropdownlist items=$ops id='op' group='filter'}
        </span>
        <span class="z-nowrap">
            {formlabel for='withtid2' __text='related'}
            {formdropdownlist items=$typeselector id='withtid2' group='filter'}
        </span>
        <span class="z-nowrap z-buttons">
            {formbutton commandName='filter' __text='Filter' class='z-bt-small'}
            {formbutton commandName='clear' __text='Clear' class='z-bt-small'}
        </span>

        {assign var='filteredlist' value=0}
        {if $filter.withtid1 OR $filter.withtid2}
            <div class="z-warningmsg">
            {if $filter.withtid1 OR $filter.withtid2}
                {assign var='filteredlist' value=1}
            {/if}
            {if $filter.withtid1 AND $filter.withtid2}
                {gt text=$filter.op assign='op'}
                {gt text='List filtered by [%1$s] as Owner %2$s [%3$s] as Related' tag1=$pubtypes[$filter.withtid1].title tag2=$op tag3=$pubtypes[$filter.withtid2].title}
            {elseif $filter.withtid1}
                {gt text='List filtered by [%s] as Owner' tag1=$pubtypes[$filter.withtid1].title}
            {elseif $filter.withtid2}
                {gt text='List filtered by [%s] as Related' tag1=$pubtypes[$filter.withtid2].title}
            {/if}
            </div>
        {/if}

        <ul id="relationslist" class="z-itemlist">
            <li id="relationslistheader" class="relationslistheader z-itemheader z-clearfix">
                <span class="z-itemcell z-w10">{gt text='ID'}</span>
                <span class="z-itemcell z-w40 z-right">{gt text='Owning side'}&nbsp;&nbsp;</span>
                <span class="z-itemcell z-w40">{gt text='Related side'}</span>
                <span class="z-itemcell z-w10">{gt text='Actions'}</span>
            </li>
            {foreach from=$relations item='item' name='relation'}
            <li id="relations_{$item.id}" class="{cycle name='relationslist' values='z-even,z-odd'} z-clearfix">
                <span class="z-itemcell z-w10">
                   {$item.id}
                </span>
                <span class="z-itemcell z-w40 z-right">
                    {if $item.type lt 2}
                        {gt text='One <strong>%s</strong>' tag1=$pubtypes[$item.tid1].title|safetext}
                    {else}
                        {gt text='Many <strong>%s</strong>' tag1=$pubtypes[$item.tid1].title|safetext}
                    {/if}
                    &nbsp;
                    <br />
                    <span class="z-sub" title="{$item.descr1|safetext}">{$item.title1|safetext} | <code>$pubdata.{$item.alias1|safetext}</code></span>
                    &nbsp;&nbsp;
                </span>
                <span class="z-itemcell z-w40">
                    {if $item.type%2 eq 0}
                        {gt text='has One <strong>%s</strong>' tag1=$pubtypes[$item.tid2].title|safetext}
                    {else}
                        {gt text='has Many <strong>%s</strong>' tag1=$pubtypes[$item.tid2].title|safetext}
                    {/if}
                    <br />&nbsp;
                    <span class="z-sub" title="{$item.descr2|safetext}"><code>$pubdata.{$item.alias2|safetext}</code> | {$item.title2|safetext}</span>
                </span>
                <span class="z-itemcell z-w10">
                    <a href="{clip_url func='relations' tid=$tid id=$item.id withtid1=$filter.withtid1 op=$filter.op withtid2=$filter.withtid2 fragment='relform'}">
                        {img modname='core' src='edit.png' set='icons/extrasmall' __title='Edit' __alt='Edit'}
                    </a>
                </span>
            </li>
            {foreachelse}
            <li class="z-odd z-clearfix z-center">
                {if $filteredlist}
                    {gt text='No relations matches the current filter.'}
                {else}
                    {gt text='There are no relations specified yet.'}
                {/if}
            </li>
            {/foreach}
        </ul>
    </fieldset>
</div>

<div class="z-form-relations">
    {formvalidationsummary}
    <fieldset id="relform">
        {if isset($relation)}
            <legend>{gt text='Edit relation'}</legend>
        {else}
            <legend>{gt text='Add a relation'}</legend>
        {/if}
        <div class="z-formrow">
        {if isset($relation)}
            <span class="z-label">{gt text='Definition:'}</span>
            <span class="z-formnote">
                {if $relation.type1 eq 0}{gt text='One'}{else}{gt text='Many'}{/if}
                <strong>{$pubtypes[$relation.tid1].title}</strong> ({$relation.tid1})
                {if $relation.type2 eq 0}{gt text='has One'}{else}{gt text='has Many'}{/if}
                <strong>{$pubtypes[$relation.tid2].title}</strong> ({$relation.tid2})
            </span>
        {else}
            {formlabel for='type1' __text='Definition:' mandatorysym=true}
            {formdropdownlist items=$reltypes.0 id='type1' group='relation'}
            {formdropdownlist items=$typeselector id='tid1' group='relation'}
            {formdropdownlist items=$reltypes.1 id='type2' group='relation'}
            {formdropdownlist items=$typeselector id='tid2' group='relation'}
        {/if}
        </div>
        <div class="z-linear" style="padding-top: 0.5em">
            <div class="z-floatleft z-w49">
                <fieldset>
                    <legend>{gt text='Owning side'}</legend>
                    <div class="z-formrow">
                        {formlabel for='alias1' __text='Alias' mandatorysym=true}
                        {formtextinput id='alias1' group='relation' maxLength='100' mandatory=true regexValidationPattern='/^[a-zA-Z0-9_]+$/' __regexValidationMessage='Alias cannot contain special characters, only a-z, A-Z, 0-9 and _'}
                    </div>
                    <div class="z-formrow">
                        {formlabel for='title1' __text='Title' mandatorysym=true}
                        {formtextinput id='title1' group='relation' maxLength='100' mandatory=true}
                    </div>
                    <div class="z-formrow">
                        {formlabel for='descr1' __text='Description'}
                        {formtextinput id='descr1' maxLength='4000' group='relation'}
                    </div>
                </fieldset>
            </div>
            <div class="z-floatright z-w49">
                <fieldset>
                    <legend>{gt text='Related side'}</legend>
                    <div class="z-formrow">
                        {formlabel for='alias2' __text='Alias' mandatorysym=true}
                        {formtextinput id='alias2' group='relation' maxLength='100' mandatory=true regexValidationPattern='/^[a-zA-Z0-9_]+$/' __regexValidationMessage='Alias cannot contain special characters, only a-z, A-Z, 0-9 and _'}
                    </div>
                    <div class="z-formrow">
                        {formlabel for='title2' __text='Title' mandatorysym=true}
                        {formtextinput id='title2' group='relation' maxLength='100' mandatory=true}
                    </div>
                    <div class="z-formrow">
                        {formlabel for='descr2' __text='Description'}
                        {formtextinput id='descr2' maxLength='4000' group='relation'}
                    </div>
                </fieldset>
            </div>
        </div>
        <div class="z-informationmsg z-clearer">
            {gettext}
            Explanation of the side details:
            <ul style="margin-bottom: 0">
                <li><strong>Alias</strong>: Used as a field name on the publication code. i.e. $pubdata.Alias1</li>
                <li><strong>Title</strong>: Used in the user side forms as the public name of the relation.</li>
                <li><strong>Description</strong>: Also used in the forms as the autocompleter tooltip.</li>
            </ul>
            {/gettext}
        </div>
    </fieldset>

    <div class="z-buttons z-formbuttons">
        {if isset($relation)}
            {formbutton commandName='save' __text='Save' class='z-bt-save'}
            {gt text='Are you sure you want to delete this relation?' assign='confirmdeletion'}
            {formbutton commandName='delete' __text='Delete' class='z-btred z-bt-delete' confirmMessage=$confirmdeletion}
        {else}
            {formbutton commandName='save' __text='Create' class='z-bt-ok'}
        {/if}
        <input class="clip-bt-reload" type="reset" value="{gt text='Reset'}" title="{gt text='Reset the form to its initial state'}" />
        {formbutton commandName='cancel' __text='Cancel' class='z-bt-cancel'}
    </div>
</div>
{/form}
