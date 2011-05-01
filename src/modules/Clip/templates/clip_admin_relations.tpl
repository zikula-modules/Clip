
{include file='clip_admin_header.tpl'}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname='core' src='agt_softwareD.png' set='icons/large' __alt='Manage Relations'}</div>

    <h2>{gt text='Manage Relations'}</h2>

    {if $tid}
        {clip_submenu tid=$tid}
    {/if}

    {form cssClass='z-form' enctype='application/x-www-form-urlencoded'}
    <div class="z-list-relations">
        <fieldset>
            <legend>{gt text='Relations'}</legend>

            <span class="z-nowrap">
                {formlabel for='withtid1' text='Filter by owner'}
                {formdropdownlist items=$typeselector id='withtid1' group='filter'}
            </span>
            <span class="z-nowrap">
                {formdropdownlist items=$ops id='op' group='filter'}
            </span>
            <span class="z-nowrap">
                {formlabel for='withtid2' text='related'}
                {formdropdownlist items=$typeselector id='withtid2' group='filter'}
            </span>
            <span class="z-nowrap z-buttons">
                {formbutton commandName='filter' __text='Filter' class='z-bt-small'}
                {formbutton commandName='clear' __text='Clear' class='z-bt-small'}
            </span>

            {if $filter.withtid1 OR $filter.withtid2}
                <div class="z-warningmsg">
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
                <li id="relations_{$item.id}" class="{cycle name='relationslist' values='z-odd,z-even'} z-clearfix">
                    <span class="z-itemcell z-w10">
                       {$item.id}
                    </span>
                    <span class="z-itemcell z-w40 z-right">
                        {if $item.type lt 2}
                            {gt text='One <strong>%s</strong>' tag1=$pubtypes[$item.tid1]->title|safetext}
                        {else}
                            {gt text='Many <strong>%s</strong>' tag1=$pubtypes[$item.tid1]->title|safetext}
                        {/if}
                        &nbsp;
                    </span>
                    <span class="z-itemcell z-w40">
                        {if $item.type%2 eq 0}
                            {gt text='has One <strong>%s</strong>' tag1=$pubtypes[$item.tid2]->title|safetext}
                        {else}
                            {gt text='has Many <strong>%s</strong>' tag1=$pubtypes[$item.tid2]->title|safetext}
                        {/if}
                    </span>
                    <span class="z-itemcell z-w10">
                        <a href="{modurl modname='Clip' type='admin' func='relations' id=$item.id tid=$tid tid1=$filter.withtid1 op=$filter.op tid2=$filter.withtid2 fragment='relform'}">
                            {img modname='core' src='edit.png' set='icons/extrasmall' __title='Edit' __alt='Edit'}
                        </a>
                    </span>
                </li>
                {foreachelse}
                <li class="z-odd z-clearfix z-center">{gt text="There are no relations specified yet."}</li>
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
                {formlabel for='type1' text='Definition' mandatorysym=true}
                {formdropdownlist items=$reltypes.0 id='type1' group='relation'}
                {formdropdownlist items=$typeselector id='tid1' group='relation'}
                {formdropdownlist items=$reltypes.1 id='type2' group='relation'}
                {formdropdownlist items=$typeselector id='tid2' group='relation'}
                {if isset($relation)}
                <div class="z-warningmsg">
                    {gt text='Changing the definition may lead to lose existing data.'}
                </div>
                {/if}
            </div>
            <div class="z-informationmsg">
                {gettext}
                Explanation of the side details:
                <ul>
                    <li><strong>Alias</strong>: Used as a field name on the publication code. i.e. $pubdata.Alias1</li>
                    <li><strong>Title</strong>: Used in the user side forms as the public name of the relation.</li>
                    <li><strong>Description</strong>: Also used in the forms as the autocompleter tooltip.</li>
                </ul>
                {/gettext}
            </div>
            <div class="z-linear" style="padding-top: 1em">
                <fieldset class="z-floatleft z-w45">
                    <legend>{gt text='Owning side'}</legend>
                    <div class="z-formrow">
                        {formlabel for='alias1' __text='Alias' mandatorysym=true}
                        {formtextinput id='alias1' group='relation' maxLength='100' mandatory=true}
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
                <fieldset class="z-floatright z-w45">
                    <legend>{gt text='Related side'}</legend>
                    <div class="z-formrow">
                        {formlabel for='alias2' __text='Alias' mandatorysym=true}
                        {formtextinput id='alias2' group='relation' maxLength='100' mandatory=true}
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
        </fieldset>

        <div class="z-buttons z-formbuttons">
            {if isset($relation)}
                {formbutton commandName='create' __text='Save' class='z-bt-save'}
                {gt text='Are you sure you want to delete this relation?' assign='confirmdeletion'}
                {formbutton commandName='delete' __text='Delete' class='z-btred z-bt-delete' confirmMessage=$confirmdeletion}
            {else}
                {formbutton commandName='create' __text='Create' class='z-bt-ok'}
            {/if}
            {formbutton commandName='cancel' __text='Cancel' class='z-bt-cancel'}
        </div>
    </div>
    {/form}
</div>
