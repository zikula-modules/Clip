
{include file='pagemaster_admin_header.tpl'}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname='core' src='run.gif' set='icons/large' __alt='Manage Relations'}</div>

    <h2>{gt text='Manage Relations'}</h2>

    {if $tid}
        {pmadminsubmenu tid=$tid}
    {/if}

    <p class="z-warningmsg">{gt text='When relations are added or removed the DB Tables of the publication types implied will be updated automatically.'}</p>

    <div class="z-form">
        <fieldset>
            <legend>{gt text='Relations'}</legend>

            <ul id="relationslist" class="z-itemlist">
                <li id="relationslistheader" class="relationslistheader z-itemheader z-clearfix">
                    <span class="z-itemcell z-w45 z-right">{gt text='Owning side'}&nbsp;&nbsp;</span>
                    <span class="z-itemcell z-w45">{gt text='Related side'}</span>
                    <span class="z-itemcell z-w10">{gt text='Actions'}</span>
                </li>
                {foreach from=$relations item='item' name='relation'}
                <li id="relations_{$item.id}" class="{cycle name='relationslist' values='z-odd,z-even'} z-clearfix">
                    <span class="z-itemcell z-w45 z-right">
                        {if $item.type lt 2}
                            {gt text='One <strong>%s</strong>' tag1=$pubtypes[$item.tid1]->title|safetext}
                        {else}
                            {gt text='Many <strong>%s</strong>' tag1=$pubtypes[$item.tid1]->title|safetext}
                        {/if}
                        &nbsp;
                    </span>
                    <span class="z-itemcell z-w45">
                        {if $item.type%2 eq 0}
                            {gt text='has One <strong>%s</strong>' tag1=$pubtypes[$item.tid2]->title|safetext}
                        {else}
                            {gt text='has Many <strong>%s</strong>' tag1=$pubtypes[$item.tid2]->title|safetext}
                        {/if}
                    </span>
                    <span class="z-itemcell z-w10">
                        <a href="{modurl modname='PageMaster' type='admin' func='relations' id=$item.id tid=$tid fragment='form'}">
                            {img modname='core' src='edit.gif' set='icons/extrasmall' __title='Edit' __alt='Edit'}
                        </a>
                    </span>
                </li>
                {foreachelse}
                <li class="z-odd z-clearfix z-center">{gt text="There are no relations specified yet."}</li>
                {/foreach}
            </ul>
        </fieldset>
    </div>

    {form cssClass='z-form z-form-relations' enctype='application/x-www-form-urlencoded'}
    <div>
        {formvalidationsummary}
        <fieldset>
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
            </div>
            <div class="z-formrow">
                {formlabel for='alias1' text='Alias1' mandatorysym=true}
                {formtextinput id='alias1' group='relation' maxLength='100' mandatory=true}
                <div class="z-formnote">{gt text='Alias to use for the owning pubtype.'}</div>
            </div>
            <div class="z-formrow">
                {formlabel for='alias2' text='Alias2' mandatorysym=true}
                {formtextinput id='alias2' group='relation' maxLength='100' mandatory=true}
                <div class="z-formnote">{gt text='Alias to use for the foreign pubtype.'}</div>
            </div>
        </fieldset>

        <div class="z-buttons z-formbuttons">
            {if isset($relation)}
                {formbutton commandName='create' __text='Save' class='z-bt-save'}
                {gt text='Are you sure you want to delete this relation?.' assign='confirmdeletion'}
                {formbutton commandName='delete' __text='Delete' class='z-bt-delete' confirmMessage=$confirmdeletion}
            {else}
                {formbutton commandName='create' __text='Create' class='z-bt-ok'}
            {/if}
            {formbutton commandName='cancel' __text='Cancel' class='z-bt-cancel'}
        </div>
    </div>
    {/form}
</div>
