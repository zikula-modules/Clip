
{* process the title according the publication status *}
{if $pubdata.id}
    {gt text="Edit '%s'" tag1=$pubdata.core_title|truncate:50 assign='pagetitle'}
{else}
    {gt text='Submit a publication' assign='pagetitle'}
{/if}
{if !$homepage}{pagesetvar name='title' value="`$pagetitle` - `$pubtype.title` - `$modvars.ZConfig.sitename`"}{/if}

<div class="clip-edit clip-edit-{$pubtype.urltitle} clip-edit-{$pubtype.urltitle}-{$clipargs.edit.state}">
    {include file='clip_generic_navbar.tpl'}

    <h2>{$pagetitle}</h2>

    {form cssClass='z-form' enctype='multipart/form-data'}
        <div>
            {formvalidationsummary}

            <fieldset class="z-linear">
                <legend>{gt text='Publication content'}</legend>
{$code}
            </fieldset>

            {if $relations}
            <fieldset>
                <legend>{gt text='Related publications'}</legend>

                {foreach from=$relations key='alias' item='item' name='relations'}
                <div class="z-formrow">
                    {formlabel for=$alias text=$item.title}
                    {clip_form_relation id=$alias relation=$item minchars=2 op='search' group='pubdata'}
                </div>
                {/foreach}

            </fieldset>
            {/if}

            <fieldset>
                <legend>{gt text='Publication options'}</legend>

                <div class="z-formrow">
                    {formlabel for='core_language' __text='Language'}
                    {formlanguageselector id='core_language' group='pubdata' mandatory=false}
                </div>

                <div class="z-formrow">
                    {formlabel for='core_publishdate' __text='Publish date'}
                    {formdateinput id='core_publishdate' group='pubdata' includeTime=true}
                    <em class="z-formnote z-sub">{gt text='Leave blank if you do not want to schedule the publication'}</em>
                </div>

                <div class="z-formrow">
                    {formlabel for='core_expiredate' __text='Expire date'}
                    {formdateinput id='core_expiredate' group='pubdata' includeTime=true}
                    <em class="z-formnote z-sub">{gt text='Leave blank if you do not want the plublication expires'}</em>
                </div>

                <div class="z-formrow">
                    {formlabel for='core_showinlist' __text='Show in list'}
                    {formcheckbox id='core_showinlist' group='pubdata' checked='checked'}
                </div>
            </fieldset>

            <div class="clip-hooks-edit">
                {notifydisplayhooks eventname="clip.ui_hooks.pubtype`$pubtype.tid`.form_edit" id=$pubobj.core_uniqueid}
            </div>

            <div class="z-buttons z-formbuttons">
                {foreach item='action' from=$actions}
                    {formbutton commandName=$action.id text=$action.title zparameters=$action.parameters.button|default:''}
                {/foreach}
                <input class="clip-bt-reload" type="reset" value="{gt text='Reset'}" title="{gt text='Reset the form to its initial state'}" />
                {formbutton commandName='cancel' __text='Cancel' class='z-bt-cancel'}
            </div>
        </div>
    {/form}
</div>
